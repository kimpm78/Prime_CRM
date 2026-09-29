<script setup lang="ts">
import { onMounted, ref } from 'vue'
import VoltDrawer from '@/components/ui/Drawer.vue'
import AppBadge from '@/components/atoms/AppBadge.vue'
import AppButton from '@/components/atoms/AppButton.vue'
import AppIcon from '@/components/atoms/AppIcon.vue'
import SearchBox from '@/components/molecules/SearchBox.vue'
import OpportunityForm from '@/components/organisms/OpportunityForm.vue'
import OpportunityTable from '@/components/organisms/OpportunityTable.vue'
import { ApiError } from '@/services/api'
import { useOpportunityStore } from '@/stores/opportunities'
import type { Opportunity, OpportunityInput, OpportunityStage, ValidationErrors } from '@/types/crm'

const store = useOpportunityStore()
const formVisible = ref(false)
const selected = ref<Opportunity | null>(null)
const editing = ref<Opportunity | null>(null)
const saving = ref(false)
const errors = ref<ValidationErrors>({})
const toast = ref('')

onMounted(() => store.fetchOpportunities())

const stageLabels: Record<OpportunityStage['stage_name'], string> = {
  lead: '初回接触', proposal: '提案中', quote: '見積提出', won: '受注', lost: '失注',
}
const stageSeverity: Record<OpportunityStage['stage_name'], string> = {
  lead: 'info', proposal: 'warn', quote: 'info', won: 'success', lost: 'danger',
}
const currency = (value: string) => new Intl.NumberFormat('ja-JP', { style: 'currency', currency: 'JPY', maximumFractionDigits: 0 }).format(Number(value))
const date = (value: string | null) => value ? new Intl.DateTimeFormat('ja-JP').format(new Date(value)) : '未設定'
const openCreate = () => { editing.value = null; errors.value = {}; formVisible.value = true }
const openEdit = (opportunity: Opportunity) => { editing.value = opportunity; errors.value = {}; formVisible.value = true }
const showToast = (message: string) => { toast.value = message; window.setTimeout(() => { toast.value = '' }, 3000) }
const save = async (input: OpportunityInput) => {
  saving.value = true
  errors.value = {}
  try {
    showToast(await store.saveOpportunity(input, editing.value?.id))
    formVisible.value = false
  } catch (error) {
    if (error instanceof ApiError) errors.value = error.errors
    else showToast(error instanceof Error ? error.message : '保存できませんでした。')
  } finally {
    saving.value = false
  }
}
const remove = async (opportunity: Opportunity) => {
  if (!window.confirm(`${opportunity.opportunity_name}を削除しますか？`)) return
  try {
    showToast(await store.deleteOpportunity(opportunity.id))
    selected.value = null
  } catch (error) {
    showToast(error instanceof Error ? error.message : '削除できませんでした。')
  }
}
</script>

<template>
  <section>
    <header class="mb-7 flex flex-wrap items-end justify-between gap-4">
      <div><p class="mb-1 text-xs font-bold tracking-widest text-slate-400">PRIME CRM / OPPORTUNITIES</p><h1 class="text-3xl font-bold tracking-tight">商談</h1><p class="mt-1 text-sm text-slate-500">進行中の案件、金額、受注見込みを一元管理します</p></div>
      <AppButton @click="openCreate"><AppIcon name="plus" :size="17" />商談を登録</AppButton>
    </header>

    <div v-if="store.error" role="alert" class="mb-4 flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><AppIcon name="alert" />{{ store.error }}</div>
    <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
      <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 p-4">
        <SearchBox v-model="store.search" placeholder="商談名・取引先名・取引先コードで検索" @search="store.fetchOpportunities(1)" />
        <p class="text-xs text-slate-500"><strong class="text-slate-800">{{ store.total }}</strong> 件</p>
      </div>
      <div class="overflow-x-auto"><OpportunityTable :opportunities="store.opportunities" :loading="store.loading" @view="selected = $event" @edit="openEdit" @delete="remove" /></div>
      <footer class="flex items-center justify-end gap-3 border-t border-slate-100 px-4 py-3 text-xs text-slate-500">
        <AppButton severity="secondary" outlined :disabled="store.page <= 1" @click="store.fetchOpportunities(store.page - 1)"><AppIcon name="chevronLeft" :size="15" />前へ</AppButton>
        <span>{{ store.page }} / {{ store.lastPage }}</span>
        <AppButton severity="secondary" outlined :disabled="store.page >= store.lastPage" @click="store.fetchOpportunities(store.page + 1)">次へ<AppIcon name="chevronRight" :size="15" /></AppButton>
      </footer>
    </article>

    <OpportunityForm v-model:visible="formVisible" :opportunity="editing" :accounts="store.accounts" :stages="store.stages" :users="store.users" :saving="saving" :errors="errors" @submit="save" />
    <VoltDrawer :visible="Boolean(selected)" position="right" header="商談詳細" class="w-[min(440px,100vw)]" @update:visible="!$event && (selected = null)">
      <template v-if="selected">
        <div class="mb-6 text-center">
          <span class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-xl bg-emerald-50 text-emerald-600"><AppIcon name="handshake" :size="24" /></span>
          <p class="text-xs text-slate-400">{{ selected.account.account_name }}</p>
          <h2 class="text-xl font-bold">{{ selected.opportunity_name }}</h2>
          <AppBadge class="mt-2" :value="stageLabels[selected.stage.stage_name]" :severity="stageSeverity[selected.stage.stage_name]" />
        </div>
        <dl class="divide-y divide-slate-100 border-y border-slate-100 text-sm">
          <div v-for="[key, value] in [['商談金額', currency(selected.amount)], ['受注確度', `${selected.stage.probability}%`], ['完了予定日', date(selected.expected_close_date)], ['営業担当', selected.owner?.name], ['説明', selected.description]]" :key="String(key)" class="grid grid-cols-[90px_1fr] gap-3 py-3"><dt class="text-xs text-slate-400">{{ key }}</dt><dd class="m-0 break-words text-slate-700">{{ value || '未設定' }}</dd></div>
        </dl>
        <div class="mt-5 flex justify-end gap-2"><AppButton severity="secondary" outlined @click="openEdit(selected)"><AppIcon name="pencil" :size="16" />編集</AppButton><AppButton severity="danger" outlined @click="remove(selected)"><AppIcon name="trash" :size="16" />削除</AppButton></div>
      </template>
    </VoltDrawer>
    <Transition enter-active-class="transition" enter-from-class="translate-y-2 opacity-0" leave-active-class="transition" leave-to-class="translate-y-2 opacity-0"><div v-if="toast" class="fixed bottom-6 right-6 z-[100] flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-xl"><AppIcon name="check" class="text-emerald-400" />{{ toast }}</div></Transition>
  </section>
</template>
