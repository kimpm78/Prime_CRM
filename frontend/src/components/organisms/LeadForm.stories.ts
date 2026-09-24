import type { Meta, StoryObj } from '@storybook/vue3-vite'
import LeadForm from './LeadForm.vue'

const meta = {
  title: 'Organisms/LeadForm',
  component: LeadForm,
  tags: ['autodocs'],
  args: {
    visible: true,
    users: [{ id: 1, name: '田中 健太' }],
    errors: {},
  },
} satisfies Meta<typeof LeadForm>

export default meta
type Story = StoryObj<typeof meta>

export const Create: Story = {}
export const ValidationError: Story = {
  args: {
    errors: {
      lead_name: ['リード名を入力してください。'],
      score: ['スコアは0から100の範囲で入力してください。'],
    },
  },
}
