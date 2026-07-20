import type { Preview } from '@storybook/vue3-vite'
import { setup } from '@storybook/vue3-vite'
import PrimeVue from 'primevue/config'
import '../src/style.css'

setup((app) => app.use(PrimeVue, { unstyled: true }))

const preview: Preview = {
  parameters: {
    controls: { matchers: { color: /(background|color)$/i, date: /Date$/i } },
    a11y: { test: 'todo' },
    layout: 'centered',
  },
}

export default preview
