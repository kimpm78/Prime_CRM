import type { Meta, StoryObj } from '@storybook/vue3-vite'
import OpportunityForm from './OpportunityForm.vue'

const meta = {
  title: 'Organisms/OpportunityForm',
  component: OpportunityForm,
  tags: ['autodocs'],
  args: {
    visible: true,
    accounts: [{ id: 1, account_code: 'AC-1001', account_name: '株式会社ネクストウェーブ' }],
    stages: [{ id: 1, stage_name: 'proposal', probability: 30, sort_order: 2 }],
    users: [{ id: 1, name: '田中 健太' }],
    errors: {},
  },
} satisfies Meta<typeof OpportunityForm>

export default meta
type Story = StoryObj<typeof meta>

export const Create: Story = {}
export const ValidationError: Story = {
  args: {
    errors: {
      opportunity_name: ['商談名を入力してください。'],
      amount: ['商談金額は0以上で入力してください。'],
    },
  },
}
