<template>
  <div class="notifications-view">
    <div class="page-header">
      <h1 class="page-title">🔔 Notifications</h1>
      <button class="gc-btn gc-btn-secondary" style="font-size: var(--gc-font-size-sm); padding: var(--gc-spacing-xs) var(--gc-spacing-md);">
        Tout marquer lu
      </button>
    </div>

    <div class="notifications-list">
      <div v-for="notif in notifications" :key="notif.id" :class="['notification-item gc-card', { unread: notif.unread }]">
        <div class="avatar" :style="{ background: getGradientColor(notif.id) }">
          {{ notif.avatar }}
        </div>
        <div class="notification-info">
          <p class="text">
            <span class="username">{{ notif.username }}</span> {{ notif.text }}
          </p>
          <span class="time">{{ notif.time }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const notifications = ref([
  { id: 1, username: 'Sarah Chen', text: 'a aimé ton publication', avatar: '❤️', time: 'Il y a 5 min', unread: true },
  { id: 2, username: 'Emma Wilson', text: 'a commenté ton statut', avatar: '💬', time: 'Il y a 30 min', unread: true },
  { id: 3, username: 'Julie Martin', text: 'a accepté ta demande d\'ami', avatar: '👥', time: 'Il y a 2h', unread: false },
])

const getGradientColor = (id) => {
  const colors = [
    'linear-gradient(135deg, #ec4899 0%, #db2777 100%)',
    'linear-gradient(135deg, #f472b6 0%, #ec4899 100%)',
  ]
  return colors[id % colors.length]
}
</script>

<style scoped>
/* Mobile First */
.notifications-view {
  max-width: 100%;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--gc-spacing-lg);
  flex-wrap: wrap;
  gap: var(--gc-spacing-sm);
}

.page-title {
  font-size: var(--gc-font-size-xl);
  font-weight: 800;
  background: linear-gradient(135deg, var(--gc-primary) 0%, var(--gc-secondary) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.notifications-list {
  display: flex;
  flex-direction: column;
  gap: var(--gc-spacing-sm);
}

.notification-item {
  display: flex;
  gap: var(--gc-spacing-md);
  padding: var(--gc-spacing-md);
  transition: all var(--gc-transition-fast);
}

.notification-item.unread {
  background: var(--gc-accent);
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: var(--gc-radius-full);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  flex-shrink: 0;
  font-size: var(--gc-font-size-lg);
}

.notification-info {
  flex: 1;
  min-width: 0;
}

.text {
  font-size: var(--gc-font-size-sm);
  color: var(--gc-gray-700);
  margin-bottom: 4px;
  line-height: 1.4;
}

.username {
  font-weight: 600;
  color: var(--gc-gray-800);
}

.time {
  font-size: var(--gc-font-size-xs);
  color: var(--gc-gray-500);
}

/* Desktop */
@media (min-width: 768px) {
  .page-title {
    font-size: var(--gc-font-size-2xl);
  }
  
  .notifications-view {
    max-width: 700px;
    margin: 0 auto;
  }
  
  .text {
    font-size: var(--gc-font-size-base);
  }
}
</style>
