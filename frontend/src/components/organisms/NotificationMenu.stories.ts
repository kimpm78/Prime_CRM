import type { Meta, StoryObj } from '@storybook/vue3-vite'
import NotificationMenu from './NotificationMenu.vue'

const notifications = [
  {
    id: 1,
    type: 'task_due',
    title: '見積条件の最終確認',
    message: '青空商事株式会社のタスクです。本日が期限です。',
    action_url: '/tasks',
    target_table: 'tasks',
    target_id: 1,
    read_at: null,
    created_at: '2026-09-15T09:30:00+09:00',
  },
  {
    id: 2,
    type: 'task_due',
    title: '導入スケジュール調整',
    message: '京浜ロジスティクス株式会社のタスクです。期限まであと7日です。',
    action_url: '/tasks',
    target_table: 'tasks',
    target_id: 2,
    read_at: '2026-09-15T10:00:00+09:00',
    created_at: '2026-09-14T16:00:00+09:00',
  },
]

const meta = {
  title: 'Organisms/NotificationMenu',
  component: NotificationMenu,
  tags: ['autodocs'],
  args: {
    notifications,
    unreadCount: 1,
    defaultOpen: true,
  },
} satisfies Meta<typeof NotificationMenu>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {}
export const Empty: Story = { args: { notifications: [], unreadCount: 0 } }
export const Loading: Story = { args: { notifications: [], unreadCount: 0, loading: true } }
export const Error: Story = { args: { notifications: [], unreadCount: 0, error: 'サーバーに接続できませんでした。' } }
