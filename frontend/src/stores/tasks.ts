import { defineStore } from 'pinia'
import { api } from '@/services/api'
import { useCrmStore } from '@/stores/crm'
import { useNotificationStore } from '@/stores/notifications'
import type { AccountOption, CrmTask, TaskIndexResponse, TaskInput, TaskOpportunityOption, UserOption } from '@/types/crm'

interface TaskState {
  tasks: CrmTask[]
  accounts: AccountOption[]
  opportunities: TaskOpportunityOption[]
  users: UserOption[]
  search: string
  page: number
  lastPage: number
  total: number
  loading: boolean
  error: string
}

export const useTaskStore = defineStore('tasks', {
  state: (): TaskState => ({
    tasks: [],
    accounts: [],
    opportunities: [],
    users: [],
    search: '',
    page: 1,
    lastPage: 1,
    total: 0,
    loading: false,
    error: '',
  }),
  actions: {
    async fetchTasks(page = 1) {
      this.loading = true
      this.error = ''
      try {
        const params = new URLSearchParams({ page: String(page), per_page: '8' })
        if (this.search.trim()) params.set('search', this.search.trim())
        const result = await api<TaskIndexResponse>(`/api/tasks?${params}`)
        this.tasks = result.data
        this.accounts = result.meta.accounts
        this.opportunities = result.meta.opportunities
        this.users = result.meta.users
        this.page = result.current_page
        this.lastPage = result.last_page
        this.total = result.total
      } catch (error) {
        this.error = error instanceof Error ? error.message : 'タスク情報を取得できませんでした。'
      } finally {
        this.loading = false
      }
    },
    async saveTask(input: TaskInput, id?: number) {
      const result = await api<{ message: string }>(id ? `/api/tasks/${id}` : '/api/tasks', {
        method: id ? 'PUT' : 'POST',
        body: JSON.stringify(input),
      })
      await Promise.all([
        this.fetchTasks(this.page),
        useCrmStore().fetchDashboard(),
        useNotificationStore().fetchNotifications(),
      ])
      return result.message
    },
    async deleteTask(id: number) {
      const result = await api<{ message: string }>(`/api/tasks/${id}`, { method: 'DELETE' })
      await Promise.all([
        this.fetchTasks(1),
        useCrmStore().fetchDashboard(),
        useNotificationStore().fetchNotifications(),
      ])
      return result.message
    },
  },
})
