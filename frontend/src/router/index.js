import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import SessionSlideView from '@/Views/SessionSlideView.vue'

const routes = [
  // ─── Auth ───────────────────────────────────────
  {
    path: '/',
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
  // {
  //   path: '/',
  //   component: () => import('@/layouts/AppLayout.vue'),
  //   meta: { requiresAuth: true },
  //   children: [
  //     { path: '', redirect: '/chat' },
  //     { path: 'chat', name: 'chat', component: () => import('@/Views/chat/HomeView.vue') },
  //     { path: 'chat/:id', name: 'conversation', component: () => import('@/Views/chat/ConversationView.vue') },
  //     { path: 'profile', name: 'profile', component: () => import('@/Views/profile/ProfileView.vue') },
  //   ],
  // },

  // 404
  // { path: '/:pathMatch(.*)*', redirect: '/chat' },
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