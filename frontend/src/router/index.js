import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
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

  // ─── App (Protected) ────────────────────────────────
  {
    path: '/',
    component: () => import('@/Layouts/AppLayout.vue'),
    redirect: '/posts',
    meta: { requiresAuth: true },
    children: [
      // Publications (nouvelle page d'accueil)
      { path: 'posts', name: 'posts', component: () => import('@/Views/PostsView.vue') },
      { path: 'homeview', name: 'homeview', component: () => import('@/Views/HomeView.vue') },
      
      // Messages & Conversations
      { 
        path: 'messages', 
        name: 'messages', 
        component: () => import('@/Views/HomeView.vue'),
        children: [
          { path: ':id', name: 'conversation', component: () => import('@/Views/ConversationView.vue') },
        ]
      },
      
      // Amis & Demandes
      { path: 'friends', name: 'friends', component: () => import('@/Views/FriendsView.vue') },

      //Groups (CHRISTELLE)
      { path: 'groups', name: 'groups', component: () => import('@/Views/GroupsView.vue') },
      
      // Membres / Contacts
      { path: 'users', name: 'users', component: () => import('@/Views/Users.vue') },
      
      // Statuts
      { path: 'statuses', name: 'statuses', component: () => import('@/Views/StatusView.vue') },
      
      // Appels
      { path: 'calls', name: 'calls', component: () => import('@/Views/CallsView.vue') },
      
      // Notifications
      { path: 'notifications', name: 'notifications', component: () => import('@/Views/NotificationsView.vue') },
      
      // Profil
      { path: 'profile', name: 'profile', component: () => import('@/Views/ProfileView.vue') },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Guard global
router.beforeEach(async (to) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isLoggedIn) {
    return { name: 'login' }
  }
  if (to.meta.guest && auth.isLoggedIn) {
    return { name: 'posts' }
  }
})

export default router
