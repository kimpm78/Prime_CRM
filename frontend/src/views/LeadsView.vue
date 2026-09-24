<script setup lang="ts">
import { onMounted, ref } from 'vue'
import VoltDrawer from '@/components/ui/Drawer.vue'
import AppBadge from '@/components/atoms/AppBadge.vue'
import AppButton from '@/components/atoms/AppButton.vue'
import AppIcon from '@/components/atoms/AppIcon.vue'
import SearchBox from '@/components/molecules/SearchBox.vue'
import LeadForm from '@/components/organisms/LeadForm.vue'
import LeadTable from '@/components/organisms/LeadTable.vue'
import { ApiError } from '@/services/api'
import { useCrmStore } from '@/stores/crm'
import { useLeadStore } from '@/stores/leads'
import type { Lead, LeadInput, ValidationErrors } from '@/types/crm'

const crmStore = useCrmStore()
const leadStore = useLeadStore()
const formVisible = ref(false)
const selected = ref<Lead | null>(null)
const editing = ref<Lead | null>(null)
const saving = ref(false)
const errors = ref<ValidationErrors>({})
const toast = ref('')

onMounted(() => Promise.all([crmStore.fetchDashboard(), leadStore.fetchLeads()]))

const openCreate = () => {
  editing.value = null
  errors.value = {}
  formVisible.value = true
}
const openEdit = (lead: Lead) => {
  editing.value = lead
  errors.value = {}
  formVisible.value = true
}
const showToast = (message: string) => {
  toast.value = message
  window.setTimeout(() => { toast.value = '' }, 3000)
}
const save = async (input: LeadInput) => {
  saving.value = true
  errors.value = {}
  try {
    showToast(await leadStore.saveLead(input, editing.value?.id))
    formVisible.value = false
  } catch (error) {
    if (error instanceof ApiError) errors.value = error.errors
    else showToast(error instanceof Error ? error.message : '保存できませんでした。')
  } finally {
    saving.value = false
  }
}
const remove = async (lead: Lead) => {
  if (!window.confirm(`${lead.lead_name}を削除しますか？`)) return
  try {
    showToast(await leadStore.deleteLead(lead.id))
    selected.value = null
  } catch (error) {
    showToast(error instanceof Error ? error.message : '削除できませんでした。')
  }
}

const statusLabel = { new: '新規', in_progress: '対応中', qualified: '有望' }
const statusSeverity = { new: 'info', in_progress: 'warn', qualified: 'success' }
</script>

<template>
  <section>
    <header class="mb-7 flex flex-wrap items-end justify-between gap-4">
      <div><p class="mb-1 text-xs font-bold tracking-widest text-slate-400">PRIME CRM / LEADS</p><h1 class="text-3xl font-bold tracking-tight">リード</h1><p class="mt-1 text-sm text-slate-500">見込み顧客の情報と対応状況を一元管理します</p></div>
      <AppButton @click="openCreate"><AppIcon name="plus" :size="17" />リードを登録</AppButton>
    </header>

    <div v-if="leadStore.error" role="alert" class="mb-4 flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><AppIcon name="alert" />{{ leadStore.error }}</div>
    <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
      <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 p-4">
        <SearchBox v-model="leadStore.search" placeholder="リード名・担当者名・連絡先・流入元で検索" @search="leadStore.fetchLeads(1)" />
        <p class="text-xs text-slate-500"><strong class="text-slate-800">{{ leadStore.total }}</strong> 件</p>
      </div>
      <div class="overflow-x-auto"><LeadTable :leads="leadStore.leads" :loading="leadStore.loading" @view="selected = $event" @edit="openEdit" @delete="remove" /></div>
      <footer class="flex items-center justify-end gap-3 border-t border-slate-100 px-4 py-3 text-xs text-slate-500">
        <AppButton severity="secondary" outlined :disabled="leadStore.page <= 1" @click="leadStore.fetchLeads(leadStore.page - 1)"><AppIcon name="chevronLeft" :size="15" />前へ</AppButton>
        <span>{{ leadStore.page }} / {{ leadStore.lastPage }}</span>
        <AppButton severity="secondary" outlined :disabled="leadStore.page >= leadStore.lastPage" @click="leadStore.fetchLeads(leadStore.page + 1)">次へ<AppIcon name="chevronRight" :size="15" /></AppButton>
      </footer>
    </article>

    <LeadForm v-model:visible="formVisible" :lead="editing" :users="crmStore.users" :saving="saving" :errors="errors" @submit="save" />
    <VoltDrawer :visible="Boolean(selected)" position="right" header="リード詳細" class="w-[min(430px,100vw)]" @update:visible="!$event && (selected = null)">
      <template v-if="selected">
        <div class="mb-6 text-center">
          <span class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-xl bg-violet-50 text-xl font-bold text-violet-600">{{ selected.lead_name.charAt(0) }}</span>
          <h2 class="text-xl font-bold">{{ selected.lead_name }}</h2>
          <AppBadge class="mt-2" :value="statusLabel[selected.status]" :severity="statusSeverity[selected.status]" />
        </div>
        <dl class="divide-y divide-slate-100 border-y border-slate-100 text-sm">
          <div v-for="[key, value] in [['担当者名', selected.contact_name], ['メール', selected.email], ['電話番号', selected.phone_number], ['流入元', selected.source], ['営業担当', selected.owner?.name], ['スコア', `${selected.score} / 100`]]" :key="String(key)" class="grid grid-cols-[90px_1fr] gap-3 py-3"><dt class="text-xs text-slate-400">{{ key }}</dt><dd class="m-0 break-words text-slate-700">{{ value || '未設定' }}</dd></div>
        </dl>
        <div class="mt-5 flex justify-end gap-2">
          <AppButton severity="secondary" outlined @click="openEdit(selected)"><AppIcon name="pencil" :size="16" />編集</AppButton>
          <AppButton severity="danger" outlined @click="remove(selected)"><AppIcon name="trash" :size="16" />削除</AppButton>
        </div>
      </template>
    </VoltDrawer>
    <Transition enter-active-class="transition" enter-from-class="translate-y-2 opacity-0" leave-active-class="transition" leave-to-class="translate-y-2 opacity-0"><div v-if="toast" class="fixed bottom-6 right-6 z-[100] flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-xl"><AppIcon name="check" class="text-emerald-400" />{{ toast }}</div></Transition>
  </section>
</template>
