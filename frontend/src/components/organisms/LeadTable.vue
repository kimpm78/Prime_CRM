<script setup lang="ts">
import Column from 'primevue/column'
import VoltDataTable from '@/components/ui/DataTable.vue'
import AppBadge from '@/components/atoms/AppBadge.vue'
import AppButton from '@/components/atoms/AppButton.vue'
import AppIcon from '@/components/atoms/AppIcon.vue'
import type { Lead } from '@/types/crm'

defineProps<{ leads: Lead[]; loading?: boolean }>()
defineEmits<{
  view: [lead: Lead]
  edit: [lead: Lead]
  delete: [lead: Lead]
}>()

const status = {
  new: { label: '新規', severity: 'info' },
  in_progress: { label: '対応中', severity: 'warn' },
  qualified: { label: '有望', severity: 'success' },
} as const
const leadStatus = (value: Lead['status']) => status[value]
const formatDate = (value: string) => new Intl.DateTimeFormat('ja-JP', {
  year: 'numeric',
  month: 'short',
  day: 'numeric',
}).format(new Date(value))
</script>

<template>
  <VoltDataTable :value="leads" :loading="loading" striped-rows table-style="min-width: 900px">
    <template #empty><div class="py-12 text-center text-sm text-slate-400">条件に一致するリードはありません。</div></template>
    <Column field="lead_name" header="リード">
      <template #body="{ data }">
        <button class="flex items-center gap-3 text-left" @click="$emit('view', data)">
          <span class="grid h-9 w-9 place-items-center rounded-lg bg-violet-50 font-bold text-violet-600">{{ data.lead_name.charAt(0) }}</span>
          <span><strong class="block text-sm text-slate-800">{{ data.lead_name }}</strong><small class="text-xs text-slate-400">{{ data.contact_name || '担当者未設定' }}</small></span>
        </button>
      </template>
    </Column>
    <Column field="source" header="流入元"><template #body="{ data }">{{ data.source || '—' }}</template></Column>
    <Column header="営業担当"><template #body="{ data }">{{ data.owner?.name || '未設定' }}</template></Column>
    <Column field="score" header="スコア">
      <template #body="{ data }"><strong class="tabular-nums text-slate-700">{{ data.score }}</strong><span class="text-xs text-slate-400"> / 100</span></template>
    </Column>
    <Column header="状態"><template #body="{ data }"><AppBadge :value="leadStatus(data.status).label" :severity="leadStatus(data.status).severity" /></template></Column>
    <Column header="更新日"><template #body="{ data }">{{ formatDate(data.updated_at) }}</template></Column>
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
