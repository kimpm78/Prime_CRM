<script setup lang="ts">
import Column from 'primevue/column'
import VoltDataTable from '@/components/ui/DataTable.vue'
import AppBadge from '@/components/atoms/AppBadge.vue'
import AppButton from '@/components/atoms/AppButton.vue'
import AppIcon from '@/components/atoms/AppIcon.vue'
import type { Opportunity, OpportunityStage } from '@/types/crm'

defineProps<{ opportunities: Opportunity[]; loading?: boolean }>()
defineEmits<{
  view: [opportunity: Opportunity]
  edit: [opportunity: Opportunity]
  delete: [opportunity: Opportunity]
}>()

const stages: Record<OpportunityStage['stage_name'], { label: string; severity: string }> = {
  lead: { label: '初回接触', severity: 'info' },
  proposal: { label: '提案中', severity: 'warn' },
  quote: { label: '見積提出', severity: 'info' },
  won: { label: '受注', severity: 'success' },
  lost: { label: '失注', severity: 'danger' },
}
const stage = (value: OpportunityStage['stage_name']) => stages[value]
const currency = (value: string) => new Intl.NumberFormat('ja-JP', {
  style: 'currency',
  currency: 'JPY',
  maximumFractionDigits: 0,
}).format(Number(value))
const date = (value: string | null) => value
  ? new Intl.DateTimeFormat('ja-JP').format(new Date(value))
  : '未設定'
</script>

<template>
  <VoltDataTable :value="opportunities" :loading="loading" striped-rows table-style="min-width: 980px">
    <template #empty><div class="py-12 text-center text-sm text-slate-400">条件に一致する商談はありません。</div></template>
    <Column field="opportunity_name" header="商談">
      <template #body="{ data }">
        <button class="flex items-center gap-3 text-left" @click="$emit('view', data)">
          <span class="grid h-9 w-9 place-items-center rounded-lg bg-emerald-50 text-emerald-600"><AppIcon name="handshake" :size="18" /></span>
          <span><strong class="block text-sm text-slate-800">{{ data.opportunity_name }}</strong><small class="text-xs text-slate-400">{{ data.account.account_name }}</small></span>
        </button>
      </template>
    </Column>
    <Column header="ステージ"><template #body="{ data }"><AppBadge :value="stage(data.stage.stage_name).label" :severity="stage(data.stage.stage_name).severity" /></template></Column>
    <Column field="amount" header="金額"><template #body="{ data }"><strong class="tabular-nums text-slate-700">{{ currency(data.amount) }}</strong></template></Column>
    <Column header="担当者"><template #body="{ data }">{{ data.owner?.name || '未設定' }}</template></Column>
    <Column header="完了予定日"><template #body="{ data }">{{ date(data.expected_close_date) }}</template></Column>
    <Column header="操作">
      <template #body="{ data }">
        <div class="flex gap-1">
          <AppButton severity="secondary" text rounded aria-label="詳細" @click="$emit('view', data)"><AppIcon name="eye" :size="16" /></AppButton>
          <AppButton severity="secondary" text rounded aria-label="編集" @click="$emit('edit', data)"><AppIcon name="pencil" :size="16" /></AppButton>
          <AppButton severity="danger" text rounded aria-label="削除" @click="$emit('delete', data)"><AppIcon name="trash" :size="16" /></AppButton>
        </div>
      </template>
    </Column>
  </VoltDataTable>
</template>
