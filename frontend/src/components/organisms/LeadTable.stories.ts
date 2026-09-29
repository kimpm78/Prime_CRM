import type { Meta, StoryObj } from '@storybook/vue3-vite'
import LeadTable from './LeadTable.vue'

const leads = [{
  id: 1,
  lead_name: 'ネクストウェーブ株式会社',
  contact_name: '山本 智子',
  email: 'yamamoto@example.com',
  phone_number: '03-1234-5678',
  source: 'Web',
  status: 'qualified' as const,
  score: 92,
  owner_user_id: 1,
  converted_account_id: null,
  is_deleted: false,
  created_at: '2026-09-15T00:00:00Z',
  updated_at: '2026-09-15T00:00:00Z',
  owner: { id: 1, name: '田中 健太' },
}]

const meta = {
  title: 'Organisms/LeadTable',
  component: LeadTable,
  tags: ['autodocs'],
  parameters: { layout: 'fullscreen' },
  args: { leads },
} satisfies Meta<typeof LeadTable>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {}
export const Empty: Story = { args: { leads: [] } }
export const Loading: Story = { args: { loading: true } }
