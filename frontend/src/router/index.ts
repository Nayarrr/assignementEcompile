import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/services',
    },
    {
      path: '/services',
      name: 'services',
      component: () => import('@/views/ServicesView.vue'),
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/LoginView.vue'),
      meta: { guest: true },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/views/RegisterView.vue'),
      meta: { guest: true },
    },
    {
      path: '/bookings',
      name: 'bookings',
      component: () => import('@/views/BookingsView.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/admin/services',
      name: 'admin-services',
      component: () => import('@/views/AdminServicesView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true },
    },
    {
      path: '/admin/bookings',
      name: 'admin-bookings',
      component: () => import('@/views/AdminBookingsView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true },
    },
  ],
})

// Navigation guard - synchronous now
router.beforeEach((to, _from, next) => {
  const authStore = useAuthStore()
  authStore.init()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (to.meta.requiresAdmin && !authStore.isAdmin) {
    next('/bookings')
  } else if (to.meta.guest && authStore.isAuthenticated) {
    next('/bookings')
  } else {
    next()
  }
})

export default router
