<template>
  <div class="friends-view">
    <div class="tabs">
      <button :class="['tab', activeTab === 'friends' ? 'active' : '']" @click="activeTab = 'friends'">
        Amis ({{ friends.length }})
      </button>
      <button :class="['tab', activeTab === 'requests' ? 'active' : '']" @click="activeTab = 'requests'">
        Demandes ({{ requests.length }})
      </button>
    </div>

    <!-- Friends List -->
    <div v-if="activeTab === 'friends'" class="friends-list">
      <div v-for="friend in friends" :key="friend.id" class="friend-item gc-card">
        <div class="avatar" :style="{ background: getGradientColor(friend.id) }">
          {{ friend.name.charAt(0) }}
        </div>
        <div class="friend-info">
          <span class="name">{{ friend.name }}</span>
          <span class="status" :class="{ online: friend.online }">{{ friend.online ? 'En ligne' : 'Hors ligne' }}</span>
        </div>
        <button class="gc-btn gc-btn-secondary" style="padding: var(--gc-spacing-xs) var(--gc-spacing-md); font-size: var(--gc-font-size-sm);">💬</button>
      </div>
    </div>

    <!-- Requests List -->
    <div v-if="activeTab === 'requests'" class="requests-list">
      <div v-for="req in requests" :key="req.id" class="request-item gc-card">
        <div class="avatar" :style="{ background: getGradientColor(req.id) }">
          {{ req.name.charAt(0) }}
        </div>
        <div class="request-info">
          <span class="name">{{ req.name }}</span>
          <span class="mutual">{{ req.mutual }} amis en commun</span>
        </div>
        <div class="request-actions">
          <button class="gc-btn gc-btn-secondary" style="padding: var(--gc-spacing-xs) var(--gc-spacing-md);">✖</button>
          <button class="gc-btn gc-btn-primary" style="padding: var(--gc-spacing-xs) var(--gc-spacing-md);">✓</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const activeTab = ref('friends')
const friends = ref([
  { id: 1, name: 'Sarah Chen', online: true },
  { id: 2, name: 'Emma Wilson', online: true },
  { id: 3, name: 'Julie Martin', online: false },
])
const requests = ref([
  { id: 1, name: 'Lucas Dupont', mutual: 3 },
  { id: 2, name: 'Manon Leroy', mutual: 5 },
])

const getGradientColor = (id) => {
  const colors = [
    'linear-gradient(135deg, #ec4899 0%, #db2777 100%)',
    'linear-gradient(135deg, #f472b6 0%, #ec4899 100%)',
    'linear-gradient(135deg, #be185d 0%, #9f1239 100%)',
    'linear-gradient(135deg, #fdf2f8 0%, #fbcfe8 100%)',
  ]
  return colors[id % colors.length]
}
</script>

<style scoped>
/* Mobile First */
.friends-view {
  max-width: 100%;
}

.tabs {
  display: flex;
  gap: var(--gc-spacing-sm);
  margin-bottom: var(--gc-spacing-lg);
  background: var(--gc-gray-100);
  padding: 4px;
  border-radius: var(--gc-radius-lg);
}

.tab {
  flex: 1;
  padding: var(--gc-spacing-sm);
  border: none;
  border-radius: var(--gc-radius-md);
  font-weight: 600;
  cursor: pointer;
  background: transparent;
  color: var(--gc-gray-600);
  font-size: var(--gc-font-size-sm);
  transition: all var(--gc-transition-fast);
}

.tab.active {
  background: white;
  color: var(--gc-primary);
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.friends-list, .requests-list {
  display: flex;
  flex-direction: column;
  gap: var(--gc-spacing-sm);
}

.friend-item, .request-item {
  display: flex;
  align-items: center;
  gap: var(--gc-spacing-md);
  padding: var(--gc-spacing-md);
}

.avatar {
  width: 45px;
  height: 45px;
  border-radius: var(--gc-radius-full);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  flex-shrink: 0;
}

.friend-info, .request-info {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}

.name {
  font-weight: 600;
  color: var(--gc-gray-800);
  font-size: var(--gc-font-size-sm);
}

.status, .mutual {
  font-size: var(--gc-font-size-xs);
  color: var(--gc-gray-500);
}

.status.online {
  color: #10b981;
}

.request-actions {
  display: flex;
  gap: var(--gc-spacing-sm);
}

/* Desktop */
@media (min-width: 768px) {
  .friends-view {
    max-width: 700px;
    margin: 0 auto;
  }
  
  .tab {
    font-size: var(--gc-font-size-base);
  }
}
</style>
