<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import AppButton from '@/components/atoms/AppButton.vue'
import AppIcon from '@/components/atoms/AppIcon.vue'
import { useAuthStore } from '@/stores/auth'

defineProps<{ open: boolean }>()
defineEmits<{ close: [] }>()

const auth = useAuthStore()
const router = useRouter()
const initial = computed(() => auth.user?.name.charAt(0) || 'P')
const roleLabel = computed(() => ({ admin: '管理者', sales: '営業担当', viewer: '閲覧者' }[auth.user?.role ?? ''] ?? 'ユーザー'))
const logout = async () => {
  await auth.logout()
  await router.replace({ name: 'login' })
}

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
        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-slate-700 text-xs font-bold text-white">{{ initial }}</span>
        <div class="min-w-0 flex-1"><strong class="block truncate text-xs text-white">{{ auth.user?.name }}</strong><span class="block truncate text-[10px] text-slate-500">{{ auth.user?.department || '所属未設定' }}・{{ roleLabel }}</span></div>
        <AppButton text rounded aria-label="ログアウト" :loading="auth.loading" @click="logout"><AppIcon name="logout" :size="17" /></AppButton>
      </div>
    </div>
  </aside>
</template>
