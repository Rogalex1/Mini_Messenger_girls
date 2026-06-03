<template>
  <div class="gc-app">

    <!-- Sidebar icônes -->
    <nav class="gc-nav">
      <RouterLink to="/profile" class="gc-nav-avatar">
        <span>{{ initials }}</span>
        <div class="gc-nav-dot"></div>
      </RouterLink>

      <div class="gc-nav-links">
        <RouterLink to="/posts" class="gc-nav-icon" active-class="active" title="Fil d'actualité">
          <i class="ti ti-news"></i>
        </RouterLink>
        <RouterLink to="/messages" class="gc-nav-icon" active-class="active" title="Messages">
          <i class="ti ti-message-circle"></i>
        </RouterLink>
        <RouterLink to="/friends" class="gc-nav-icon" active-class="active" title="Amis">
          <i class="ti ti-users"></i>
        </RouterLink>
        <RouterLink to="/groups" class="gc-nav-icon" active-class="active" title="Groupes">
          <i class="ti ti-users-group"></i>
        </RouterLink>
        <RouterLink to="/statuses" class="gc-nav-icon" active-class="active" title="Statuts">
          <i class="ti ti-circle-dashed"></i>
        </RouterLink>
        <RouterLink to="/calls" class="gc-nav-icon" active-class="active" title="Appels">
          <i class="ti ti-phone"></i>
        </RouterLink>
        <RouterLink to="/notifications" class="gc-nav-icon" active-class="active" title="Notifications">
          <i class="ti ti-bell"></i>
          <span v-if="unreadCount" class="gc-nav-badge">{{ unreadCount }}</span>
        </RouterLink>
      </div>

      <div class="gc-nav-bottom">
        <button class="gc-nav-icon" @click="auth.logout()" title="Déconnexion">
          <i class="ti ti-logout"></i>
        </button>
      </div>
    </nav>

    <!-- Contenu principal -->
    <main class="gc-main">
      <RouterView :key="$route.path" />
    </main>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useChatStore } from '@/stores/chat'

const auth = useAuthStore()
const chatStore = useChatStore()
const unreadCount = ref(0) // À lier plus tard à un store de notifications

const initials = computed(() => {
  const u = auth.user
  if (!u) return '?'
  
  // Essayer de prendre les initiales du profil
  const fn = u.profile?.first_name?.[0] ?? ''
  const ln = u.profile?.last_name?.[0]  ?? ''
  const profileInitials = (fn + ln).toUpperCase()
  
  if (profileInitials) return profileInitials
  
  // Sinon prendre les 2 premières lettres du username
  return u.username?.substring(0, 2).toUpperCase() || '?'
})

onMounted(async () => {
  await auth.fetchMe()
  chatStore.subscribeToOnlineUsers()
})
</script>

<style scoped>
.gc-app {
  display: flex;
  height: 100vh;
  width: 100vw;
  overflow: hidden;
  background: #fdf2f8;
}

/* ─── Sidebar nav ─────────────────────────── */
.gc-nav {
  width: 72px;
  background: #fff;
  border-right: 1px solid rgba(236, 72, 153, 0.1);
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 20px 0;
  flex-shrink: 0;
  z-index: 100;
}

.gc-nav-avatar {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: 700;
  color: #fff;
  margin-bottom: 30px;
  position: relative;
  text-decoration: none;
  box-shadow: 0 4px 12px rgba(236, 72, 153, 0.3);
}

.gc-nav-dot {
  position: absolute;
  bottom: -2px;
  right: -2px;
  width: 12px;
  height: 12px;
  background: #22c55e;
  border-radius: 50%;
  border: 2px solid #fff;
}

.gc-nav-links {
  display: flex;
  flex-direction: column;
  gap: 12px;
  width: 100%;
  align-items: center;
}

.gc-nav-icon {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #9ca3af;
  text-decoration: none;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  background: transparent;
  border: none;
}

.gc-nav-icon i { 
  font-size: 24px; 
}

.gc-nav-icon:hover {
  color: #ec4899;
  background: #fff1f2;
}

.gc-nav-icon.active {
  background: #ec4899;
  color: #fff;
  box-shadow: 0 4px 12px rgba(236, 72, 153, 0.25);
}

.gc-nav-badge {
  position: absolute;
  top: 8px;
  right: 8px;
  background: #ef4444;
  color: #fff;
  font-size: 10px;
  font-weight: 700;
  padding: 2px 5px;
  border-radius: 8px;
  border: 2px solid #fff;
  line-height: 1;
}

.gc-nav-bottom {
  margin-top: auto;
}

/* ─── Main Content ────────────────────────── */
.gc-main {
  flex: 1;
  height: 100%;
  overflow: hidden;
  position: relative;
}

/* Mobile responsive */
@media (max-width: 768px) {
  .gc-app {
    flex-direction: column-reverse;
  }

  .gc-nav {
    width: 100%;
    height: 64px;
    flex-direction: row;
    padding: 0 16px;
    border-right: none;
    border-top: 1px solid rgba(236, 72, 153, 0.1);
    justify-content: space-between;
  }

  .gc-nav-avatar, .gc-nav-bottom {
    display: none;
  }

  .gc-nav-links {
    flex-direction: row;
    justify-content: space-around;
    gap: 0;
  }

  .gc-nav-icon {
    width: 50px;
    height: 50px;
  }
}
</style>