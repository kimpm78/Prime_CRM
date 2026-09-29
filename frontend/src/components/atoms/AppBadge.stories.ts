import type { Meta, StoryObj } from '@storybook/vue3-vite'
import AppBadge from './AppBadge.vue'

const meta = { title: 'Atoms/AppBadge', component: AppBadge, tags: ['autodocs'], args: { value: '有効', severity: 'success' } } satisfies Meta<typeof AppBadge>
export default meta
type Story = StoryObj<typeof meta>
export const Active: Story = {}
export const Warning: Story = { args: { value: '対応中', severity: 'warn' } }
export const Danger: Story = { args: { value: '高', severity: 'danger' } }
