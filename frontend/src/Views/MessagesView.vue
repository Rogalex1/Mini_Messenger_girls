<template>
  <div class="messages-view">
    <div class="page-header">
      <h1 class="page-title">💬 Messages</h1>
      <button class="gc-btn gc-btn-primary">
        <span>+</span>
      </button>
    </div>

    <div class="conversations-list">
      <div v-for="conv in conversations" :key="conv.id" class="conversation-item gc-card">
        <div class="avatar" :style="{ background: getGradientColor(conv.id) }">
          {{ conv.name.charAt(0) }}
        </div>
        <div class="conversation-info">
          <div class="name-time">
            <span class="name">{{ conv.name }}</span>
            <span class="time">{{ conv.lastMessageTime }}</span>
          </div>
          <div class="last-message-preview">
            <span>{{ conv.lastMessage }}</span>
            <span v-if="conv.unread > 0" class="unread-badge">{{ conv.unread }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const conversations = ref([
  { id: 1, name: 'Sarah Chen', lastMessage: 'On se voit demain ?', lastMessageTime: '12:30', unread: 2 },
  { id: 2, name: 'Emma Wilson', lastMessage: 'Merci pour le message !', lastMessageTime: '11:15', unread: 0 },
  { id: 3, name: 'Groupe Projet', lastMessage: 'Julie a envoyé un fichier', lastMessageTime: 'Hier', unread: 0 },
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
.messages-view {
  max-width: 100%;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--gc-spacing-lg);
}

.page-title {
  font-size: var(--gc-font-size-xl);
  font-weight: 800;
  background: linear-gradient(135deg, var(--gc-primary) 0%, var(--gc-secondary) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.conversations-list {
  display: flex;
  flex-direction: column;
  gap: var(--gc-spacing-sm);
}

.conversation-item {
  display: flex;
  align-items: center;
  gap: var(--gc-spacing-md);
  padding: var(--gc-spacing-md);
  cursor: pointer;
  transition: all var(--gc-transition-fast);
}

.conversation-item:hover {
  transform: translateX(4px);
  box-shadow: 0 4px 12px rgba(236, 72, 153, 0.1);
}

.avatar {
  width: 50px;
  height: 50px;
  border-radius: var(--gc-radius-full);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: var(--gc-font-size-lg);
  flex-shrink: 0;
}

.conversation-info {
  flex: 1;
  min-width: 0;
}

.name-time {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
}

.name {
  font-weight: 600;
  color: var(--gc-gray-800);
  font-size: var(--gc-font-size-sm);
}

.time {
  font-size: var(--gc-font-size-xs);
  color: var(--gc-gray-500);
}

.last-message-preview {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.last-message-preview span:first-child {
  font-size: var(--gc-font-size-sm);
  color: var(--gc-gray-600);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  flex: 1;
}

.unread-badge {
  background: linear-gradient(135deg, var(--gc-primary) 0%, var(--gc-secondary) 100%);
  color: white;
  font-size: 10px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 12px;
  min-width: 20px;
  text-align: center;
}

/* Desktop */
@media (min-width: 768px) {
  .page-title {
    font-size: var(--gc-font-size-2xl);
  }
  
  .messages-view {
    max-width: 700px;
    margin: 0 auto;
  }
}
</style>
