import type { Meta, StoryObj } from '@storybook/vue3-vite'
import CustomerTable from './CustomerTable.vue'

const customers = [{ id: 1, account_code: 'AC-1001', account_name: '株式会社ネクストウェーブ', industry: 'IT・通信', phone_number: '03-6821-4567', website: null, postal_code: '100-0005', address: '東京都千代田区', owner_user_id: 1, memo: null, is_deleted: false, created_at: '2026-07-20T00:00:00Z', updated_at: '2026-07-20T00:00:00Z', owner: { id: 1, name: '佐藤 美咲' }, opportunities_count: 1 }]
const meta = { title: 'Organisms/CustomerTable', component: CustomerTable, tags: ['autodocs'], parameters: { layout: 'fullscreen' }, args: { customers } } satisfies Meta<typeof CustomerTable>
export default meta
type Story = StoryObj<typeof meta>
export const Default: Story = {}
export const Empty: Story = { args: { customers: [] } }
