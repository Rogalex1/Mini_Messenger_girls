<template>
  <div class="conversation-view">
    <div class="conversation-header gc-glass">
      <router-link to="/messages" class="back-btn">←</router-link>
      <div class="user-info">
        <div class="avatar" :style="{ background: getGradientColor(1) }">S</div>
        <div class="details">
          <h2 class="name">Sarah Chen</h2>
          <span class="status">En ligne</span>
        </div>
      </div>
    </div>

    <div class="messages-container">
      <div class="messages-list">
        <div v-for="msg in messages" :key="msg.id" :class="['message', msg.fromMe ? 'from-me' : 'from-other']">
          <div class="message-bubble">
            <p>{{ msg.text }}</p>
            <span class="message-time">{{ msg.time }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="input-container">
      <input type="text" placeholder="Écris un message..." class="gc-input">
      <button class="send-btn gc-btn gc-btn-primary">→</button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const messages = ref([
  { id: 1, text: 'Salut ! Ça va ?', time: '12:20', fromMe: false },
  { id: 2, text: 'Ça va bien merci ! Et toi ?', time: '12:21', fromMe: true },
  { id: 3, text: 'Très bien ! On se voit demain pour le café ?', time: '12:22', fromMe: false },
])

const getGradientColor = (id) => {
  const colors = [
    'linear-gradient(135deg, #ec4899 0%, #db2777 100%)',
    'linear-gradient(135deg, #f472b6 0%, #ec4899 100%)',
    'linear-gradient(135deg, #be185d 0%, #9f1239 100%)',
  ]
  return colors[id % colors.length]
}
</script>

<style scoped>
/* Mobile First */
.conversation-view {
  display: flex;
  flex-direction: column;
  height: calc(100vh - 70px);
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 70px;
  background: linear-gradient(135deg, #fdf2f8 0%, #fff1f2 50%, #fef3c7 100%);
}

.conversation-header {
  display: flex;
  align-items: center;
  gap: var(--gc-spacing-md);
  padding: var(--gc-spacing-md);
  z-index: 10;
}

.back-btn {
  background: none;
  border: none;
  font-size: var(--gc-font-size-2xl);
  cursor: pointer;
  padding: var(--gc-spacing-xs);
  line-height: 1;
}

.conversation-header .user-info {
  display: flex;
  align-items: center;
  gap: var(--gc-spacing-md);
}

.conversation-header .avatar {
  width: 40px;
  height: 40px;
  border-radius: var(--gc-radius-full);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
}

.conversation-header .name {
  font-size: var(--gc-font-size-base);
  font-weight: 600;
  color: var(--gc-gray-800);
}

.conversation-header .status {
  font-size: var(--gc-font-size-xs);
  color: var(--gc-gray-500);
}

.messages-container {
  flex: 1;
  overflow-y: auto;
  padding: var(--gc-spacing-md);
}

.messages-list {
  display: flex;
  flex-direction: column;
  gap: var(--gc-spacing-md);
}

.message {
  display: flex;
}

.message.from-me {
  justify-content: flex-end;
}

.message.from-other {
  justify-content: flex-start;
}

.message-bubble {
  max-width: 75%;
  padding: var(--gc-spacing-sm) var(--gc-spacing-md);
  border-radius: var(--gc-radius-lg);
}

.message.from-me .message-bubble {
  background: linear-gradient(135deg, var(--gc-primary) 0%, var(--gc-secondary) 100%);
  color: white;
  border-bottom-right-radius: 4px;
}

.message.from-other .message-bubble {
  background: white;
  color: var(--gc-gray-800);
  border-bottom-left-radius: 4px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.message-time {
  display: block;
  font-size: 10px;
  opacity: 0.7;
  text-align: right;
  margin-top: 4px;
}

.input-container {
  display: flex;
  gap: var(--gc-spacing-sm);
  padding: var(--gc-spacing-md);
  background: var(--gc-glass-bg);
  backdrop-filter: var(--gc-glass-blur);
  border-top: 1px solid var(--gc-glass-border);
}

.send-btn {
  padding: var(--gc-spacing-sm) var(--gc-spacing-md);
  font-size: var(--gc-font-size-lg);
}

/* Desktop */
@media (min-width: 768px) {
  .conversation-view {
    height: 100vh;
    bottom: 0;
  }
  
  .conversation-header, .messages-container, .input-container {
    padding-left: 300px; /* Offset for sidebar */
  }
  
  .message-bubble {
    max-width: 50%;
  }
}
</style>
