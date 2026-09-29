import { defineStore } from 'pinia'
import { api } from '@/services/api'
import type { NotificationIndexResponse, NotificationItem } from '@/types/crm'

interface NotificationState {
  notifications: NotificationItem[]
  unreadCount: number
  loading: boolean
  error: string
}

export const useNotificationStore = defineStore('notifications', {
  state: (): NotificationState => ({
    notifications: [],
    unreadCount: 0,
    loading: false,
    error: '',
  }),
  actions: {
    async fetchNotifications() {
      this.loading = true
      this.error = ''
      try {
        const result = await api<NotificationIndexResponse>('/api/notifications?limit=10')
        this.notifications = result.data
        this.unreadCount = result.unread_count
      } catch (error) {
        this.error = error instanceof Error ? error.message : '通知を取得できませんでした。'
      } finally {
        this.loading = false
      }
    },
    async markAsRead(id: number) {
      const target = this.notifications.find((notification) => notification.id === id)
      if (!target || target.read_at) return

      const result = await api<{ data: NotificationItem }>(`/api/notifications/${id}/read`, { method: 'PATCH' })
      this.notifications = this.notifications.map((notification) => notification.id === id ? result.data : notification)
      this.unreadCount = Math.max(0, this.unreadCount - 1)
    },
    async markAllAsRead() {
      if (this.unreadCount === 0) return

      await api('/api/notifications/read-all', { method: 'POST' })
      const readAt = new Date().toISOString()
      this.notifications = this.notifications.map((notification) => ({
        ...notification,
        read_at: notification.read_at ?? readAt,
      }))
      this.unreadCount = 0
    },
    clear() {
      this.notifications = []
      this.unreadCount = 0
      this.error = ''
    },
  },
})
