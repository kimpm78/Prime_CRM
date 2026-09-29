export interface UserOption {
  id: number
  name: string
}

export interface AuthUser {
  id: number
  name: string
  email: string
  department: string | null
  role: string | null
}

export interface LoginInput {
  email: string
  password: string
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

export interface Lead {
  id: number
  lead_name: string
  contact_name: string | null
  email: string | null
  phone_number: string | null
  source: string | null
  status: 'new' | 'in_progress' | 'qualified'
  score: number
  owner_user_id: number | null
  converted_account_id: number | null
  is_deleted: boolean
  created_at: string
  updated_at: string
  owner?: UserOption | null
}

export interface LeadInput {
  lead_name: string
  contact_name: string
  email: string
  phone_number: string
  source: string
  status: Lead['status']
  score: number
  owner_user_id: number | null
}

export interface AccountOption {
  id: number
  account_code: string
  account_name: string
}

export interface OpportunityStage {
  id: number
  stage_name: 'lead' | 'proposal' | 'quote' | 'won' | 'lost'
  probability: number
  sort_order: number
}

export interface Opportunity {
  id: number
  account_id: number
  contact_id: number | null
  stage_id: number
  owner_user_id: number | null
  opportunity_name: string
  amount: string
  expected_close_date: string | null
  description: string | null
  is_deleted: boolean
  created_at: string
  updated_at: string
  account: AccountOption
  stage: OpportunityStage
  owner?: UserOption | null
}

export interface OpportunityInput {
  account_id: number | null
  stage_id: number | null
  owner_user_id: number | null
  opportunity_name: string
  amount: number
  expected_close_date: string
  description: string
}

export interface OpportunityIndexResponse extends Paginated<Opportunity> {
  meta: {
    accounts: AccountOption[]
    stages: OpportunityStage[]
    users: UserOption[]
  }
}

export interface TaskOpportunityOption {
  id: number
  account_id: number
  opportunity_name: string
}

export interface CrmTask {
  id: number
  account_id: number | null
  opportunity_id: number | null
  assigned_user_id: number
  title: string
  description: string | null
  due_date: string | null
  status: 'not_started' | 'in_progress' | 'completed'
  priority: 'high' | 'middle' | 'low'
  is_deleted: boolean
  created_at: string
  updated_at: string
  account?: AccountOption | null
  opportunity?: TaskOpportunityOption | null
  assignee: UserOption
}

export interface TaskInput {
  account_id: number | null
  opportunity_id: number | null
  assigned_user_id: number | null
  title: string
  description: string
  due_date: string
  status: CrmTask['status']
  priority: CrmTask['priority']
}

export interface TaskIndexResponse extends Paginated<CrmTask> {
  meta: {
    accounts: AccountOption[]
    opportunities: TaskOpportunityOption[]
    users: UserOption[]
  }
}

export interface ContactOption {
  id: number
  account_id: number
  last_name: string
  first_name: string | null
  email: string | null
}

export interface CaseStatus {
  id: number
  status_name: 'new' | 'in_progress' | 'resolved'
  sort_order: number
}

export interface SupportCase {
  id: number
  account_id: number
  contact_id: number | null
  status_id: number
  owner_user_id: number | null
  subject: string
  description: string | null
  priority: 'high' | 'middle' | 'low'
  opened_at: string
  closed_at: string | null
  is_deleted: boolean
  created_at: string
  updated_at: string
  account: AccountOption
  contact?: ContactOption | null
  status: CaseStatus
  owner?: UserOption | null
}

export interface SupportCaseInput {
  account_id: number | null
  contact_id: number | null
  status_id: number | null
  owner_user_id: number | null
  subject: string
  description: string
  priority: SupportCase['priority']
  opened_at: string
}

export interface SupportCaseIndexResponse extends Paginated<SupportCase> {
  meta: {
    accounts: AccountOption[]
    contacts: ContactOption[]
    statuses: CaseStatus[]
    users: UserOption[]
  }
}

export interface NotificationItem {
  id: number
  type: 'task_due' | 'system' | string
  title: string
  message: string
  action_url: string | null
  target_table: string | null
  target_id: number | null
  read_at: string | null
  created_at: string
}

export interface NotificationIndexResponse {
  data: NotificationItem[]
  unread_count: number
}

export interface DashboardPayload {
  metrics: { accounts: number; pipeline_amount: string; open_tasks: number; active_cases: number }
  pipeline: Array<{ id: number; stage_name: string; probability: number; deals_count: number; amount: string }>
  tasks: Array<{ id: number; title: string; due_date: string | null; priority: string; account_name: string; assignee: string }>
  opportunities: Array<{ id: number; opportunity_name: string; account_name: string; stage_name: string; probability: number; amount: string; expected_close_date: string | null }>
  leads: Array<Pick<Lead, 'id' | 'lead_name' | 'contact_name' | 'source' | 'status' | 'score'>>
  cases: Array<{ id: number; subject: string; priority: string; opened_at: string; account_name: string; status_name: string }>
  users: UserOption[]
}

export interface Paginated<T> {
  data: T[]
  current_page: number
  last_page: number
  total: number
}

export type ValidationErrors = Record<string, string[]>
