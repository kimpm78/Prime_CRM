<script setup lang="ts">
import { RouterLink } from 'vue-router'
import AppButton from '@/components/atoms/AppButton.vue'
import AppIcon from '@/components/atoms/AppIcon.vue'

defineProps<{ open: boolean }>()
defineEmits<{ close: [] }>()

const nav = [
  { to: '/', label: 'ダッシュボード', icon: 'dashboard' },
  { to: '/customers', label: '取引先', icon: 'building' },
  { to: '/leads', label: 'リード', icon: 'leads' },
  { to: '/opportunities', label: '商談', icon: 'handshake' },
  { to: '/tasks', label: 'タスク', icon: 'tasks' },
  { to: '/cases', label: '問い合わせ', icon: 'cases' },
]
</script>

<template>
  <div v-if="open" class="fixed inset-0 z-40 bg-slate-950/50 lg:hidden" @click="$emit('close')" />
  <aside :class="['fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-[#121a2e] px-4 py-5 text-slate-300 transition-transform lg:translate-x-0', open ? 'translate-x-0' : '-translate-x-full']">
    <div class="mb-8 flex items-center justify-between px-2">
      <div class="flex items-center gap-3">
        <span class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-blue-500 to-violet-500 text-lg font-bold text-white shadow-lg shadow-blue-950">P</span>
        <div><strong class="block text-lg leading-none text-white">Prime</strong><span class="text-[10px] font-bold tracking-[0.28em] text-slate-500">CRM</span></div>
      </div>
      <AppButton class="lg:hidden" severity="secondary" text rounded aria-label="閉じる" @click="$emit('close')"><AppIcon name="close" /></AppButton>
    </div>
    <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-widest text-slate-600">Workspace</p>
    <nav class="space-y-1">
      <RouterLink v-for="item in nav" :key="item.to" :to="item.to" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold text-slate-400 transition hover:bg-slate-800 hover:text-white" active-class="!bg-blue-600 !text-white shadow-lg shadow-slate-950/30" @click="$emit('close')">
        <AppIcon :name="item.icon" :size="18" />{{ item.label }}
      </RouterLink>
    </nav>
    <div class="mt-auto border-t border-slate-800 pt-4">
      <div class="flex items-center gap-3 rounded-lg px-2 py-2">
        <span class="grid h-9 w-9 place-items-center rounded-full bg-slate-700 text-xs font-bold text-white">佐</span>
        <div><strong class="block text-xs text-white">佐藤 美咲</strong><span class="text-[10px] text-slate-500">営業企画・管理者</span></div>
      </div>
    </div>
  </aside>
</template>
