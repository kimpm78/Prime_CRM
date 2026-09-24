import { defineStore } from 'pinia'
import { api } from '@/services/api'
import type { Customer, CustomerInput, DashboardPayload, Paginated } from '@/types/crm'

interface CrmState {
  dashboard: DashboardPayload | null
  customers: Customer[]
  search: string
  page: number
  lastPage: number
  total: number
  loading: boolean
  error: string
  dashboardLoading: boolean
  dashboardError: string
}

export const useCrmStore = defineStore('crm', {
  state: (): CrmState => ({ dashboard: null, customers: [], search: '', page: 1, lastPage: 1, total: 0, loading: false, error: '', dashboardLoading: false, dashboardError: '' }),
  getters: {
    users: (state) => state.dashboard?.users ?? [],
  },
  actions: {
    async fetchDashboard() {
      this.dashboardLoading = true
      this.dashboardError = ''
      try {
        this.dashboard = await api<DashboardPayload>('/api/dashboard')
      } catch (error) {
        this.dashboardError = error instanceof Error ? error.message : 'ダッシュボード情報を取得できませんでした。'
      } finally {
        this.dashboardLoading = false
      }
    },
    async fetchCustomers(page = 1) {
      this.loading = true
      this.error = ''
      try {
        const params = new URLSearchParams({ page: String(page), per_page: '8' })
        if (this.search.trim()) params.set('search', this.search.trim())
        const result = await api<Paginated<Customer>>(`/api/accounts?${params}`)
        this.customers = result.data
        this.page = result.current_page
        this.lastPage = result.last_page
        this.total = result.total
      } catch (error) {
        this.error = error instanceof Error ? error.message : '取引先情報を取得できませんでした。'
      } finally {
        this.loading = false
      }
    },
    async saveCustomer(input: CustomerInput, id?: number) {
      const result = await api<{ message: string }>(id ? `/api/accounts/${id}` : '/api/accounts', {
        method: id ? 'PUT' : 'POST',
        body: JSON.stringify(input),
      })
      await Promise.all([this.fetchCustomers(this.page), this.fetchDashboard()])
      return result.message
    },
    async deleteCustomer(id: number) {
      const result = await api<{ message: string }>(`/api/accounts/${id}`, { method: 'DELETE' })
      await Promise.all([this.fetchCustomers(1), this.fetchDashboard()])
      return result.message
    },
  },
})
