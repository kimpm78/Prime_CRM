import { createRouter, createWebHistory } from 'vue-router'
const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', name: 'dashboard', component: () => import('@/views/DashboardView.vue'), meta: { title: 'ダッシュボード' } },
    { path: '/customers', name: 'customers', component: () => import('@/views/CustomersView.vue'), meta: { title: '取引先' } },
    { path: '/:module(leads|opportunities|tasks|cases)', name: 'module', component: () => import('@/views/ModuleView.vue') },
  ],
})

router.afterEach((to) => {
  const title = typeof to.meta.title === 'string' ? to.meta.title : 'CRM'
  document.title = `${title} | Prime CRM`
})

export default router
