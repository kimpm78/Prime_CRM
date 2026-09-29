<script setup lang="ts">
import AppButton from '@/components/atoms/AppButton.vue'
import AppIcon from '@/components/atoms/AppIcon.vue'
import AppInput from '@/components/atoms/AppInput.vue'

const model = defineModel<string>({ default: '' })
const emit = defineEmits<{ search: [] }>()
withDefaults(defineProps<{ placeholder?: string }>(), {
  placeholder: '取引先名・コード・業種・電話番号で検索',
})
</script>

<template>
  <form class="flex w-full max-w-xl items-center gap-2" @submit.prevent="emit('search')">
    <div class="relative min-w-0 flex-1">
      <AppIcon name="search" :size="17" class="pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-slate-400" />
      <AppInput v-model="model" type="search" :placeholder="placeholder" class="pl-10" />
    </div>
    <AppButton type="submit" label="検索" />
  </form>
</template>
