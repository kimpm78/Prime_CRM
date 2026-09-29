<script setup lang="ts">
import VoltSelect from '@/components/ui/Select.vue'

const model = defineModel<string>({ default: 'active' })
withDefaults(defineProps<{ disabled?: boolean; options?: Array<{ label: string; value: string }> }>(), {
  options: () => [{ label: '有効', value: 'active' }, { label: '無効', value: 'inactive' }],
})
</script>

<template>
  <VoltSelect v-model="model" :options="options" option-label="label" option-value="value" :disabled="disabled" class="w-full text-sm" />
</template>
