import type { Meta, StoryObj } from '@storybook/vue3-vite'
import CaseTable from './CaseTable.vue'

const cases = [{
  id: 1,
  account_id: 1,
  contact_id: 1,
  status_id: 2,
  owner_user_id: 1,
  subject: '管理画面にログインできない',
  description: 'ログイン時に認証エラーが表示されます。',
  priority: 'high' as const,
  opened_at: '2026-09-24T01:30:00Z',
  closed_at: null,
  is_deleted: false,
  created_at: '2026-09-24T01:30:00Z',
  updated_at: '2026-09-24T01:30:00Z',
  account: { id: 1, account_code: 'AC-1001', account_name: '株式会社ネクストウェーブ' },
  contact: { id: 1, account_id: 1, last_name: '山田', first_name: '太郎', email: 'yamada@example.com' },
  status: { id: 2, status_name: 'in_progress' as const, sort_order: 2 },
  owner: { id: 1, name: '田中 健太' },
}]

const meta = {
  title: 'Organisms/CaseTable',
  component: CaseTable,
  tags: ['autodocs'],
  parameters: { layout: 'fullscreen' },
  args: { cases },
} satisfies Meta<typeof CaseTable>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {}
export const Empty: Story = { args: { cases: [] } }
export const Loading: Story = { args: { loading: true } }
