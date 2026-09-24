import type { Meta, StoryObj } from '@storybook/vue3-vite'
import TaskForm from './TaskForm.vue'

const meta = {
  title: 'Organisms/TaskForm',
  component: TaskForm,
  tags: ['autodocs'],
  args: {
    visible: true,
    accounts: [{ id: 1, account_code: 'AC-1001', account_name: '株式会社ネクストウェーブ' }],
    opportunities: [{ id: 1, account_id: 1, opportunity_name: 'DX統合基盤 導入プロジェクト' }],
    users: [{ id: 1, name: '田中 健太' }],
    errors: {},
  },
} satisfies Meta<typeof TaskForm>

export default meta
type Story = StoryObj<typeof meta>

export const Create: Story = {}
export const ValidationError: Story = {
  args: {
    errors: {
      title: ['タスク名を入力してください。'],
      assigned_user_id: ['担当者を選択してください。'],
    },
  },
}
