import type { Meta, StoryObj } from '@storybook/vue3-vite'
import OpportunityTable from './OpportunityTable.vue'

const opportunities = [{
  id: 1,
  account_id: 1,
  contact_id: null,
  stage_id: 2,
  owner_user_id: 1,
  opportunity_name: 'DX統合基盤 導入プロジェクト',
  amount: '4800000.00',
  expected_close_date: '2026-12-20',
  description: '要件ヒアリングと提案内容を継続調整中',
  is_deleted: false,
  created_at: '2026-09-15T00:00:00Z',
  updated_at: '2026-09-15T00:00:00Z',
  account: { id: 1, account_code: 'AC-1001', account_name: '株式会社ネクストウェーブ' },
  stage: { id: 2, stage_name: 'proposal' as const, probability: 30, sort_order: 2 },
  owner: { id: 1, name: '田中 健太' },
}]

const meta = {
  title: 'Organisms/OpportunityTable',
  component: OpportunityTable,
  tags: ['autodocs'],
  parameters: { layout: 'fullscreen' },
  args: { opportunities },
} satisfies Meta<typeof OpportunityTable>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {}
export const Empty: Story = { args: { opportunities: [] } }
export const Loading: Story = { args: { loading: true } }
