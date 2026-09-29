import type { Meta, StoryObj } from '@storybook/vue3-vite'
import CaseForm from './CaseForm.vue'

const meta = {
  title: 'Organisms/CaseForm',
  component: CaseForm,
  tags: ['autodocs'],
  args: {
    visible: true,
    accounts: [{ id: 1, account_code: 'AC-1001', account_name: '株式会社ネクストウェーブ' }],
    contacts: [{ id: 1, account_id: 1, last_name: '山田', first_name: '太郎', email: 'yamada@example.com' }],
    statuses: [
      { id: 1, status_name: 'new', sort_order: 1 },
      { id: 2, status_name: 'in_progress', sort_order: 2 },
      { id: 3, status_name: 'resolved', sort_order: 3 },
    ],
    users: [{ id: 1, name: '田中 健太' }],
    errors: {},
  },
} satisfies Meta<typeof CaseForm>

export default meta
type Story = StoryObj<typeof meta>

export const Create: Story = {}
export const ValidationError: Story = {
  args: {
    errors: {
      subject: ['件名を入力してください。'],
      account_id: ['取引先を選択してください。'],
    },
  },
}
