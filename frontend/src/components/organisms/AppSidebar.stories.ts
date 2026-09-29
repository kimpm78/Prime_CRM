import type { Meta, StoryObj } from '@storybook/vue3-vite'
import { useAuthStore } from '@/stores/auth'
import AppSidebar from './AppSidebar.vue'

const meta = {
  title: 'Organisms/AppSidebar',
  component: AppSidebar,
  tags: ['autodocs'],
  parameters: { layout: 'fullscreen' },
  args: { open: true },
  decorators: [
    () => ({
      setup() {
        const auth = useAuthStore()
        auth.user = {
          id: 1,
          name: 'Prime CRM 管理者',
          email: 'admin@prime-crm.test',
          department: 'システム管理',
          role: 'admin',
        }
        auth.initialized = true
      },
      template: '<div class="min-h-screen bg-slate-100"><story /></div>',
    }),
  ],
} satisfies Meta<typeof AppSidebar>

export default meta
type Story = StoryObj<typeof meta>

export const Desktop: Story = {}
export const MobileClosed: Story = { args: { open: false } }
