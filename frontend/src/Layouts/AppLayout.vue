<template>
  <div class="app-layout">
    <!-- Mobile Top Header with Logout -->
    <header class="mobile-header gc-glass">
      <div class="header-content">
        <div class="logo-mini">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
          </svg>
        </div>
        <button class="logout-btn-mobile" @click="logout" title="Déconnexion">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
        </button>
      </div>
    </header>

    <!-- Mobile Bottom Navigation (5 items like major social apps) -->
    <nav class="mobile-nav gc-glass">
      <router-link to="/posts" class="nav-item" active-class="active">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
          <polyline points="14 2 14 8 20 8"/>
          <line x1="16" y1="13" x2="8" y2="13"/>
          <line x1="16" y1="17" x2="8" y2="17"/>
        </svg>
        <span class="nav-text">Posts</span>
      </router-link>
      <router-link to="/messages" class="nav-item" active-class="active">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
        <span class="nav-text">Messages</span>
        <span v-if="unreadMessages > 0" class="badge">{{ unreadMessages }}</span>
      </router-link>
      <router-link to="/friends" class="nav-item" active-class="active">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
          <circle cx="9" cy="7" r="4"/>
          <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
          <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
        <span class="nav-text">Amis</span>
        <span v-if="pendingRequests > 0" class="badge">{{ pendingRequests }}</span>
      </router-link>
      <router-link to="/statuses" class="nav-item" active-class="active">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
          <circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/>
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
          <polyline points="17 8 12 13 7 8"/>
        </svg>
        <span class="nav-text">Statuts</span>
      </router-link>
      <router-link to="/profile" class="nav-item" active-class="active">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
          <circle cx="12" cy="7" r="4"/>
        </svg>
        <span class="nav-text">Profil</span>
      </router-link>
    </nav>

    <!-- Desktop Sidebar -->
    <aside class="desktop-sidebar gc-glass">
      <div class="sidebar-header">
        <div class="logo-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
          </svg>
        </div>
        <h1 class="logo">GlowChat</h1>
      </div>

      <nav class="sidebar-nav">
        <router-link to="/posts" class="nav-item" active-class="active">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
          </svg>
          Posts
        </router-link>
        <router-link to="/messages" class="nav-item" active-class="active">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
          </svg>
          Messages
          <span v-if="unreadMessages > 0" class="badge">{{ unreadMessages }}</span>
        </router-link>
        <router-link to="/friends" class="nav-item" active-class="active">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
          Amis
          <span v-if="pendingRequests > 0" class="badge">{{ pendingRequests }}</span>
        </router-link>
        <router-link to="/statuses" class="nav-item" active-class="active">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
            <circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/>
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <polyline points="17 8 12 13 7 8"/>
          </svg>
          Statuts
        </router-link>
        <router-link to="/calls" class="nav-item" active-class="active">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
          </svg>
          Appels
        </router-link>
        <router-link to="/notifications" class="nav-item" active-class="active">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
          </svg>
          Notifications
        </router-link>
      </nav>

      <div class="sidebar-footer">
        <router-link to="/profile" class="profile-link">
          <div class="avatar" :style="{ background: getGradientColor(1) }">
            JD
          </div>
          <div class="user-info">
            <span class="name">Jane Doe</span>
            <span class="status">
              <span class="status-dot"></span>
              En ligne
            </span>
          </div>
        </router-link>
        <button class="logout-btn" @click="logout" title="Déconnexion">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const unreadMessages = ref(3)
const pendingRequests = ref(2)

const getGradientColor = (id) => {
  const colors = [
    'linear-gradient(135deg, #ec4899 0%, #db2777 100%)',
    'linear-gradient(135deg, #f472b6 0%, #ec4899 100%)',
    'linear-gradient(135deg, #be185d 0%, #9f1239 100%)',
    'linear-gradient(135deg, #fdf2f8 0%, #fbcfe8 100%)',
  ]
  return colors[id % colors.length]
}

const logout = () => {
  authStore.logout()
}
</script>

<style scoped>
/* === MOBILE FIRST - DEFAULT STYLES === */
.app-layout {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  padding-top: 60px;
  padding-bottom: 70px;
}

/* Mobile Top Header */
.mobile-header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  padding: var(--gc-spacing-sm) var(--gc-spacing-md);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 500px;
  margin: 0 auto;
}

.logo-mini {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
  border-radius: var(--gc-radius-md);
  color: white;
}

.logo-mini svg {
  width: 20px;
  height: 20px;
  stroke-width: 2.5;
}

.logout-btn-mobile {
  background: var(--gc-accent);
  border: none;
  padding: var(--gc-spacing-xs);
  border-radius: var(--gc-radius-md);
  cursor: pointer;
  color: var(--gc-primary);
  transition: all var(--gc-transition-fast);
  display: flex;
  align-items: center;
  justify-content: center;
}

.logout-btn-mobile:hover {
  background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
  color: white;
}

.logout-btn-mobile svg {
  width: 20px;
  height: 20px;
}

/* Mobile Bottom Navigation */
.mobile-nav {
  display: flex;
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  padding: var(--gc-spacing-xs) var(--gc-spacing-sm);
  border-top: 1px solid var(--gc-glass-border);
  justify-content: space-around;
  align-items: center;
  gap: var(--gc-spacing-xs);
  background: rgba(255, 255, 255, 0.85);
}

