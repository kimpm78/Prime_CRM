<script setup lang="ts">
import VoltInputText from '@/components/ui/InputText.vue'

defineOptions({ inheritAttrs: false })
const model = defineModel<string>({ default: '' })
defineProps<{ invalid?: boolean }>()
</script>

<template>
  <VoltInputText v-model="model" v-bind="$attrs" :invalid="invalid" class="w-full text-sm" />
</template>
