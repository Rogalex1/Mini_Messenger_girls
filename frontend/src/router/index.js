import { createRouter, createWebHistory } from 'vue-router'
import SessionSlideView from '@/Views/SessionSlideView.vue'

const routes = [
  // ─── Auth ───────────────────────────────────────
  {
    path: '/login',
    component: () => import('@/Layouts/AuthLayout.vue'),
    children: [
      { path: '', name: 'login', component: () => import('@/Views/auth/LoginView.vue') },
    ],
    meta: { guest: true },
  },

  {
    path: "/sessionSlide",
    name: "sessionSlide",
    component: SessionSlideView,
  },

  {
    path: '/register',
    component: () => import('@/Layouts/AuthLayout.vue'),
    children: [
      { path: '', name: 'register', component: () => import('@/Views/auth/RegisterView.vue') },
    ],
    meta: { guest: true },
  },

  // ─── App ────────────────────────────────────────
  {
    path: '/',
    component: () => import('@/Layouts/AppLayout.vue'),
    redirect: '/statuses',
    children: [
      { path: 'statuses', name: 'statuses', component: () => import('@/Views/StatusView.vue') },
      { path: 'posts', name: 'posts', component: () => import('@/Views/PostsView.vue') },
      { path: 'profile', name: 'profile', component: () => import('@/Views/ProfileView.vue') },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Guard global
// router.beforeEach(async (to) => {
//   const auth = useAuthStore()

//   if (to.meta.requiresAuth && !auth.isLoggedIn) {
//     return { name: 'login' }
//   }
//   if (to.meta.guest && auth.isLoggedIn) {
//     return { name: 'chat' }
//   }
// })

export default router