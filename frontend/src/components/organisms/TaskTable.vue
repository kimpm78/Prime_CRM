<script setup lang="ts">
import Column from 'primevue/column'
import VoltDataTable from '@/components/ui/DataTable.vue'
import AppBadge from '@/components/atoms/AppBadge.vue'
import AppButton from '@/components/atoms/AppButton.vue'
import AppIcon from '@/components/atoms/AppIcon.vue'
import type { CrmTask } from '@/types/crm'

defineProps<{ tasks: CrmTask[]; loading?: boolean }>()
defineEmits<{ view: [task: CrmTask]; edit: [task: CrmTask]; delete: [task: CrmTask] }>()

const statuses: Record<CrmTask['status'], { label: string; severity: string }> = {
  not_started: { label: '未着手', severity: 'info' },
  in_progress: { label: '対応中', severity: 'warn' },
  completed: { label: '完了', severity: 'success' },
}
const priorities: Record<CrmTask['priority'], { label: string; severity: string }> = {
  high: { label: '高', severity: 'danger' },
  middle: { label: '中', severity: 'warn' },
  low: { label: '低', severity: 'info' },
}
const taskStatus = (value: CrmTask['status']) => statuses[value]
const taskPriority = (value: CrmTask['priority']) => priorities[value]
const date = (value: string | null) => value ? new Intl.DateTimeFormat('ja-JP').format(new Date(value)) : '未設定'
</script>

<template>
  <VoltDataTable :value="tasks" :loading="loading" striped-rows table-style="min-width: 980px">
    <template #empty><div class="py-12 text-center text-sm text-slate-400">条件に一致するタスクはありません。</div></template>
    <Column field="title" header="タスク">
      <template #body="{ data }"><button class="flex items-center gap-3 text-left" @click="$emit('view', data)"><span class="grid h-9 w-9 place-items-center rounded-lg bg-amber-50 text-amber-600"><AppIcon name="tasks" :size="18" /></span><span><strong class="block text-sm text-slate-800">{{ data.title }}</strong><small class="text-xs text-slate-400">{{ data.account?.account_name || '関連先未設定' }}</small></span></button></template>
    </Column>
    <Column header="関連商談"><template #body="{ data }">{{ data.opportunity?.opportunity_name || '—' }}</template></Column>
    <Column header="担当者"><template #body="{ data }">{{ data.assignee.name }}</template></Column>
    <Column header="期限"><template #body="{ data }">{{ date(data.due_date) }}</template></Column>
    <Column header="状態"><template #body="{ data }"><AppBadge :value="taskStatus(data.status).label" :severity="taskStatus(data.status).severity" /></template></Column>
    <Column header="優先度"><template #body="{ data }"><AppBadge :value="taskPriority(data.priority).label" :severity="taskPriority(data.priority).severity" /></template></Column>
    <Column header="操作"><template #body="{ data }"><div class="flex gap-1"><AppButton severity="secondary" text rounded aria-label="詳細" @click="$emit('view', data)"><AppIcon name="eye" :size="16" /></AppButton><AppButton severity="secondary" text rounded aria-label="編集" @click="$emit('edit', data)"><AppIcon name="pencil" :size="16" /></AppButton><AppButton severity="danger" text rounded aria-label="削除" @click="$emit('delete', data)"><AppIcon name="trash" :size="16" /></AppButton></div></template></Column>
  </VoltDataTable>
</template>
