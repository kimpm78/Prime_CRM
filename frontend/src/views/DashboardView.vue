<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useCrmStore } from '@/stores/crm'
import AppBadge from '@/components/atoms/AppBadge.vue'
import AppIcon from '@/components/atoms/AppIcon.vue'

const store = useCrmStore()
onMounted(() => store.fetchDashboard())

const metrics = computed(() => [
  { label: '登録取引先', value: `${store.dashboard?.metrics.accounts ?? 0}社`, icon: 'building', tone: 'blue' },
  { label: '進行中パイプライン', value: new Intl.NumberFormat('ja-JP', { style: 'currency', currency: 'JPY', maximumFractionDigits: 0 }).format(Number(store.dashboard?.metrics.pipeline_amount ?? 0)), icon: 'money', tone: 'violet' },
  { label: '未完了タスク', value: `${store.dashboard?.metrics.open_tasks ?? 0}件`, icon: 'tasks', tone: 'amber' },
  { label: '対応中の問い合わせ', value: `${store.dashboard?.metrics.active_cases ?? 0}件`, icon: 'cases', tone: 'emerald' },
])
const maxPipeline = computed(() => Math.max(...(store.dashboard?.pipeline.map((item) => Number(item.amount)) ?? [1]), 1))
const currency = (value: string) => new Intl.NumberFormat('ja-JP', { style: 'currency', currency: 'JPY', maximumFractionDigits: 0 }).format(Number(value))
const labels: Record<string, string> = { lead: '初回接触', proposal: '提案中', quote: '見積提出', won: '受注', lost: '失注', high: '高', middle: '中', low: '低' }
</script>

<template>
  <section>
    <header class="mb-7"><p class="mb-1 text-xs font-bold tracking-widest text-slate-400">PRIME CRM / DASHBOARD</p><h1 class="text-3xl font-bold tracking-tight text-slate-900">ダッシュボード</h1><p class="mt-1 text-sm text-slate-500">営業状況をひと目で確認できます</p></header>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <article v-for="metric in metrics" :key="metric.label" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-5 flex items-start justify-between"><span :class="['grid h-10 w-10 place-items-center rounded-lg', metric.tone === 'blue' ? 'bg-blue-50 text-blue-600' : metric.tone === 'violet' ? 'bg-violet-50 text-violet-600' : metric.tone === 'amber' ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600']"><AppIcon :name="metric.icon" /></span><AppBadge value="LIVE" severity="success" /></div>
        <p class="text-xs font-semibold text-slate-500">{{ metric.label }}</p><strong class="mt-2 block truncate text-2xl font-bold text-slate-900">{{ metric.value }}</strong>
      </article>
    </div>
    <div class="mt-4 grid gap-4 xl:grid-cols-[1.35fr_.8fr]">
      <article class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <header class="border-b border-slate-100 px-5 py-4"><h2 class="font-bold text-slate-800">商談パイプライン</h2><p class="text-xs text-slate-400">ステージ別の金額と案件数</p></header>
        <div class="space-y-5 p-5">
          <div v-for="stage in store.dashboard?.pipeline" :key="stage.id" class="grid grid-cols-[90px_1fr_100px] items-center gap-4 text-xs">
            <span class="font-semibold text-slate-600">{{ labels[stage.stage_name] ?? stage.stage_name }} <small class="text-slate-400">{{ stage.deals_count }}件</small></span>
            <span class="h-2 overflow-hidden rounded-full bg-slate-100"><span class="block h-full rounded-full bg-gradient-to-r from-blue-500 to-violet-500" :style="{ width: `${Math.max(Number(stage.amount) / maxPipeline * 100, 3)}%` }" /></span>
            <strong class="text-right text-slate-700">{{ currency(stage.amount) }}</strong>
          </div>
        </div>
      </article>
      <article class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <header class="border-b border-slate-100 px-5 py-4"><h2 class="font-bold text-slate-800">次のタスク</h2><p class="text-xs text-slate-400">期限と優先度を確認</p></header>
        <div class="divide-y divide-slate-100 px-5">
          <div v-for="task in store.dashboard?.tasks?.slice(0, 5)" :key="task.id" class="flex items-center gap-3 py-3"><span class="h-4 w-4 shrink-0 rounded-full border-2 border-slate-300" /><div class="min-w-0 flex-1"><strong class="block truncate text-xs text-slate-700">{{ task.title }}</strong><small class="text-[11px] text-slate-400">{{ task.account_name }}</small></div><AppBadge :value="labels[task.priority] ?? task.priority" :severity="task.priority === 'high' ? 'danger' : 'warn'" /></div>
        </div>
      </article>
    </div>
  </section>
</template>
