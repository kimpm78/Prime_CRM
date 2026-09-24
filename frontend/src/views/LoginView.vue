<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppButton from '@/components/atoms/AppButton.vue'
import AppIcon from '@/components/atoms/AppIcon.vue'
import AppInput from '@/components/atoms/AppInput.vue'
import { ApiError } from '@/services/api'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()
const email = ref('')
const password = ref('')
const isPasswordVisible = ref(false)
const error = ref('')

const submit = async () => {
  error.value = ''
  try {
    await auth.login({ email: email.value, password: password.value })
    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/'
    await router.replace(redirect)
  } catch (caught) {
    if (caught instanceof ApiError) {
      error.value = caught.errors.email?.[0] ?? caught.message
      return
    }
    error.value = 'ログインできませんでした。時間をおいて再度お試しください。'
  }
}
</script>

<template>
  <main class="relative grid min-h-screen place-items-center overflow-hidden bg-[#0d1528] px-4 py-10 text-slate-900">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(53,105,232,.24),transparent_34%),radial-gradient(circle_at_80%_75%,rgba(124,58,237,.18),transparent_36%)]" />
    <section class="relative w-full max-w-md rounded-2xl border border-white/10 bg-white p-7 shadow-2xl shadow-black/30 sm:p-9" aria-labelledby="login-title">
      <header class="mb-8 text-center">
        <span class="mx-auto mb-4 grid h-14 w-14 place-items-center rounded-2xl bg-gradient-to-br from-blue-500 to-violet-600 text-2xl font-bold text-white shadow-lg shadow-blue-200">P</span>
        <p class="text-[11px] font-bold tracking-[0.3em] text-blue-600">PRIME CRM</p>
        <h1 id="login-title" class="mt-2 text-2xl font-bold tracking-tight">ログイン</h1>
        <p class="mt-2 text-sm text-slate-500">登録済みのアカウントでCRMにアクセスします</p>
      </header>

      <form class="space-y-5" @submit.prevent="submit">
        <label class="block">
          <span class="mb-2 block text-xs font-bold text-slate-600">メールアドレス</span>
          <AppInput v-model="email" type="email" autocomplete="username" placeholder="name@example.com" :invalid="Boolean(error)" required />
        </label>
        <div>
          <label for="password" class="mb-2 block text-xs font-bold text-slate-600">パスワード</label>
          <div class="relative">
            <AppInput
              id="password"
              v-model="password"
              :type="isPasswordVisible ? 'text' : 'password'"
              autocomplete="current-password"
              placeholder="パスワードを入力"
              class="pr-11"
              :invalid="Boolean(error)"
              required
            />
            <button
              type="button"
              class="absolute right-1.5 top-1/2 grid h-9 w-9 -translate-y-1/2 place-items-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-blue-600"
              :aria-label="isPasswordVisible ? 'パスワードを非表示にする' : 'パスワードを表示する'"
              :aria-pressed="isPasswordVisible"
              :title="isPasswordVisible ? 'パスワードを非表示にする' : 'パスワードを表示する'"
              @click="isPasswordVisible = !isPasswordVisible"
            >
              <AppIcon :name="isPasswordVisible ? 'eyeOff' : 'eye'" :size="18" />
            </button>
          </div>
        </div>
        <p v-if="error" role="alert" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2.5 text-sm text-rose-700">{{ error }}</p>
        <AppButton type="submit" class="!w-full !justify-center" :loading="auth.loading" :disabled="auth.loading">ログイン</AppButton>
      </form>
    </section>
  </main>
</template>
