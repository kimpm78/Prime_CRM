<script setup lang="ts">
import { onMounted, ref } from 'vue'
import VoltDrawer from '@/components/ui/Drawer.vue'
import AppBadge from '@/components/atoms/AppBadge.vue'
import AppButton from '@/components/atoms/AppButton.vue'
import AppIcon from '@/components/atoms/AppIcon.vue'
import SearchBox from '@/components/molecules/SearchBox.vue'
import CaseForm from '@/components/organisms/CaseForm.vue'
import CaseTable from '@/components/organisms/CaseTable.vue'
import { ApiError } from '@/services/api'
import { useSupportCaseStore } from '@/stores/cases'
import type { CaseStatus, SupportCase, SupportCaseInput, ValidationErrors } from '@/types/crm'

const store = useSupportCaseStore()
const formVisible = ref(false)
const selected = ref<SupportCase | null>(null)
const editing = ref<SupportCase | null>(null)
const saving = ref(false)
const errors = ref<ValidationErrors>({})
const toast = ref('')

onMounted(() => store.fetchCases())

const statusLabels: Record<CaseStatus['status_name'], string> = { new: '新規', in_progress: '対応中', resolved: '解決済み' }
const statusSeverity: Record<CaseStatus['status_name'], string> = { new: 'info', in_progress: 'warn', resolved: 'success' }
const priorityLabels: Record<SupportCase['priority'], string> = { high: '高', middle: '中', low: '低' }
const dateTime = (value: string | null) => value ? new Intl.DateTimeFormat('ja-JP', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value)) : '未設定'
const contactName = (supportCase: SupportCase) => supportCase.contact ? `${supportCase.contact.last_name} ${supportCase.contact.first_name ?? ''}`.trim() : '未設定'
const openCreate = () => { editing.value = null; errors.value = {}; formVisible.value = true }
const openEdit = (supportCase: SupportCase) => { editing.value = supportCase; errors.value = {}; formVisible.value = true }
const showToast = (message: string) => { toast.value = message; window.setTimeout(() => { toast.value = '' }, 3000) }
const save = async (input: SupportCaseInput) => {
  saving.value = true
  errors.value = {}
  try {
    showToast(await store.saveCase(input, editing.value?.id))
    formVisible.value = false
  } catch (error) {
    if (error instanceof ApiError) errors.value = error.errors
    else showToast(error instanceof Error ? error.message : '保存できませんでした。')
  } finally {
    saving.value = false
  }
}
const remove = async (supportCase: SupportCase) => {
  if (!window.confirm(`${supportCase.subject}を削除しますか？`)) return
  try {
    showToast(await store.deleteCase(supportCase.id))
    selected.value = null
  } catch (error) {
    showToast(error instanceof Error ? error.message : '削除できませんでした。')
  }
}
</script>

<template>
  <section>
    <header class="mb-7 flex flex-wrap items-end justify-between gap-4">
      <div><p class="mb-1 text-xs font-bold tracking-widest text-slate-400">PRIME CRM / CASES</p><h1 class="text-3xl font-bold tracking-tight">問い合わせ</h1><p class="mt-1 text-sm text-slate-500">顧客からの問い合わせ、担当者、対応状況を一元管理します</p></div>
      <AppButton @click="openCreate"><AppIcon name="plus" :size="17" />問い合わせを登録</AppButton>
    </header>

    <div v-if="store.error" role="alert" class="mb-4 flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><AppIcon name="alert" />{{ store.error }}</div>
    <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
      <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 p-4">
        <SearchBox v-model="store.search" placeholder="件名・内容・取引先・連絡先で検索" @search="store.fetchCases(1)" />
        <p class="text-xs text-slate-500"><strong class="text-slate-800">{{ store.total }}</strong> 件</p>
      </div>
      <div class="overflow-x-auto"><CaseTable :cases="store.cases" :loading="store.loading" @view="selected = $event" @edit="openEdit" @delete="remove" /></div>
      <footer class="flex items-center justify-end gap-3 border-t border-slate-100 px-4 py-3 text-xs text-slate-500">
        <AppButton severity="secondary" outlined :disabled="store.page <= 1" @click="store.fetchCases(store.page - 1)"><AppIcon name="chevronLeft" :size="15" />前へ</AppButton>
        <span>{{ store.page }} / {{ store.lastPage }}</span>
        <AppButton severity="secondary" outlined :disabled="store.page >= store.lastPage" @click="store.fetchCases(store.page + 1)">次へ<AppIcon name="chevronRight" :size="15" /></AppButton>
      </footer>
    </article>

    <CaseForm v-model:visible="formVisible" :support-case="editing" :accounts="store.accounts" :contacts="store.contacts" :statuses="store.statuses" :users="store.users" :saving="saving" :errors="errors" @submit="save" />
    <VoltDrawer :visible="Boolean(selected)" position="right" header="問い合わせ詳細" class="w-[min(440px,100vw)]" @update:visible="!$event && (selected = null)">
      <template v-if="selected">
        <div class="mb-6 text-center">
          <span class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-xl bg-violet-50 text-violet-600"><AppIcon name="cases" :size="24" /></span>
          <p class="text-xs text-slate-400">{{ selected.account.account_name }}</p>
          <h2 class="text-xl font-bold">{{ selected.subject }}</h2>
          <div class="mt-2 flex justify-center gap-2"><AppBadge :value="statusLabels[selected.status.status_name]" :severity="statusSeverity[selected.status.status_name]" /><AppBadge :value="`優先度 ${priorityLabels[selected.priority]}`" :severity="selected.priority === 'high' ? 'danger' : selected.priority === 'middle' ? 'warn' : 'info'" /></div>
        </div>
        <dl class="divide-y divide-slate-100 border-y border-slate-100 text-sm">
          <div v-for="[key, value] in [['連絡先', contactName(selected)], ['担当者', selected.owner?.name], ['受付日時', dateTime(selected.opened_at)], ['完了日時', dateTime(selected.closed_at)], ['問い合わせ内容', selected.description]]" :key="String(key)" class="grid grid-cols-[90px_1fr] gap-3 py-3"><dt class="text-xs text-slate-400">{{ key }}</dt><dd class="m-0 break-words text-slate-700">{{ value || '未設定' }}</dd></div>
        </dl>
        <div class="mt-5 flex justify-end gap-2"><AppButton severity="secondary" outlined @click="openEdit(selected)"><AppIcon name="pencil" :size="16" />編集</AppButton><AppButton severity="danger" outlined @click="remove(selected)"><AppIcon name="trash" :size="16" />削除</AppButton></div>
      </template>
    </VoltDrawer>
    <Transition enter-active-class="transition" enter-from-class="translate-y-2 opacity-0" leave-active-class="transition" leave-to-class="translate-y-2 opacity-0"><div v-if="toast" class="fixed bottom-6 right-6 z-[100] flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-xl"><AppIcon name="check" class="text-emerald-400" />{{ toast }}</div></Transition>
  </section>
</template>
