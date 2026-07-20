<script setup lang="ts">
import { reactive, watch } from 'vue'
import VoltDialog from '@/components/ui/Dialog.vue'
import AppButton from '@/components/atoms/AppButton.vue'
import AppIcon from '@/components/atoms/AppIcon.vue'
import AppInput from '@/components/atoms/AppInput.vue'
import FormField from '@/components/molecules/FormField.vue'
import StatusSelect from '@/components/molecules/StatusSelect.vue'
import type { Customer, CustomerInput, UserOption, ValidationErrors } from '@/types/crm'

const props = defineProps<{ visible: boolean; customer?: Customer | null; users: UserOption[]; saving?: boolean; errors?: ValidationErrors }>()
const emit = defineEmits<{ 'update:visible': [value: boolean]; submit: [input: CustomerInput] }>()

const blank = (): CustomerInput & { status: string } => ({ account_code: '', account_name: '', industry: '', phone_number: '', website: '', postal_code: '', address: '', owner_user_id: null, memo: '', status: 'active' })
const form = reactive(blank())

watch(() => [props.visible, props.customer] as const, () => {
  Object.assign(form, blank(), props.customer ? {
    account_code: props.customer.account_code, account_name: props.customer.account_name,
    industry: props.customer.industry ?? '', phone_number: props.customer.phone_number ?? '',
    website: props.customer.website ?? '', postal_code: props.customer.postal_code ?? '',
    address: props.customer.address ?? '', owner_user_id: props.customer.owner_user_id,
    memo: props.customer.memo ?? '',
  } : {})
}, { immediate: true })

const firstError = (field: string) => props.errors?.[field]?.[0]
const submit = () => {
  const { status: _status, ...input } = form
  emit('submit', input)
}
</script>

<template>
  <VoltDialog :visible="visible" modal :header="customer ? '取引先を編集' : '新しい取引先を登録'" class="w-[min(760px,calc(100vw-2rem))]" @update:visible="emit('update:visible', $event)">
    <form class="grid gap-4 pt-2 sm:grid-cols-2" @submit.prevent="submit">
      <FormField label="取引先コード" for-id="account-code" required :error="firstError('account_code')"><AppInput id="account-code" v-model="form.account_code" :invalid="Boolean(firstError('account_code'))" placeholder="AC-1007" /></FormField>
      <FormField label="取引先名" for-id="account-name" required :error="firstError('account_name')"><AppInput id="account-name" v-model="form.account_name" :invalid="Boolean(firstError('account_name'))" placeholder="株式会社サンプル" /></FormField>
      <FormField label="業種" for-id="industry"><AppInput id="industry" v-model="form.industry" placeholder="IT・通信" /></FormField>
      <FormField label="状態"><StatusSelect v-model="form.status" disabled /></FormField>
      <FormField label="電話番号" for-id="phone" :error="firstError('phone_number')"><AppInput id="phone" v-model="form.phone_number" placeholder="03-1234-5678" /></FormField>
      <FormField label="郵便番号" for-id="postal" :error="firstError('postal_code')"><AppInput id="postal" v-model="form.postal_code" placeholder="100-0005" /></FormField>
      <FormField label="担当者" for-id="owner"><select id="owner" v-model="form.owner_user_id" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500"><option :value="null">未設定</option><option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option></select></FormField>
      <FormField label="Webサイト" for-id="website" :error="firstError('website')"><AppInput id="website" v-model="form.website" type="url" placeholder="https://example.com" /></FormField>
      <FormField label="住所" for-id="address" class="sm:col-span-2"><AppInput id="address" v-model="form.address" placeholder="東京都千代田区…" /></FormField>
      <FormField label="メモ" for-id="memo" class="sm:col-span-2"><textarea id="memo" v-model="form.memo" rows="3" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500" placeholder="商談背景や連絡時の注意点" /></FormField>
      <div class="flex justify-end gap-2 border-t border-slate-100 pt-4 sm:col-span-2"><AppButton severity="secondary" outlined label="キャンセル" @click="emit('update:visible', false)" /><AppButton type="submit" :loading="saving"><AppIcon name="save" :size="16" />{{ customer ? '変更を保存' : '取引先を登録' }}</AppButton></div>
    </form>
  </VoltDialog>
</template>
