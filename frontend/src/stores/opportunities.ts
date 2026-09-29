import { defineStore } from 'pinia'
import { api } from '@/services/api'
import { useCrmStore } from '@/stores/crm'
import type { AccountOption, Opportunity, OpportunityIndexResponse, OpportunityInput, OpportunityStage, UserOption } from '@/types/crm'

interface OpportunityState {
  opportunities: Opportunity[]
  accounts: AccountOption[]
  stages: OpportunityStage[]
  users: UserOption[]
  search: string
  page: number
  lastPage: number
  total: number
  loading: boolean
  error: string
}

export const useOpportunityStore = defineStore('opportunities', {
  state: (): OpportunityState => ({
    opportunities: [],
    accounts: [],
    stages: [],
    users: [],
    search: '',
    page: 1,
    lastPage: 1,
    total: 0,
    loading: false,
    error: '',
  }),
  actions: {
    async fetchOpportunities(page = 1) {
      this.loading = true
      this.error = ''
      try {
        const params = new URLSearchParams({ page: String(page), per_page: '8' })
        if (this.search.trim()) params.set('search', this.search.trim())
        const result = await api<OpportunityIndexResponse>(`/api/opportunities?${params}`)
        this.opportunities = result.data
        this.accounts = result.meta.accounts
        this.stages = result.meta.stages
        this.users = result.meta.users
        this.page = result.current_page
        this.lastPage = result.last_page
        this.total = result.total
      } catch (error) {
        this.error = error instanceof Error ? error.message : '商談情報を取得できませんでした。'
      } finally {
        this.loading = false
      }
    },
    async saveOpportunity(input: OpportunityInput, id?: number) {
      const result = await api<{ message: string }>(id ? `/api/opportunities/${id}` : '/api/opportunities', {
        method: id ? 'PUT' : 'POST',
        body: JSON.stringify(input),
      })
      await Promise.all([this.fetchOpportunities(this.page), useCrmStore().fetchDashboard()])
      return result.message
    },
    async deleteOpportunity(id: number) {
      const result = await api<{ message: string }>(`/api/opportunities/${id}`, { method: 'DELETE' })
      await Promise.all([this.fetchOpportunities(1), useCrmStore().fetchDashboard()])
      return result.message
    },
  },
})
