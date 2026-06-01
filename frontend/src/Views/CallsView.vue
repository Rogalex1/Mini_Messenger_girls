<template>
  <div class="calls-view">
    <div class="page-header">
      <h1 class="page-title">📞 Appels</h1>
    </div>

    <div class="calls-list">
      <div v-for="call in calls" :key="call.id" class="call-item gc-card">
        <div class="avatar" :style="{ background: getGradientColor(call.id) }">
          {{ call.name.charAt(0) }}
        </div>
        <div class="call-info">
          <div class="name-icon">
            <span class="name">{{ call.name }}</span>
            <span :class="['icon', call.type]">{{ call.type === 'incoming' ? '📥' : '📤' }}</span>
          </div>
          <span class="time">{{ call.time }}</span>
        </div>
        <button class="gc-btn gc-btn-primary" style="padding: var(--gc-spacing-xs) var(--gc-spacing-md);">📞</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const calls = ref([
  { id: 1, name: 'Sarah Chen', type: 'incoming', time: 'Aujourd\'hui 12:15' },
  { id: 2, name: 'Emma Wilson', type: 'outgoing', time: 'Hier 18:40' },
  { id: 3, name: 'Julie Martin', type: 'incoming', time: 'Il y a 2 jours' },
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
.calls-view {
  max-width: 100%;
}

.page-header {
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

.calls-list {
  display: flex;
  flex-direction: column;
  gap: var(--gc-spacing-sm);
}

.call-item {
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

.call-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.name-icon {
  display: flex;
  align-items: center;
  gap: var(--gc-spacing-xs);
}

.name {
  font-weight: 600;
  color: var(--gc-gray-800);
  font-size: var(--gc-font-size-sm);
}

.icon {
  font-size: var(--gc-font-size-sm);
}

.icon.incoming {
  color: var(--gc-gray-500);
}

.icon.outgoing {
  color: var(--gc-primary);
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
  
  .calls-view {
    max-width: 700px;
    margin: 0 auto;
  }
}
</style>
