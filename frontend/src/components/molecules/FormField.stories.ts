import type { Meta, StoryObj } from '@storybook/vue3-vite'
import AppInput from '@/components/atoms/AppInput.vue'
import FormField from './FormField.vue'

const meta = { title: 'Molecules/FormField', component: FormField, tags: ['autodocs'], args: { label: '取引先名', required: true } } satisfies Meta<typeof FormField>
export default meta
type Story = StoryObj<typeof meta>
export const Default: Story = { render: (args) => ({ components: { FormField, AppInput }, setup: () => ({ args }), template: '<FormField v-bind="args"><AppInput placeholder="株式会社サンプル" /></FormField>' }) }
export const Error: Story = { args: { error: '取引先名を入力してください。' }, render: (args) => ({ components: { FormField, AppInput }, setup: () => ({ args }), template: '<FormField v-bind="args"><AppInput invalid /></FormField>' }) }
