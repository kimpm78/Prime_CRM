import { defineStore } from 'pinia'
import { api } from '@/services/api'
import { useCrmStore } from '@/stores/crm'
import type { AccountOption, CaseStatus, ContactOption, SupportCase, SupportCaseIndexResponse, SupportCaseInput, UserOption } from '@/types/crm'

interface SupportCaseState {
  cases: SupportCase[]
  accounts: AccountOption[]
  contacts: ContactOption[]
  statuses: CaseStatus[]
  users: UserOption[]
  search: string
  page: number
  lastPage: number
  total: number
  loading: boolean
  error: string
}

export const useSupportCaseStore = defineStore('cases', {
  state: (): SupportCaseState => ({
    cases: [],
    accounts: [],
    contacts: [],
    statuses: [],
    users: [],
    search: '',
    page: 1,
    lastPage: 1,
    total: 0,
    loading: false,
    error: '',
  }),
  actions: {
    async fetchCases(page = 1) {
      this.loading = true
      this.error = ''
      try {
        const params = new URLSearchParams({ page: String(page), per_page: '8' })
        if (this.search.trim()) params.set('search', this.search.trim())
        const result = await api<SupportCaseIndexResponse>(`/api/cases?${params}`)
        this.cases = result.data
        this.accounts = result.meta.accounts
        this.contacts = result.meta.contacts
        this.statuses = result.meta.statuses
        this.users = result.meta.users
        this.page = result.current_page
        this.lastPage = result.last_page
        this.total = result.total
      } catch (error) {
        this.error = error instanceof Error ? error.message : '問い合わせ情報を取得できませんでした。'
      } finally {
        this.loading = false
      }
    },
    async saveCase(input: SupportCaseInput, id?: number) {
      const result = await api<{ message: string }>(id ? `/api/cases/${id}` : '/api/cases', {
        method: id ? 'PUT' : 'POST',
        body: JSON.stringify(input),
      })
      await Promise.all([this.fetchCases(this.page), useCrmStore().fetchDashboard()])
      return result.message
    },
    async deleteCase(id: number) {
      const result = await api<{ message: string }>(`/api/cases/${id}`, { method: 'DELETE' })
      await Promise.all([this.fetchCases(1), useCrmStore().fetchDashboard()])
      return result.message
    },
  },
})
