<template>
  <div class="app-layout">
    <!-- Mobile Bottom Navbar -->
    <nav class="mobile-nav gc-glass">
      <router-link to="/statuses" class="nav-item" active-class="active">
        <span class="nav-icon">📱</span>
        <span class="nav-text">Statuts</span>
      </router-link>
      <router-link to="/posts" class="nav-item" active-class="active">
        <span class="nav-icon">📝</span>
        <span class="nav-text">Posts</span>
      </router-link>
      <router-link to="/profile" class="nav-item" active-class="active">
        <span class="nav-icon">👤</span>
        <span class="nav-text">Profil</span>
      </router-link>
    </nav>

    <!-- Desktop Sidebar -->
    <aside class="desktop-sidebar gc-glass">
      <div class="sidebar-header">
        <h1 class="logo">💖 GlowChat</h1>
      </div>
      <nav class="sidebar-nav">
        <router-link to="/statuses" class="nav-item" active-class="active">
          <span class="nav-icon">📱</span>
          Statuts
        </router-link>
        <router-link to="/posts" class="nav-item" active-class="active">
          <span class="nav-icon">📝</span>
          Publications
        </router-link>
        <router-link to="/profile" class="nav-item" active-class="active">
          <span class="nav-icon">👤</span>
          Profil
        </router-link>
      </nav>
    </aside>

    <main class="main-content">
      <router-view />
    </main>
  </div>
</template>

<script setup>
</script>

<style scoped>
/* Mobile First - Default Styles */
.app-layout {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  padding-bottom: 70px; /* Espace pour la navbar en bas */
}

/* Mobile Bottom Navbar */
.mobile-nav {
  display: flex;
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  padding: var(--gc-spacing-sm) var(--gc-spacing-md);
  border-top: 1px solid var(--gc-glass-border);
  justify-content: space-around;
  gap: var(--gc-spacing-xs);
}

.mobile-nav .nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
  padding: var(--gc-spacing-xs) var(--gc-spacing-sm);
  border-radius: var(--gc-radius-md);
  color: var(--gc-gray-600);
  text-decoration: none;
  font-weight: 500;
  transition: all var(--gc-transition-fast);
  font-size: var(--gc-font-size-xs);
}

.mobile-nav .nav-icon {
  font-size: var(--gc-font-size-xl);
}

.mobile-nav .nav-item:hover,
.mobile-nav .nav-item.active {
  background: linear-gradient(135deg, var(--gc-primary) 0%, var(--gc-secondary) 100%);
  color: var(--gc-white);
}

/* Hide Desktop Sidebar on Mobile */
.desktop-sidebar {
  display: none;
}

.main-content {
  flex: 1;
  padding: var(--gc-spacing-md);
}

/* Tablet and Up (768px+) */
@media (min-width: 768px) {
  .app-layout {
    flex-direction: row;
    padding-bottom: 0;
  }

  /* Hide Mobile Navbar */
  .mobile-nav {
    display: none;
  }

  /* Show Desktop Sidebar */
  .desktop-sidebar {
    display: flex;
    flex-direction: column;
    width: 260px;
    padding: var(--gc-spacing-xl) var(--gc-spacing-lg);
    border-right: 1px solid var(--gc-glass-border);
    position: fixed;
    height: 100vh;
    left: 0;
    top: 0;
  }

  .sidebar-header {
    margin-bottom: var(--gc-spacing-xl);
  }

  .logo {
    font-size: var(--gc-font-size-2xl);
    font-weight: 800;
    background: linear-gradient(135deg, var(--gc-primary) 0%, var(--gc-secondary) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: var(--gc-spacing-sm);
  }

  .desktop-sidebar .nav-item {
    display: flex;
    align-items: center;
    gap: var(--gc-spacing-md);
    padding: var(--gc-spacing-md) var(--gc-spacing-lg);
    border-radius: var(--gc-radius-lg);
    color: var(--gc-gray-600);
    text-decoration: none;
    font-weight: 500;
    transition: all var(--gc-transition-fast);
  }

  .desktop-sidebar .nav-item:hover,
  .desktop-sidebar .nav-item.active {
    background: linear-gradient(135deg, var(--gc-primary) 0%, var(--gc-secondary) 100%);
    color: var(--gc-white);
  }

  .desktop-sidebar .nav-icon {
    font-size: var(--gc-font-size-lg);
  }

  .main-content {
    margin-left: 260px;
    padding: var(--gc-spacing-xl);
  }
}

/* Desktop (1024px+) */
@media (min-width: 1024px) {
  .desktop-sidebar {
    width: 300px;
  }

  .main-content {
    margin-left: 300px;
  }
}
</style>
