<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue'
import AppButton from '@/components/atoms/AppButton.vue'
import AppIcon from '@/components/atoms/AppIcon.vue'
import type { NotificationItem } from '@/types/crm'

const props = withDefaults(defineProps<{
  notifications: NotificationItem[]
  unreadCount: number
  loading?: boolean
  error?: string
  defaultOpen?: boolean
}>(), { defaultOpen: false })
const emit = defineEmits<{
  refresh: []
  read: [id: number]
  readAll: []
  navigate: [notification: NotificationItem]
}>()

const open = ref(props.defaultOpen)
const root = ref<HTMLElement | null>(null)

const toggle = () => {
  open.value = !open.value
  if (open.value) emit('refresh')
}
const selectNotification = (notification: NotificationItem) => {
  if (notification.action_url) emit('navigate', notification)
  else if (!notification.read_at) emit('read', notification.id)
  open.value = false
}
const closeFromOutside = (event: MouseEvent) => {
  if (open.value && root.value && !root.value.contains(event.target as Node)) open.value = false
}
const closeFromEscape = (event: KeyboardEvent) => {
  if (event.key === 'Escape') open.value = false
}
const time = (value: string) => new Intl.DateTimeFormat('ja-JP', {
  month: 'numeric', day: 'numeric', hour: '2-digit', minute: '2-digit',
}).format(new Date(value))

onMounted(() => {
  document.addEventListener('click', closeFromOutside)
  document.addEventListener('keydown', closeFromEscape)
})
onBeforeUnmount(() => {
  document.removeEventListener('click', closeFromOutside)
  document.removeEventListener('keydown', closeFromEscape)
})
</script>

<template>
  <div ref="root" class="relative">
    <AppButton
      severity="secondary"
      class="relative"
      text
      rounded
      :aria-label="unreadCount ? `通知（未読${unreadCount}件）` : '通知'"
      aria-controls="notification-panel"
      :aria-expanded="open"
      @click="toggle"
    >
      <AppIcon name="bell" :size="19" />
      <span v-if="unreadCount" class="absolute right-1 top-1 grid min-h-4 min-w-4 place-items-center rounded-full bg-rose-500 px-1 text-[10px] font-bold leading-4 text-white ring-2 ring-white">
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </AppButton>

    <section
      v-if="open"
      id="notification-panel"
      class="absolute right-0 top-12 z-50 w-[min(24rem,calc(100vw-2rem))] overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/15"
      aria-label="通知一覧"
    >
      <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
        <div>
          <h2 class="text-sm font-bold text-slate-900">通知</h2>
          <p class="mt-0.5 text-xs text-slate-500">未読 {{ unreadCount }}件</p>
        </div>
        <AppButton v-if="unreadCount" severity="secondary" text class="!px-2 !py-1" aria-label="すべて既読にする" @click="emit('readAll')">
          <AppIcon name="checkAll" :size="15" />すべて既読
        </AppButton>
      </div>

      <div v-if="loading" class="space-y-3 p-4" aria-live="polite" aria-label="通知を読み込み中">
        <div v-for="index in 3" :key="index" class="animate-pulse rounded-lg bg-slate-50 p-3">
          <div class="h-3 w-2/3 rounded bg-slate-200" />
          <div class="mt-2 h-2 w-full rounded bg-slate-100" />
        </div>
      </div>
      <div v-else-if="error" class="p-6 text-center">
        <span class="mx-auto grid h-10 w-10 place-items-center rounded-full bg-rose-50 text-rose-600"><AppIcon name="alert" /></span>
        <p class="mt-3 text-sm font-semibold text-slate-700">通知を取得できませんでした</p>
        <p class="mt-1 text-xs text-slate-500">{{ error }}</p>
        <AppButton class="mt-3" severity="secondary" outlined label="再読み込み" @click="emit('refresh')" />
      </div>
      <div v-else-if="notifications.length === 0" class="p-8 text-center">
        <span class="mx-auto grid h-11 w-11 place-items-center rounded-full bg-slate-100 text-slate-400"><AppIcon name="bell" /></span>
        <p class="mt-3 text-sm font-semibold text-slate-700">新しい通知はありません</p>
        <p class="mt-1 text-xs text-slate-500">期限が近い担当タスクをここでお知らせします。</p>
      </div>
      <ul v-else class="max-h-[min(28rem,70vh)] divide-y divide-slate-100 overflow-y-auto">
        <li v-for="notification in notifications" :key="notification.id">
          <button
            type="button"
            class="group flex w-full gap-3 px-4 py-3 text-left transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-blue-500"
            @click="selectNotification(notification)"
          >
            <span :class="['mt-0.5 grid h-9 w-9 shrink-0 place-items-center rounded-full', notification.read_at ? 'bg-slate-100 text-slate-400' : 'bg-amber-50 text-amber-600']">
              <AppIcon :name="notification.type === 'task_due' ? 'clock' : 'bell'" :size="17" />
            </span>
            <span class="min-w-0 flex-1">
              <span class="flex items-start justify-between gap-2">
                <strong :class="['truncate text-sm', notification.read_at ? 'font-semibold text-slate-600' : 'font-bold text-slate-900']">{{ notification.title }}</strong>
                <span v-if="!notification.read_at" class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-blue-600" aria-label="未読" />
              </span>
              <span class="mt-1 block text-xs leading-5 text-slate-500">{{ notification.message }}</span>
              <time :datetime="notification.created_at" class="mt-1.5 block text-[11px] text-slate-400">{{ time(notification.created_at) }}</time>
            </span>
          </button>
        </li>
      </ul>
    </section>
  </div>
</template>
