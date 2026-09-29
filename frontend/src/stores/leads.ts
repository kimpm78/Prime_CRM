import { defineStore } from 'pinia'
import { api } from '@/services/api'
import { useCrmStore } from '@/stores/crm'
import type { Lead, LeadInput, Paginated } from '@/types/crm'

interface LeadState {
  leads: Lead[]
  search: string
  page: number
  lastPage: number
  total: number
  loading: boolean
  error: string
}

export const useLeadStore = defineStore('leads', {
  state: (): LeadState => ({
    leads: [],
    search: '',
    page: 1,
    lastPage: 1,
    total: 0,
    loading: false,
    error: '',
  }),
  actions: {
    async fetchLeads(page = 1) {
      this.loading = true
      this.error = ''
      try {
        const params = new URLSearchParams({ page: String(page), per_page: '8' })
        if (this.search.trim()) params.set('search', this.search.trim())
        const result = await api<Paginated<Lead>>(`/api/leads?${params}`)
        this.leads = result.data
        this.page = result.current_page
        this.lastPage = result.last_page
        this.total = result.total
      } catch (error) {
        this.error = error instanceof Error ? error.message : 'リード情報を取得できませんでした。'
      } finally {
        this.loading = false
      }
    },
    async saveLead(input: LeadInput, id?: number) {
      const result = await api<{ message: string }>(id ? `/api/leads/${id}` : '/api/leads', {
        method: id ? 'PUT' : 'POST',
        body: JSON.stringify(input),
      })
      await Promise.all([this.fetchLeads(this.page), useCrmStore().fetchDashboard()])
      return result.message
    },
    async deleteLead(id: number) {
      const result = await api<{ message: string }>(`/api/leads/${id}`, { method: 'DELETE' })
      await Promise.all([this.fetchLeads(1), useCrmStore().fetchDashboard()])
      return result.message
    },
  },
})