.mobile-nav .nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
  padding: var(--gc-spacing-xs) var(--gc-spacing-sm);
  border-radius: var(--gc-radius-lg);
  color: var(--gc-gray-500);
  text-decoration: none;
  font-weight: 600;
  transition: all var(--gc-transition-base);
  font-size: var(--gc-font-size-xs);
  position: relative;
}

.mobile-nav .nav-icon {
  width: 24px;
  height: 24px;
}

.mobile-nav .nav-item:hover {
  color: var(--gc-primary);
  background: var(--gc-accent);
}

.mobile-nav .nav-item.active {
  background: linear-gradient(135deg, var(--gc-primary) 0%, var(--gc-secondary) 100%);
  color: var(--gc-white);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(236, 72, 153, 0.3);
}

.mobile-nav .badge {
  position: absolute;
  top: 0;
  right: 4px;
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
  font-size: 9px;
  font-weight: 800;
  padding: 2px 6px;
  border-radius: 12px;
  min-width: 16px;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.15);
}

/* Hide Desktop Sidebar on Mobile */
.desktop-sidebar {
  display: none;
}

.main-content {
  flex: 1;
  padding: var(--gc-spacing-md);
}

/* === TABLET & DESKTOP (768px+) === */
@media (min-width: 768px) {
  .app-layout {
    flex-direction: row;
    padding-top: 0;
    padding-bottom: 0;
  }

  .mobile-header {
    display: none;
  }

  .mobile-nav {
    display: none;
  }

  .desktop-sidebar {
    display: flex;
    flex-direction: column;
    width: 280px;
    padding: var(--gc-spacing-xl) var(--gc-spacing-lg);
    border-right: 1px solid var(--gc-glass-border);
    position: fixed;
    height: 100vh;
    left: 0;
    top: 0;
    background: rgba(255, 255, 255, 0.8);
  }

  .sidebar-header {
    margin-bottom: var(--gc-spacing-xl);
    display: flex;
    align-items: center;
    gap: var(--gc-spacing-md);
  }

  .logo-icon {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--gc-primary) 0%, var(--gc-secondary) 100%);
    border-radius: var(--gc-radius-lg);
    color: white;
  }

  .logo-icon svg {
    width: 24px;
    height: 24px;
    stroke-width: 2.5;
  }

  .logo {
    font-size: var(--gc-font-size-2xl);
    font-weight: 800;
    background: linear-gradient(135deg, var(--gc-primary) 0%, var(--gc-secondary) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    letter-spacing: -0.5px;
  }

  .sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: var(--gc-spacing-xs);
    flex: 1;
  }

  .desktop-sidebar .nav-item {
    display: flex;
    align-items: center;
    gap: var(--gc-spacing-md);
    padding: var(--gc-spacing-md) var(--gc-spacing-lg);
    border-radius: var(--gc-radius-xl);
    color: var(--gc-gray-600);
    text-decoration: none;
    font-weight: 600;
    transition: all var(--gc-transition-base);
    position: relative;
  }

  .desktop-sidebar .nav-icon {
    width: 22px;
    height: 22px;
    stroke-width: 2;
  }

  .desktop-sidebar .nav-item:hover {
    background: var(--gc-accent);
    color: var(--gc-primary);
    transform: translateX(4px);
  }

  .desktop-sidebar .nav-item.active {
    background: linear-gradient(135deg, var(--gc-primary) 0%, var(--gc-secondary) 100%);
    color: var(--gc-white);
    box-shadow: 0 4px 12px rgba(236, 72, 153, 0.25);
  }

  .desktop-sidebar .badge {
    margin-left: auto;
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    font-size: 11px;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 12px;
    min-width: 20px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.15);
  }

  .sidebar-footer {
    display: flex;
    align-items: center;
    gap: var(--gc-spacing-md);
    padding: var(--gc-spacing-md);
    margin-top: var(--gc-spacing-md);
    border-top: 1px solid var(--gc-gray-100);
    background: var(--gc-accent);
    border-radius: var(--gc-radius-xl);
  }

  .profile-link {
    display: flex;
    align-items: center;
    gap: var(--gc-spacing-md);
    text-decoration: none;
    flex: 1;
  }

  .sidebar-footer .avatar {
    width: 44px;
    height: 44px;
    border-radius: var(--gc-radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: var(--gc-font-size-base);
    flex-shrink: 0;
    box-shadow: 0 4px 8px rgba(236, 72, 153, 0.2);
  }

  .sidebar-footer .user-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
  }

  .sidebar-footer .name {
    font-weight: 700;
    color: var(--gc-gray-800);
    font-size: var(--gc-font-size-sm);
  }

  .sidebar-footer .status {
    font-size: var(--gc-font-size-xs);
    color: var(--gc-gray-500);
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .sidebar-footer .status-dot {
    width: 8px;
    height: 8px;
    background: #10b981;
    border-radius: 50%;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
  }

  .logout-btn {
    background: none;
    border: none;
    font-size: var(--gc-font-size-lg);
    cursor: pointer;
    padding: var(--gc-spacing-sm);
    color: var(--gc-gray-500);
    transition: all var(--gc-transition-fast);
    border-radius: var(--gc-radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .logout-btn:hover {
    background: white;
    color: var(--gc-primary);
  }

  .logout-btn svg {
    width: 20px;
    height: 20px;
  }

  .main-content {
    margin-left: 280px;
    padding: var(--gc-spacing-xl);
  }
}

/* === LARGER DESKTOP (1024px+) === */
@media (min-width: 1024px) {
  .desktop-sidebar {
    width: 310px;
  }

  .main-content {
    margin-left: 310px;
    padding: var(--gc-spacing-2xl);
  }
}
</style>
