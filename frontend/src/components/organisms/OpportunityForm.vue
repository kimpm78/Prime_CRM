<script setup lang="ts">
import { reactive, watch } from 'vue'
import VoltDialog from '@/components/ui/Dialog.vue'
import AppButton from '@/components/atoms/AppButton.vue'
import AppIcon from '@/components/atoms/AppIcon.vue'
import AppInput from '@/components/atoms/AppInput.vue'
import FormField from '@/components/molecules/FormField.vue'
import type { AccountOption, Opportunity, OpportunityInput, OpportunityStage, UserOption, ValidationErrors } from '@/types/crm'

const props = defineProps<{
  visible: boolean
  opportunity?: Opportunity | null
  accounts: AccountOption[]
  stages: OpportunityStage[]
  users: UserOption[]
  saving?: boolean
  errors?: ValidationErrors
}>()
const emit = defineEmits<{
  'update:visible': [value: boolean]
  submit: [input: OpportunityInput]
}>()

type OpportunityFormState = Omit<OpportunityInput, 'amount'> & { amount: string }

const stageLabels: Record<OpportunityStage['stage_name'], string> = {
  lead: '初回接触',
  proposal: '提案中',
  quote: '見積提出',
  won: '受注',
  lost: '失注',
}
const blank = (): OpportunityFormState => ({
  account_id: null,
  stage_id: null,
  owner_user_id: null,
  opportunity_name: '',
  amount: '0',
  expected_close_date: '',
  description: '',
})
const form = reactive<OpportunityFormState>(blank())

watch(() => [props.visible, props.opportunity] as const, () => {
  Object.assign(form, blank(), props.opportunity ? {
    account_id: props.opportunity.account_id,
    stage_id: props.opportunity.stage_id,
    owner_user_id: props.opportunity.owner_user_id,
    opportunity_name: props.opportunity.opportunity_name,
    amount: props.opportunity.amount,
    expected_close_date: props.opportunity.expected_close_date?.slice(0, 10) ?? '',
    description: props.opportunity.description ?? '',
  } : {})
}, { immediate: true })

const firstError = (field: string) => props.errors?.[field]?.[0]
const submit = () => {
  emit('submit', {
    ...form,
    amount: Number(form.amount),
  })
}
</script>

<template>
  <VoltDialog
    :visible="visible"
    modal
    :header="opportunity ? '商談を編集' : '新しい商談を登録'"
    class="w-[min(760px,calc(100vw-2rem))]"
    @update:visible="emit('update:visible', $event)"
  >
    <form class="grid gap-4 pt-2 sm:grid-cols-2" @submit.prevent="submit">
      <FormField label="商談名" for-id="opportunity-name" required :error="firstError('opportunity_name')" class="sm:col-span-2">
        <AppInput id="opportunity-name" v-model="form.opportunity_name" :invalid="Boolean(firstError('opportunity_name'))" placeholder="新システム導入プロジェクト" required />
      </FormField>
      <FormField label="取引先" for-id="opportunity-account" required :error="firstError('account_id')">
        <select id="opportunity-account" v-model="form.account_id" required :class="['w-full rounded-md border bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500', firstError('account_id') ? 'border-rose-400' : 'border-slate-300']">
          <option :value="null" disabled>取引先を選択</option>
          <option v-for="account in accounts" :key="account.id" :value="account.id">{{ account.account_name }}（{{ account.account_code }}）</option>
        </select>
      </FormField>
      <FormField label="商談ステージ" for-id="opportunity-stage" required :error="firstError('stage_id')">
        <select id="opportunity-stage" v-model="form.stage_id" required :class="['w-full rounded-md border bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500', firstError('stage_id') ? 'border-rose-400' : 'border-slate-300']">
          <option :value="null" disabled>ステージを選択</option>
          <option v-for="stage in stages" :key="stage.id" :value="stage.id">{{ stageLabels[stage.stage_name] }}（{{ stage.probability }}%）</option>
        </select>
      </FormField>
      <FormField label="商談金額" for-id="opportunity-amount" required :error="firstError('amount')">
        <AppInput id="opportunity-amount" v-model="form.amount" type="number" min="0" step="1" inputmode="numeric" :invalid="Boolean(firstError('amount'))" required />
      </FormField>
      <FormField label="完了予定日" for-id="opportunity-close-date" :error="firstError('expected_close_date')">
        <AppInput id="opportunity-close-date" v-model="form.expected_close_date" type="date" :invalid="Boolean(firstError('expected_close_date'))" />
      </FormField>
      <FormField label="営業担当" for-id="opportunity-owner" :error="firstError('owner_user_id')">
        <select id="opportunity-owner" v-model="form.owner_user_id" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
          <option :value="null">未設定</option>
          <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
        </select>
      </FormField>
      <FormField label="説明" for-id="opportunity-description" :error="firstError('description')" class="sm:col-span-2">
        <textarea id="opportunity-description" v-model="form.description" rows="4" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="商談背景、要件、次回アクションなど" />
      </FormField>
      <div class="flex justify-end gap-2 border-t border-slate-100 pt-4 sm:col-span-2">
        <AppButton severity="secondary" outlined label="キャンセル" @click="emit('update:visible', false)" />
        <AppButton type="submit" :loading="saving" :disabled="saving"><AppIcon name="save" :size="16" />{{ opportunity ? '変更を保存' : '商談を登録' }}</AppButton>
      </div>
    </form>
  </VoltDialog>
</template>
