<script setup lang="ts">
import { reactive, watch } from 'vue'
import VoltDialog from '@/components/ui/Dialog.vue'
import AppButton from '@/components/atoms/AppButton.vue'
import AppIcon from '@/components/atoms/AppIcon.vue'
import AppInput from '@/components/atoms/AppInput.vue'
import FormField from '@/components/molecules/FormField.vue'
import StatusSelect from '@/components/molecules/StatusSelect.vue'
import type { Lead, LeadInput, UserOption, ValidationErrors } from '@/types/crm'

const props = defineProps<{
  visible: boolean
  lead?: Lead | null
  users: UserOption[]
  saving?: boolean
  errors?: ValidationErrors
}>()
const emit = defineEmits<{
  'update:visible': [value: boolean]
  submit: [input: LeadInput]
}>()

type LeadFormState = Omit<LeadInput, 'score'> & { score: string }

const statusOptions = [
  { label: '新規', value: 'new' },
  { label: '対応中', value: 'in_progress' },
  { label: '有望', value: 'qualified' },
]
const blank = (): LeadFormState => ({
  lead_name: '',
  contact_name: '',
  email: '',
  phone_number: '',
  source: '',
  status: 'new',
  score: '0',
  owner_user_id: null,
})
const form = reactive<LeadFormState>(blank())

watch(() => [props.visible, props.lead] as const, () => {
  Object.assign(form, blank(), props.lead ? {
    lead_name: props.lead.lead_name,
    contact_name: props.lead.contact_name ?? '',
    email: props.lead.email ?? '',
    phone_number: props.lead.phone_number ?? '',
    source: props.lead.source ?? '',
    status: props.lead.status,
    score: String(props.lead.score),
    owner_user_id: props.lead.owner_user_id,
  } : {})
}, { immediate: true })

const firstError = (field: string) => props.errors?.[field]?.[0]
const submit = () => {
  emit('submit', {
    ...form,
    score: Number(form.score),
  })
}
</script>

<template>
  <VoltDialog
    :visible="visible"
    modal
    :header="lead ? 'リードを編集' : '新しいリードを登録'"
    class="w-[min(720px,calc(100vw-2rem))]"
    @update:visible="emit('update:visible', $event)"
  >
    <form class="grid gap-4 pt-2 sm:grid-cols-2" @submit.prevent="submit">
      <FormField label="リード名" for-id="lead-name" required :error="firstError('lead_name')">
        <AppInput id="lead-name" v-model="form.lead_name" :invalid="Boolean(firstError('lead_name'))" placeholder="株式会社サンプル" required />
      </FormField>
      <FormField label="担当者名" for-id="contact-name" :error="firstError('contact_name')">
        <AppInput id="contact-name" v-model="form.contact_name" :invalid="Boolean(firstError('contact_name'))" placeholder="山田 太郎" />
      </FormField>
      <FormField label="メールアドレス" for-id="lead-email" :error="firstError('email')">
        <AppInput id="lead-email" v-model="form.email" type="email" :invalid="Boolean(firstError('email'))" placeholder="lead@example.com" />
      </FormField>
      <FormField label="電話番号" for-id="lead-phone" :error="firstError('phone_number')">
        <AppInput id="lead-phone" v-model="form.phone_number" type="tel" :invalid="Boolean(firstError('phone_number'))" placeholder="03-1234-5678" />
      </FormField>
      <FormField label="流入元" for-id="lead-source" :error="firstError('source')">
        <AppInput id="lead-source" v-model="form.source" :invalid="Boolean(firstError('source'))" placeholder="Web・展示会・紹介など" />
      </FormField>
      <FormField label="ステータス" for-id="lead-status" required :error="firstError('status')">
        <StatusSelect id="lead-status" v-model="form.status" :options="statusOptions" />
      </FormField>
      <FormField label="スコア" for-id="lead-score" required :error="firstError('score')" hint="0から100で入力してください">
        <AppInput id="lead-score" v-model="form.score" type="number" min="0" max="100" step="1" inputmode="numeric" :invalid="Boolean(firstError('score'))" required />
      </FormField>
      <FormField label="営業担当" for-id="lead-owner" :error="firstError('owner_user_id')">
        <select id="lead-owner" v-model="form.owner_user_id" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
          <option :value="null">未設定</option>
          <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
        </select>
      </FormField>
      <div class="flex justify-end gap-2 border-t border-slate-100 pt-4 sm:col-span-2">
        <AppButton severity="secondary" outlined label="キャンセル" @click="emit('update:visible', false)" />
        <AppButton type="submit" :loading="saving" :disabled="saving">
          <AppIcon name="save" :size="16" />{{ lead ? '変更を保存' : 'リードを登録' }}
        </AppButton>
      </div>
    </form>
  </VoltDialog>
</template>
