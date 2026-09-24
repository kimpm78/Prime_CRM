import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/login', name: 'login', component: () => import('@/views/LoginView.vue'), meta: { title: 'ログイン', public: true } },
    { path: '/', name: 'dashboard', component: () => import('@/views/DashboardView.vue'), meta: { title: 'ダッシュボード' } },
    { path: '/customers', name: 'customers', component: () => import('@/views/CustomersView.vue'), meta: { title: '取引先' } },
    { path: '/leads', name: 'leads', component: () => import('@/views/LeadsView.vue'), meta: { title: 'リード' } },
    { path: '/opportunities', name: 'opportunities', component: () => import('@/views/OpportunitiesView.vue'), meta: { title: '商談' } },
    { path: '/tasks', name: 'tasks', component: () => import('@/views/TasksView.vue'), meta: { title: 'タスク' } },
    { path: '/cases', name: 'cases', component: () => import('@/views/CasesView.vue'), meta: { title: '問い合わせ' } },
  ],
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()
  await auth.initialize()

  if (to.meta.public === true) {
    return auth.authenticated ? { name: 'dashboard' } : true
  }

  if (!auth.authenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  return true
})

router.afterEach((to) => {
  const title = typeof to.meta.title === 'string' ? to.meta.title : 'CRM'
  document.title = `${title} | Prime CRM`
})

export default router
