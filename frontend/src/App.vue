<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { RouterView, useRoute, useRouter } from 'vue-router'
import AppSidebar from '@/components/organisms/AppSidebar.vue'
import AppButton from '@/components/atoms/AppButton.vue'
import AppIcon from '@/components/atoms/AppIcon.vue'
import NotificationMenu from '@/components/organisms/NotificationMenu.vue'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notifications'
import type { NotificationItem } from '@/types/crm'

const mobileMenuOpen = ref(false)
const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const notificationStore = useNotificationStore()
const publicPage = computed(() => route.meta.public === true)

const openNotification = async (notification: NotificationItem) => {
  await notificationStore.markAsRead(notification.id)
  if (notification.action_url) await router.push(notification.action_url)
}

onMounted(() => {
  if (auth.authenticated) void notificationStore.fetchNotifications()
})
watch(() => auth.authenticated, (authenticated) => {
  if (authenticated) void notificationStore.fetchNotifications()
  else notificationStore.clear()
})
</script>

<template>
  <RouterView v-if="publicPage" />
  <div v-else class="min-h-screen bg-slate-50 text-slate-900">
    <AppSidebar :open="mobileMenuOpen" @close="mobileMenuOpen = false" />
    <div class="lg:pl-64">
      <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200 bg-white/95 px-4 backdrop-blur md:px-8">
        <AppButton class="lg:hidden" severity="secondary" text rounded aria-label="メニュー" @click="mobileMenuOpen = true">
          <AppIcon name="menu" :size="20" />
        </AppButton>
        <div class="hidden items-center gap-2 text-xs font-semibold text-slate-500 sm:flex">
          <span class="h-2 w-2 rounded-full bg-emerald-500" />
          {{ auth.user?.name }}としてログイン中
        </div>
        <NotificationMenu
          :notifications="notificationStore.notifications"
          :unread-count="notificationStore.unreadCount"
          :loading="notificationStore.loading"
          :error="notificationStore.error"
          @refresh="notificationStore.fetchNotifications"
          @read="notificationStore.markAsRead"
          @read-all="notificationStore.markAllAsRead"
          @navigate="openNotification"
        />
      </header>
      <main class="mx-auto max-w-[1500px] p-4 md:p-8">
        <RouterView />
      </main>
    </div>
  </div>
</template>
