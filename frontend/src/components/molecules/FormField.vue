<script setup lang="ts">
defineProps<{ label: string; forId?: string; required?: boolean; error?: string; hint?: string }>()
</script>

<template>
  <div class="space-y-1.5">
    <label :for="forId" class="block text-xs font-semibold text-slate-600">
      {{ label }} <span v-if="required" class="ml-1 text-rose-500">必須</span>
    </label>
    <slot />
    <p v-if="error" class="text-xs font-medium text-rose-600">{{ error }}</p>
    <p v-else-if="hint" class="text-xs text-slate-400">{{ hint }}</p>
  </div>
</template>
