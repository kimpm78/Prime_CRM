<script setup lang="ts">
import VoltButton from '@/components/ui/Button.vue'

withDefaults(defineProps<{
  label?: string
  severity?: string
  type?: 'button' | 'submit' | 'reset'
  disabled?: boolean
  loading?: boolean
  text?: boolean
  outlined?: boolean
  rounded?: boolean
}>(), { type: 'button' })
</script>

<template>
  <VoltButton v-bind="$props" class="text-sm font-semibold">
    <slot />
  </VoltButton>
</template>
