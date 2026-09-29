<script setup lang="ts">
import Column from 'primevue/column'
import VoltDataTable from '@/components/ui/DataTable.vue'
import AppBadge from '@/components/atoms/AppBadge.vue'
import AppButton from '@/components/atoms/AppButton.vue'
import AppIcon from '@/components/atoms/AppIcon.vue'
import type { Customer } from '@/types/crm'

defineProps<{ customers: Customer[]; loading?: boolean }>()
defineEmits<{ view: [customer: Customer]; edit: [customer: Customer]; delete: [customer: Customer] }>()

const formatDate = (value: string) => new Intl.DateTimeFormat('ja-JP', { year: 'numeric', month: 'short', day: 'numeric' }).format(new Date(value))
</script>

<template>
  <VoltDataTable :value="customers" :loading="loading" striped-rows table-style="min-width: 920px">
    <template #empty><div class="py-12 text-center text-sm text-slate-400">条件に一致する取引先はありません。</div></template>
    <Column field="account_name" header="取引先">
      <template #body="{ data }"><button class="flex items-center gap-3 text-left" @click="$emit('view', data)"><span class="grid h-9 w-9 place-items-center rounded-lg bg-blue-50 font-bold text-blue-600">{{ data.account_name.charAt(0) }}</span><span><strong class="block text-sm text-slate-800">{{ data.account_name }}</strong><small class="text-xs text-slate-400">{{ data.account_code }}</small></span></button></template>
    </Column>
    <Column field="industry" header="業種"><template #body="{ data }">{{ data.industry || '—' }}</template></Column>
    <Column header="担当者"><template #body="{ data }">{{ data.owner?.name || '未設定' }}</template></Column>
    <Column field="phone_number" header="電話番号"><template #body="{ data }">{{ data.phone_number || '—' }}</template></Column>
    <Column header="状態"><template #body><AppBadge value="有効" severity="success" /></template></Column>
    <Column header="更新日"><template #body="{ data }">{{ formatDate(data.updated_at) }}</template></Column>
    <Column header="操作">
      <template #body="{ data }"><div class="flex gap-1"><AppButton severity="secondary" text rounded aria-label="詳細" @click="$emit('view', data)"><AppIcon name="eye" :size="16" /></AppButton><AppButton severity="secondary" text rounded aria-label="編集" @click="$emit('edit', data)"><AppIcon name="pencil" :size="16" /></AppButton><AppButton severity="danger" text rounded aria-label="削除" @click="$emit('delete', data)"><AppIcon name="trash" :size="16" /></AppButton></div></template>
    </Column>
  </VoltDataTable>
</template>
