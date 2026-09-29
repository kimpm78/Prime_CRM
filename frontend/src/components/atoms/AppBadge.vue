<script setup lang="ts">
import VoltTag from '@/components/ui/Tag.vue'

withDefaults(defineProps<{ value?: string | number; severity?: string; rounded?: boolean }>(), { rounded: true })
</script>

<template>
  <VoltTag v-bind="$props"><slot /></VoltTag>
</template>
