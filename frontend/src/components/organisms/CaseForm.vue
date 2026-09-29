<script setup lang="ts">
import { computed, reactive, watch } from 'vue'
import VoltDialog from '@/components/ui/Dialog.vue'
import AppButton from '@/components/atoms/AppButton.vue'
import AppIcon from '@/components/atoms/AppIcon.vue'
import AppInput from '@/components/atoms/AppInput.vue'
import FormField from '@/components/molecules/FormField.vue'
import type { AccountOption, CaseStatus, ContactOption, SupportCase, SupportCaseInput, UserOption, ValidationErrors } from '@/types/crm'

const props = defineProps<{
  visible: boolean
  supportCase?: SupportCase | null
  accounts: AccountOption[]
  contacts: ContactOption[]
  statuses: CaseStatus[]
  users: UserOption[]
  saving?: boolean
  errors?: ValidationErrors
}>()
const emit = defineEmits<{
  'update:visible': [value: boolean]
  submit: [input: SupportCaseInput]
}>()

const pad = (value: number) => String(value).padStart(2, '0')
const toLocalDateTime = (value?: string) => {
  const date = value ? new Date(value) : new Date()
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`
}
const blank = (): SupportCaseInput => ({
  account_id: null,
  contact_id: null,
  status_id: null,
  owner_user_id: null,
  subject: '',
  description: '',
  priority: 'middle',
  opened_at: toLocalDateTime(),
})
const form = reactive<SupportCaseInput>(blank())
const filteredContacts = computed(() => props.contacts.filter((contact) => contact.account_id === form.account_id))

watch(() => [props.visible, props.supportCase, props.statuses] as const, () => {
  Object.assign(form, blank(), props.supportCase ? {
    account_id: props.supportCase.account_id,
    contact_id: props.supportCase.contact_id,
    status_id: props.supportCase.status_id,
    owner_user_id: props.supportCase.owner_user_id,
    subject: props.supportCase.subject,
    description: props.supportCase.description ?? '',
    priority: props.supportCase.priority,
    opened_at: toLocalDateTime(props.supportCase.opened_at),
  } : {
    status_id: props.statuses.find((status) => status.status_name === 'new')?.id ?? props.statuses[0]?.id ?? null,
  })
}, { immediate: true })

watch(() => form.account_id, (accountId) => {
  if (form.contact_id && !props.contacts.some(
    (contact) => contact.id === form.contact_id && contact.account_id === accountId,
  )) {
    form.contact_id = null
  }
})

const firstError = (field: string) => props.errors?.[field]?.[0]
const contactName = (contact: ContactOption) => `${contact.last_name} ${contact.first_name ?? ''}`.trim()
const statusLabel = (status: CaseStatus['status_name']) => ({
  new: '新規', in_progress: '対応中', resolved: '解決済み',
}[status])
</script>

<template>
  <VoltDialog
    :visible="visible"
    modal
    :header="supportCase ? '問い合わせを編集' : '新しい問い合わせを登録'"
    class="w-[min(760px,calc(100vw-2rem))]"
    @update:visible="emit('update:visible', $event)"
  >
    <form class="grid gap-4 pt-2 sm:grid-cols-2" @submit.prevent="emit('submit', { ...form })">
      <FormField label="件名" for-id="case-subject" required :error="firstError('subject')" class="sm:col-span-2">
        <AppInput id="case-subject" v-model="form.subject" :invalid="Boolean(firstError('subject'))" placeholder="管理画面にログインできない" required />
      </FormField>
      <FormField label="取引先" for-id="case-account" required :error="firstError('account_id')">
        <select id="case-account" v-model="form.account_id" required :class="['w-full rounded-md border bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500', firstError('account_id') ? 'border-rose-400' : 'border-slate-300']">
          <option :value="null" disabled>取引先を選択</option>
          <option v-for="account in accounts" :key="account.id" :value="account.id">{{ account.account_name }}（{{ account.account_code }}）</option>
        </select>
      </FormField>
      <FormField label="連絡先" for-id="case-contact" :error="firstError('contact_id')" hint="取引先を選択すると候補が表示されます">
        <select id="case-contact" v-model="form.contact_id" :disabled="!form.account_id" :class="['w-full rounded-md border bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 disabled:bg-slate-100 disabled:text-slate-400', firstError('contact_id') ? 'border-rose-400' : 'border-slate-300']">
          <option :value="null">未設定</option>
          <option v-for="contact in filteredContacts" :key="contact.id" :value="contact.id">{{ contactName(contact) }}{{ contact.email ? `（${contact.email}）` : '' }}</option>
        </select>
      </FormField>
      <FormField label="ステータス" for-id="case-status" required :error="firstError('status_id')">
        <select id="case-status" v-model="form.status_id" required :class="['w-full rounded-md border bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500', firstError('status_id') ? 'border-rose-400' : 'border-slate-300']">
          <option :value="null" disabled>ステータスを選択</option>
          <option v-for="status in statuses" :key="status.id" :value="status.id">{{ statusLabel(status.status_name) }}</option>
        </select>
      </FormField>
      <FormField label="優先度" for-id="case-priority" required :error="firstError('priority')">
        <select id="case-priority" v-model="form.priority" required class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
          <option value="high">高</option>
          <option value="middle">中</option>
          <option value="low">低</option>
        </select>
      </FormField>
      <FormField label="担当者" for-id="case-owner" :error="firstError('owner_user_id')">
        <select id="case-owner" v-model="form.owner_user_id" :class="['w-full rounded-md border bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500', firstError('owner_user_id') ? 'border-rose-400' : 'border-slate-300']">
          <option :value="null">未設定</option>
          <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
        </select>
      </FormField>
      <FormField label="受付日時" for-id="case-opened-at" required :error="firstError('opened_at')">
        <AppInput id="case-opened-at" v-model="form.opened_at" type="datetime-local" :invalid="Boolean(firstError('opened_at'))" required />
      </FormField>
      <FormField label="問い合わせ内容" for-id="case-description" :error="firstError('description')" class="sm:col-span-2">
        <textarea id="case-description" v-model="form.description" rows="5" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="お客様からの問い合わせ内容や発生状況を入力" />
      </FormField>
      <div class="flex justify-end gap-2 border-t border-slate-100 pt-4 sm:col-span-2">
        <AppButton severity="secondary" outlined label="キャンセル" @click="emit('update:visible', false)" />
        <AppButton type="submit" :loading="saving" :disabled="saving"><AppIcon name="save" :size="16" />{{ supportCase ? '変更を保存' : '問い合わせを登録' }}</AppButton>
      </div>
    </form>
  </VoltDialog>
</template>
