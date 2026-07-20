<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import AppBadge from '@/components/atoms/AppBadge.vue'
import AppIcon from '@/components/atoms/AppIcon.vue'
import { useCrmStore } from '@/stores/crm'

const route = useRoute()
const store = useCrmStore()
onMounted(() => store.fetchDashboard())
const meta = computed(() => ({
  leads: { title: 'リード', icon: 'leads', description: '優先度の高い見込み顧客を確認します' },
  opportunities: { title: '商談', icon: 'handshake', description: '進行中の案件と受注見込みを管理します' },
  tasks: { title: 'タスク', icon: 'tasks', description: '期限と優先度から次の行動を整理します' },
  cases: { title: '問い合わせ', icon: 'cases', description: '顧客からの問い合わせ状況を確認します' },
}[String(route.params.module)] ?? { title: 'CRM', icon: 'dashboard', description: '' }))
</script>

<template>
  <section><header class="mb-7"><p class="mb-1 text-xs font-bold tracking-widest text-slate-400">PRIME CRM / {{ String(route.params.module).toUpperCase() }}</p><h1 class="text-3xl font-bold tracking-tight">{{ meta.title }}</h1><p class="mt-1 text-sm text-slate-500">{{ meta.description }}</p></header><article class="rounded-xl border border-slate-200 bg-white p-8 shadow-sm"><div class="mb-6 flex items-center gap-4"><span class="grid h-12 w-12 place-items-center rounded-xl bg-blue-50 text-blue-600"><AppIcon :name="meta.icon" :size="23" /></span><div><h2 class="font-bold">{{ meta.title }}ワークスペース</h2><p class="text-sm text-slate-500">PostgreSQLの設計済みデータをダッシュボードAPIから表示しています。</p></div><AppBadge class="ml-auto" value="DB連携済み" severity="success" /></div><div class="grid gap-3 sm:grid-cols-3"><div v-for="item in store.dashboard?.pipeline?.slice(0, 3)" :key="item.id" class="rounded-lg border border-slate-100 bg-slate-50 p-4"><p class="text-xs font-semibold text-slate-400">{{ item.stage_name }}</p><strong class="mt-2 block text-xl">{{ item.deals_count }}件</strong></div></div></article></section>
</template>
