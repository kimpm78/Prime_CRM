<script setup lang="ts">
import {
  AlertCircle, Bell, Building2, CheckCircle2, ChevronLeft, ChevronRight, CircleDollarSign,
  Eye, Handshake, LayoutDashboard, ListTodo, Menu, MessageSquareText, PackageOpen, Pencil,
  Plus, Save, Search, Settings, Trash2, UserRoundSearch, Users, X,
} from 'lucide-vue-next'
import type { Component } from 'vue'

const icons: Record<string, Component> = {
  alert: AlertCircle, bell: Bell, building: Building2, check: CheckCircle2, chevronLeft: ChevronLeft,
  chevronRight: ChevronRight, money: CircleDollarSign, eye: Eye, handshake: Handshake,
  dashboard: LayoutDashboard, tasks: ListTodo, menu: Menu, cases: MessageSquareText,
  products: PackageOpen, pencil: Pencil, plus: Plus, save: Save, search: Search,
  settings: Settings, trash: Trash2, leads: UserRoundSearch, users: Users, close: X,
}

const props = withDefaults(defineProps<{ name: keyof typeof icons | string; size?: number; strokeWidth?: number }>(), {
  size: 18,
  strokeWidth: 1.8,
})
</script>

<template>
  <component :is="icons[props.name] ?? AlertCircle" :size="props.size" :stroke-width="props.strokeWidth" aria-hidden="true" />
</template>
