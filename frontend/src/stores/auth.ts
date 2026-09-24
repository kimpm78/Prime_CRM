import { defineStore } from 'pinia'
import { ApiError, api, prepareCsrf } from '@/services/api'
import type { AuthUser, LoginInput } from '@/types/crm'

interface AuthState {
  user: AuthUser | null
  initialized: boolean
  loading: boolean
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({ user: null, initialized: false, loading: false }),
  getters: {
    authenticated: (state) => Boolean(state.user),
  },
  actions: {
    async initialize() {
      if (this.initialized) return
      this.loading = true
      try {
        const response = await api<{ user: AuthUser }>('/api/auth/user')
        this.user = response.user
      } catch (error) {
        if (!(error instanceof ApiError) || error.status !== 401) throw error
        this.user = null
      } finally {
        this.initialized = true
        this.loading = false
      }
    },
    async login(input: LoginInput) {
      this.loading = true
      try {
        await prepareCsrf()
        const response = await api<{ user: AuthUser }>('/api/auth/login', {
          method: 'POST',
          body: JSON.stringify(input),
        })
        this.user = response.user
        this.initialized = true
      } finally {
        this.loading = false
      }
    },
    async logout() {
      this.loading = true
      try {
        await api('/api/auth/logout', { method: 'POST' })
      } finally {
        this.user = null
        this.initialized = true
        this.loading = false
      }
    },
  },
})
