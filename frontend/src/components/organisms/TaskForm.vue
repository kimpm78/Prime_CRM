<script setup lang="ts">
import { computed, reactive, watch } from 'vue'
import VoltDialog from '@/components/ui/Dialog.vue'
import AppButton from '@/components/atoms/AppButton.vue'
import AppIcon from '@/components/atoms/AppIcon.vue'
import AppInput from '@/components/atoms/AppInput.vue'
import FormField from '@/components/molecules/FormField.vue'
import type { AccountOption, CrmTask, TaskInput, TaskOpportunityOption, UserOption, ValidationErrors } from '@/types/crm'

const props = defineProps<{
  visible: boolean
  task?: CrmTask | null
  accounts: AccountOption[]
  opportunities: TaskOpportunityOption[]
  users: UserOption[]
  saving?: boolean
  errors?: ValidationErrors
}>()
const emit = defineEmits<{
  'update:visible': [value: boolean]
  submit: [input: TaskInput]
}>()

const blank = (): TaskInput => ({
  account_id: null,
  opportunity_id: null,
  assigned_user_id: null,
  title: '',
  description: '',
  due_date: '',
  status: 'not_started',
  priority: 'middle',
})
const form = reactive<TaskInput>(blank())
const filteredOpportunities = computed(() => props.opportunities.filter(
  (opportunity) => opportunity.account_id === form.account_id,
))

watch(() => [props.visible, props.task] as const, () => {
  Object.assign(form, blank(), props.task ? {
    account_id: props.task.account_id,
    opportunity_id: props.task.opportunity_id,
    assigned_user_id: props.task.assigned_user_id,
    title: props.task.title,
    description: props.task.description ?? '',
    due_date: props.task.due_date?.slice(0, 10) ?? '',
    status: props.task.status,
    priority: props.task.priority,
  } : {})
}, { immediate: true })

watch(() => form.account_id, (accountId) => {
  if (form.opportunity_id && !props.opportunities.some(
    (opportunity) => opportunity.id === form.opportunity_id && opportunity.account_id === accountId,
  )) {
    form.opportunity_id = null
  }
})

const firstError = (field: string) => props.errors?.[field]?.[0]
</script>

<template>
  <VoltDialog
    :visible="visible"
    modal
    :header="task ? 'タスクを編集' : '新しいタスクを登録'"
    class="w-[min(760px,calc(100vw-2rem))]"
    @update:visible="emit('update:visible', $event)"
  >
    <form class="grid gap-4 pt-2 sm:grid-cols-2" @submit.prevent="emit('submit', { ...form })">
      <FormField label="タスク名" for-id="task-title" required :error="firstError('title')" class="sm:col-span-2">
        <AppInput id="task-title" v-model="form.title" :invalid="Boolean(firstError('title'))" placeholder="提案資料の作成" required />
      </FormField>
      <FormField label="取引先" for-id="task-account" :error="firstError('account_id')">
        <select id="task-account" v-model="form.account_id" :class="['w-full rounded-md border bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500', firstError('account_id') ? 'border-rose-400' : 'border-slate-300']">
          <option :value="null">未設定</option>
          <option v-for="account in accounts" :key="account.id" :value="account.id">{{ account.account_name }}（{{ account.account_code }}）</option>
        </select>
      </FormField>
      <FormField label="関連商談" for-id="task-opportunity" :error="firstError('opportunity_id')" hint="取引先を選択すると候補が表示されます">
        <select id="task-opportunity" v-model="form.opportunity_id" :disabled="!form.account_id" :class="['w-full rounded-md border bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 disabled:bg-slate-100 disabled:text-slate-400', firstError('opportunity_id') ? 'border-rose-400' : 'border-slate-300']">
          <option :value="null">未設定</option>
          <option v-for="opportunity in filteredOpportunities" :key="opportunity.id" :value="opportunity.id">{{ opportunity.opportunity_name }}</option>
        </select>
      </FormField>
      <FormField label="担当者" for-id="task-assignee" required :error="firstError('assigned_user_id')">
        <select id="task-assignee" v-model="form.assigned_user_id" required :class="['w-full rounded-md border bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500', firstError('assigned_user_id') ? 'border-rose-400' : 'border-slate-300']">
          <option :value="null" disabled>担当者を選択</option>
          <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
        </select>
      </FormField>
      <FormField label="期限" for-id="task-due-date" :error="firstError('due_date')">
        <AppInput id="task-due-date" v-model="form.due_date" type="date" :invalid="Boolean(firstError('due_date'))" />
      </FormField>
      <FormField label="状態" for-id="task-status" required :error="firstError('status')">
        <select id="task-status" v-model="form.status" required class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
          <option value="not_started">未着手</option>
          <option value="in_progress">対応中</option>
          <option value="completed">完了</option>
        </select>
      </FormField>
      <FormField label="優先度" for-id="task-priority" required :error="firstError('priority')">
        <select id="task-priority" v-model="form.priority" required class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
          <option value="high">高</option>
          <option value="middle">中</option>
          <option value="low">低</option>
        </select>
      </FormField>
      <FormField label="説明" for-id="task-description" :error="firstError('description')" class="sm:col-span-2">
        <textarea id="task-description" v-model="form.description" rows="4" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="作業内容や完了条件を入力" />
      </FormField>
      <div class="flex justify-end gap-2 border-t border-slate-100 pt-4 sm:col-span-2">
        <AppButton severity="secondary" outlined label="キャンセル" @click="emit('update:visible', false)" />
        <AppButton type="submit" :loading="saving" :disabled="saving"><AppIcon name="save" :size="16" />{{ task ? '変更を保存' : 'タスクを登録' }}</AppButton>
      </div>
    </form>
  </VoltDialog>
</template>
