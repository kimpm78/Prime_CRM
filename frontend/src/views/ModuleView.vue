<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import AppBadge from '@/components/atoms/AppBadge.vue'
import AppIcon from '@/components/atoms/AppIcon.vue'
import { useCrmStore } from '@/stores/crm'

const route = useRoute()
const store = useCrmStore()

onMounted(() => {
  if (!store.dashboard) store.fetchDashboard()
})

const moduleName = computed(() => String(route.params.module))
const meta = computed(() => ({
  leads: { title: 'リード', icon: 'leads', description: '優先度の高い見込み顧客を確認します' },
  opportunities: { title: '商談', icon: 'handshake', description: '進行中の案件と受注見込みを管理します' },
  tasks: { title: 'タスク', icon: 'tasks', description: '期限と優先度から次の行動を整理します' },
  cases: { title: '問い合わせ', icon: 'cases', description: '顧客からの問い合わせ状況を確認します' },
}[moduleName.value] ?? { title: 'CRM', icon: 'dashboard', description: '' }))

const labels: Record<string, string> = {
  new: '新規', in_progress: '対応中', qualified: '有望', completed: '完了',
  lead: '初回接触', proposal: '提案中', quote: '見積提出', won: '受注', lost: '失注',
  high: '高', middle: '中', low: '低',
}
const currency = (value: string) => new Intl.NumberFormat('ja-JP', { style: 'currency', currency: 'JPY', maximumFractionDigits: 0 }).format(Number(value))
const date = (value: string | null) => value ? new Intl.DateTimeFormat('ja-JP').format(new Date(value)) : '未設定'

const rows = computed(() => {
  const dashboard = store.dashboard
  if (!dashboard) return []

  if (moduleName.value === 'leads') {
    return dashboard.leads.map((lead) => ({
      id: lead.id,
      primary: lead.lead_name,
      secondary: [lead.contact_name, lead.source].filter(Boolean).join('・') || '詳細未設定',
      detail: `スコア ${lead.score}`,
      badge: labels[lead.status] ?? lead.status,
      severity: lead.status === 'qualified' ? 'success' : 'info',
    }))
  }
  if (moduleName.value === 'opportunities') {
    return dashboard.opportunities.map((opportunity) => ({
      id: opportunity.id,
      primary: opportunity.opportunity_name,
      secondary: opportunity.account_name,
      detail: `${currency(opportunity.amount)}・完了予定 ${date(opportunity.expected_close_date)}`,
      badge: labels[opportunity.stage_name] ?? opportunity.stage_name,
      severity: opportunity.stage_name === 'won' ? 'success' : opportunity.stage_name === 'lost' ? 'danger' : 'info',
    }))
  }
  if (moduleName.value === 'tasks') {
    return dashboard.tasks.map((task) => ({
      id: task.id,
      primary: task.title,
      secondary: [task.account_name, task.assignee].filter(Boolean).join('・') || '関連先未設定',
      detail: `期限 ${date(task.due_date)}`,
      badge: labels[task.priority] ?? task.priority,
      severity: task.priority === 'high' ? 'danger' : task.priority === 'middle' ? 'warn' : 'info',
    }))
  }
  return dashboard.cases.map((item) => ({
    id: item.id,
    primary: item.subject,
    secondary: item.account_name,
    detail: `受付 ${date(item.opened_at)}`,
    badge: labels[item.status_name] ?? item.status_name,
    severity: item.status_name === 'resolved' ? 'success' : item.priority === 'high' ? 'danger' : 'warn',
  }))
})
</script>

<template>
  <section>
    <header class="mb-7">
      <p class="mb-1 text-xs font-bold tracking-widest text-slate-400">PRIME CRM / {{ moduleName.toUpperCase() }}</p>
      <h1 class="text-3xl font-bold tracking-tight">{{ meta.title }}</h1>
      <p class="mt-1 text-sm text-slate-500">{{ meta.description }}</p>
    </header>

    <div v-if="store.dashboardError" role="alert" class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ store.dashboardError }}</div>
    <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
      <header class="flex items-center gap-4 border-b border-slate-100 px-5 py-4">
        <span class="grid h-11 w-11 place-items-center rounded-xl bg-blue-50 text-blue-600"><AppIcon :name="meta.icon" :size="21" /></span>
        <div><h2 class="font-bold">{{ meta.title }}一覧</h2><p class="text-xs text-slate-400">データベースに登録されている最新情報</p></div>
        <AppBadge class="ml-auto" :value="`${rows.length}件`" severity="info" />
      </header>

      <div v-if="store.dashboardLoading" class="p-8 text-center text-sm text-slate-400">読み込み中...</div>
      <div v-else-if="rows.length" class="divide-y divide-slate-100">
        <div v-for="row in rows" :key="row.id" class="flex flex-wrap items-center gap-4 px-5 py-4">
          <div class="min-w-0 flex-1">
            <strong class="block truncate text-sm text-slate-800">{{ row.primary }}</strong>
            <span class="mt-1 block truncate text-xs text-slate-500">{{ row.secondary }}</span>
          </div>
          <span class="text-xs text-slate-500">{{ row.detail }}</span>
          <AppBadge :value="row.badge" :severity="row.severity" />
        </div>
      </div>
      <div v-else class="p-10 text-center">
        <span class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-slate-100 text-slate-400"><AppIcon :name="meta.icon" /></span>
        <p class="mt-3 text-sm font-semibold text-slate-600">登録データはありません</p>
        <p class="mt-1 text-xs text-slate-400">データベースに登録すると、この画面に反映されます。</p>
      </div>
    </article>
  </section>
</template>
