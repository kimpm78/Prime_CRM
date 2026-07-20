import type { Meta, StoryObj } from '@storybook/vue3-vite'
import AppButton from './AppButton.vue'

const meta = { title: 'Atoms/AppButton', component: AppButton, tags: ['autodocs'], args: { label: '取引先を登録' } } satisfies Meta<typeof AppButton>
export default meta
type Story = StoryObj<typeof meta>
export const Primary: Story = {}
export const Outlined: Story = { args: { label: 'キャンセル', severity: 'secondary', outlined: true } }
export const Loading: Story = { args: { label: '保存中', loading: true } }
