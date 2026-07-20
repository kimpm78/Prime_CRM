export interface UserOption {
  id: number
  name: string
}

export interface Customer {
  id: number
  account_code: string
  account_name: string
  industry: string | null
  phone_number: string | null
  website: string | null
  postal_code: string | null
  address: string | null
  owner_user_id: number | null
  memo: string | null
  is_deleted: boolean
  created_at: string
  updated_at: string
  contacts_count?: number
  opportunities_count?: number
  owner?: UserOption | null
}

export interface CustomerInput {
  account_code: string
  account_name: string
  industry: string
  phone_number: string
  website: string
  postal_code: string
  address: string
  owner_user_id: number | null
  memo: string
}

export interface DashboardPayload {
  metrics: { accounts: number; pipeline_amount: string; open_tasks: number; active_cases: number }
  pipeline: Array<{ id: number; stage_name: string; probability: number; deals_count: number; amount: string }>
  tasks: Array<{ id: number; title: string; due_date: string | null; priority: string; account_name: string; assignee: string }>
  opportunities: Array<{ id: number; opportunity_name: string; account_name: string; stage_name: string; probability: number; amount: string; expected_close_date: string | null }>
  users: UserOption[]
}

export interface Paginated<T> {
  data: T[]
  current_page: number
  last_page: number
  total: number
}

export type ValidationErrors = Record<string, string[]>
