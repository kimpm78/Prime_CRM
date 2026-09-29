<script setup lang="ts">
import Column from 'primevue/column'
import VoltDataTable from '@/components/ui/DataTable.vue'
import AppBadge from '@/components/atoms/AppBadge.vue'
import AppButton from '@/components/atoms/AppButton.vue'
import AppIcon from '@/components/atoms/AppIcon.vue'
import type { CaseStatus, SupportCase } from '@/types/crm'

defineProps<{ cases: SupportCase[]; loading?: boolean }>()
defineEmits<{ view: [supportCase: SupportCase]; edit: [supportCase: SupportCase]; delete: [supportCase: SupportCase] }>()

const statuses: Record<CaseStatus['status_name'], { label: string; severity: string }> = {
  new: { label: '新規', severity: 'info' },
  in_progress: { label: '対応中', severity: 'warn' },
  resolved: { label: '解決済み', severity: 'success' },
}
const priorities: Record<SupportCase['priority'], { label: string; severity: string }> = {
  high: { label: '高', severity: 'danger' },
  middle: { label: '中', severity: 'warn' },
  low: { label: '低', severity: 'info' },
}
const caseStatus = (value: CaseStatus['status_name']) => statuses[value]
const casePriority = (value: SupportCase['priority']) => priorities[value]
const dateTime = (value: string) => new Intl.DateTimeFormat('ja-JP', { dateStyle: 'short', timeStyle: 'short' }).format(new Date(value))
const contactName = (supportCase: SupportCase) => supportCase.contact ? `${supportCase.contact.last_name} ${supportCase.contact.first_name ?? ''}`.trim() : '—'
</script>

<template>
  <VoltDataTable :value="cases" :loading="loading" striped-rows table-style="min-width: 1040px">
    <template #empty><div class="py-12 text-center text-sm text-slate-400">条件に一致する問い合わせはありません。</div></template>
    <Column field="subject" header="問い合わせ">
      <template #body="{ data }"><button class="flex items-center gap-3 text-left" @click="$emit('view', data)"><span class="grid h-9 w-9 place-items-center rounded-lg bg-violet-50 text-violet-600"><AppIcon name="cases" :size="18" /></span><span><strong class="block text-sm text-slate-800">{{ data.subject }}</strong><small class="text-xs text-slate-400">{{ data.account.account_name }}</small></span></button></template>
    </Column>
    <Column header="連絡先"><template #body="{ data }">{{ contactName(data) }}</template></Column>
    <Column header="担当者"><template #body="{ data }">{{ data.owner?.name || '未設定' }}</template></Column>
    <Column header="受付日時"><template #body="{ data }">{{ dateTime(data.opened_at) }}</template></Column>
    <Column header="ステータス"><template #body="{ data }"><AppBadge :value="caseStatus(data.status.status_name).label" :severity="caseStatus(data.status.status_name).severity" /></template></Column>
    <Column header="優先度"><template #body="{ data }"><AppBadge :value="casePriority(data.priority).label" :severity="casePriority(data.priority).severity" /></template></Column>
    <Column header="操作"><template #body="{ data }"><div class="flex gap-1"><AppButton severity="secondary" text rounded aria-label="詳細" @click="$emit('view', data)"><AppIcon name="eye" :size="16" /></AppButton><AppButton severity="secondary" text rounded aria-label="編集" @click="$emit('edit', data)"><AppIcon name="pencil" :size="16" /></AppButton><AppButton severity="danger" text rounded aria-label="削除" @click="$emit('delete', data)"><AppIcon name="trash" :size="16" /></AppButton></div></template></Column>
  </VoltDataTable>
</template>
