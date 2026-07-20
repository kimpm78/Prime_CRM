<script setup lang="ts">
import { ref } from 'vue'
import { RouterView } from 'vue-router'
import AppSidebar from '@/components/organisms/AppSidebar.vue'
import AppButton from '@/components/atoms/AppButton.vue'
import AppIcon from '@/components/atoms/AppIcon.vue'

const mobileMenuOpen = ref(false)
</script>

<template>
  <div class="min-h-screen bg-slate-50 text-slate-900">
    <AppSidebar :open="mobileMenuOpen" @close="mobileMenuOpen = false" />
    <div class="lg:pl-64">
      <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200 bg-white/95 px-4 backdrop-blur md:px-8">
        <AppButton class="lg:hidden" severity="secondary" text rounded aria-label="メニュー" @click="mobileMenuOpen = true">
          <AppIcon name="menu" :size="20" />
        </AppButton>
        <div class="hidden items-center gap-2 text-xs font-semibold text-slate-400 sm:flex">
          <span class="h-2 w-2 rounded-full bg-emerald-500" />
          PostgreSQL 16 接続中
        </div>
        <AppButton severity="secondary" text rounded aria-label="通知">
          <AppIcon name="bell" :size="19" />
        </AppButton>
      </header>
      <main class="mx-auto max-w-[1500px] p-4 md:p-8">
        <RouterView />
      </main>
    </div>
  </div>
</template>
