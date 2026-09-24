import type { Meta, StoryObj } from '@storybook/vue3-vite'
import TaskTable from './TaskTable.vue'

const tasks = [{
  id: 1,
  account_id: 1,
  opportunity_id: 1,
  assigned_user_id: 1,
  title: '提案資料の作成',
  description: '次回商談に向けた提案資料を作成する',
  due_date: '2026-09-30',
  status: 'in_progress' as const,
  priority: 'high' as const,
  is_deleted: false,
  created_at: '2026-09-15T00:00:00Z',
  updated_at: '2026-09-15T00:00:00Z',
  account: { id: 1, account_code: 'AC-1001', account_name: '株式会社ネクストウェーブ' },
  opportunity: { id: 1, account_id: 1, opportunity_name: 'DX統合基盤 導入プロジェクト' },
  assignee: { id: 1, name: '田中 健太' },
}]

const meta = {
  title: 'Organisms/TaskTable',
  component: TaskTable,
  tags: ['autodocs'],
  parameters: { layout: 'fullscreen' },
  args: { tasks },
} satisfies Meta<typeof TaskTable>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {}
export const Empty: Story = { args: { tasks: [] } }
export const Loading: Story = { args: { loading: true } }
