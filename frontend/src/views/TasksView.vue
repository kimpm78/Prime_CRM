<script setup lang="ts">
import { onMounted, ref } from 'vue'
import VoltDrawer from '@/components/ui/Drawer.vue'
import AppBadge from '@/components/atoms/AppBadge.vue'
import AppButton from '@/components/atoms/AppButton.vue'
import AppIcon from '@/components/atoms/AppIcon.vue'
import SearchBox from '@/components/molecules/SearchBox.vue'
import TaskForm from '@/components/organisms/TaskForm.vue'
import TaskTable from '@/components/organisms/TaskTable.vue'
import { ApiError } from '@/services/api'
import { useTaskStore } from '@/stores/tasks'
import type { CrmTask, TaskInput, ValidationErrors } from '@/types/crm'

const store = useTaskStore()
const formVisible = ref(false)
const selected = ref<CrmTask | null>(null)
const editing = ref<CrmTask | null>(null)
const saving = ref(false)
const errors = ref<ValidationErrors>({})
const toast = ref('')

onMounted(() => store.fetchTasks())

const statusLabels: Record<CrmTask['status'], string> = { not_started: '未着手', in_progress: '対応中', completed: '完了' }
const statusSeverity: Record<CrmTask['status'], string> = { not_started: 'info', in_progress: 'warn', completed: 'success' }
const priorityLabels: Record<CrmTask['priority'], string> = { high: '高', middle: '中', low: '低' }
const date = (value: string | null) => value ? new Intl.DateTimeFormat('ja-JP').format(new Date(value)) : '未設定'
const openCreate = () => { editing.value = null; errors.value = {}; formVisible.value = true }
const openEdit = (task: CrmTask) => { editing.value = task; errors.value = {}; formVisible.value = true }
const showToast = (message: string) => { toast.value = message; window.setTimeout(() => { toast.value = '' }, 3000) }
const save = async (input: TaskInput) => {
  saving.value = true
  errors.value = {}
  try {
    showToast(await store.saveTask(input, editing.value?.id))
    formVisible.value = false
  } catch (error) {
    if (error instanceof ApiError) errors.value = error.errors
    else showToast(error instanceof Error ? error.message : '保存できませんでした。')
  } finally {
    saving.value = false
  }
}
const remove = async (task: CrmTask) => {
  if (!window.confirm(`${task.title}を削除しますか？`)) return
  try {
    showToast(await store.deleteTask(task.id))
    selected.value = null
  } catch (error) {
    showToast(error instanceof Error ? error.message : '削除できませんでした。')
  }
}
</script>

<template>
  <section>
    <header class="mb-7 flex flex-wrap items-end justify-between gap-4"><div><p class="mb-1 text-xs font-bold tracking-widest text-slate-400">PRIME CRM / TASKS</p><h1 class="text-3xl font-bold tracking-tight">タスク</h1><p class="mt-1 text-sm text-slate-500">期限と優先度から次のアクションを管理します</p></div><AppButton @click="openCreate"><AppIcon name="plus" :size="17" />タスクを登録</AppButton></header>
    <div v-if="store.error" role="alert" class="mb-4 flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"><AppIcon name="alert" />{{ store.error }}</div>
    <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
      <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 p-4"><SearchBox v-model="store.search" placeholder="タスク名・説明・取引先・商談名で検索" @search="store.fetchTasks(1)" /><p class="text-xs text-slate-500"><strong class="text-slate-800">{{ store.total }}</strong> 件</p></div>
      <div class="overflow-x-auto"><TaskTable :tasks="store.tasks" :loading="store.loading" @view="selected = $event" @edit="openEdit" @delete="remove" /></div>
      <footer class="flex items-center justify-end gap-3 border-t border-slate-100 px-4 py-3 text-xs text-slate-500"><AppButton severity="secondary" outlined :disabled="store.page <= 1" @click="store.fetchTasks(store.page - 1)"><AppIcon name="chevronLeft" :size="15" />前へ</AppButton><span>{{ store.page }} / {{ store.lastPage }}</span><AppButton severity="secondary" outlined :disabled="store.page >= store.lastPage" @click="store.fetchTasks(store.page + 1)">次へ<AppIcon name="chevronRight" :size="15" /></AppButton></footer>
    </article>
    <TaskForm v-model:visible="formVisible" :task="editing" :accounts="store.accounts" :opportunities="store.opportunities" :users="store.users" :saving="saving" :errors="errors" @submit="save" />
    <VoltDrawer :visible="Boolean(selected)" position="right" header="タスク詳細" class="w-[min(440px,100vw)]" @update:visible="!$event && (selected = null)">
      <template v-if="selected">
        <div class="mb-6 text-center"><span class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-xl bg-amber-50 text-amber-600"><AppIcon name="tasks" :size="24" /></span><h2 class="text-xl font-bold">{{ selected.title }}</h2><div class="mt-2 flex justify-center gap-2"><AppBadge :value="statusLabels[selected.status]" :severity="statusSeverity[selected.status]" /><AppBadge :value="`優先度 ${priorityLabels[selected.priority]}`" :severity="selected.priority === 'high' ? 'danger' : selected.priority === 'middle' ? 'warn' : 'info'" /></div></div>
        <dl class="divide-y divide-slate-100 border-y border-slate-100 text-sm"><div v-for="[key, value] in [['取引先', selected.account?.account_name], ['関連商談', selected.opportunity?.opportunity_name], ['担当者', selected.assignee.name], ['期限', date(selected.due_date)], ['説明', selected.description]]" :key="String(key)" class="grid grid-cols-[90px_1fr] gap-3 py-3"><dt class="text-xs text-slate-400">{{ key }}</dt><dd class="m-0 break-words text-slate-700">{{ value || '未設定' }}</dd></div></dl>
        <div class="mt-5 flex justify-end gap-2"><AppButton severity="secondary" outlined @click="openEdit(selected)"><AppIcon name="pencil" :size="16" />編集</AppButton><AppButton severity="danger" outlined @click="remove(selected)"><AppIcon name="trash" :size="16" />削除</AppButton></div>
      </template>
    </VoltDrawer>
    <Transition enter-active-class="transition" enter-from-class="translate-y-2 opacity-0" leave-active-class="transition" leave-to-class="translate-y-2 opacity-0"><div v-if="toast" class="fixed bottom-6 right-6 z-[100] flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-xl"><AppIcon name="check" class="text-emerald-400" />{{ toast }}</div></Transition>
  </section>
</template>
