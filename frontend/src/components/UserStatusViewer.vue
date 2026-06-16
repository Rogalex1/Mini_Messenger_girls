<template>
  <div class="status-viewer-overlay" @click.self="$emit('close')">
    <div class="status-viewer">
      <!-- Header -->
      <div class="viewer-header">
        <button @click="$emit('close')" class="back-btn">←</button>
        <div class="user-info">
          <div class="avatar" :style="{ background: getGradientColor(user.id) }">
            {{ user.profile?.first_name?.[0] || user.username?.[0] }}
          </div>
          <div class="user-details">
            <span class="username">
              {{ user.profile?.first_name }} {{ user.profile?.last_name || user.username }}
            </span>
          </div>
        </div>
      </div>

      <!-- Progress bar -->
      <div class="progress-container">
        <div 
          v-for="(status, index) in user.statuses" 
          :key="status.id"
          class="progress-item"
        >
          <div 
            class="progress-bar" 
            :class="{ active: currentIndex === index, viewed: currentIndex > index }"
          >
            <div 
              class="progress-fill" 
              v-if="currentIndex === index"
              :style="{ animationDuration: `${displayDuration}ms` }"
            ></div>
          </div>
        </div>
      </div>

      <!-- Current status -->
      <div class="status-content">
        <div v-if="user.statuses[currentIndex]?.type === 'image'" class="status-media">
          <img 
            :src="user.statuses[currentIndex]?.media_url" 
            alt="Status" 
            class="media-full"
          />
        </div>
        <div v-else-if="user.statuses[currentIndex]?.type === 'video'" class="status-media">
          <video 
            :src="user.statuses[currentIndex]?.media_url" 
            class="media-full" 
            controls 
            autoplay
          ></video>
        </div>
        <div v-else class="status-text-content">
          <p class="status-text">{{ user.statuses[currentIndex]?.caption }}</p>
        </div>
        <div v-if="user.statuses[currentIndex]?.caption && user.statuses[currentIndex]?.type !== 'text'" class="caption-overlay">
          {{ user.statuses[currentIndex].caption }}
        </div>
      </div>

      <!-- Navigation -->
      <div class="navigation-overlay">
        <div class="nav-left" @click="prevStatus"></div>
        <div class="nav-right" @click="nextStatus"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  user: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close'])

const currentIndex = ref(0)
const displayDuration = 5000 // 5 secondes par statut
let timer = null

const getGradientColor = (id) => {
  const colors = [
    'linear-gradient(135deg, #ec4899 0%, #db2777 100%)',
    'linear-gradient(135deg, #f472b6 0%, #ec4899 100%)',
    'linear-gradient(135deg, #be185d 0%, #9f1239 100%)',
    'linear-gradient(135deg, #fdf2f8 0%, #fbcfe8 100%)',
  ]
  return colors[id % colors.length]
}

const nextStatus = () => {
  if (currentIndex.value < props.user.statuses.length - 1) {
    currentIndex.value++
    resetTimer()
  } else {
    emit('close')
  }
}

const prevStatus = () => {
  if (currentIndex.value > 0) {
    currentIndex.value--
    resetTimer()
  }
}

const resetTimer = () => {
  if (timer) clearTimeout(timer)
  timer = setTimeout(nextStatus, displayDuration)
}

onMounted(() => {
  resetTimer()
})

onUnmounted(() => {
  if (timer) clearTimeout(timer)
})
</script>

<style scoped>
.status-viewer-overlay {
  position: fixed;
  inset: 0;
  background: black;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
}

.status-viewer {
  width: 100%;
  max-width: 500px;
  height: 100%;
  max-height: 900px;
  position: relative;
  background: var(--gc-gray-900);
}

.viewer-header {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  z-index: 10;
  padding: var(--gc-spacing-md);
  display: flex;
  align-items: center;
  gap: var(--gc-spacing-sm);
  background: linear-gradient(to bottom, rgba(0,0,0,0.5), transparent);
}

.back-btn {
  background: none;
  border: none;
  color: white;
  font-size: 24px;
  cursor: pointer;
  padding: var(--gc-spacing-xs);
}

.user-info {
  display: flex;
  align-items: center;
  gap: var(--gc-spacing-sm);
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: var(--gc-font-size-lg);
}

.username {
  color: white;
  font-weight: 600;
}

.progress-container {
  position: absolute;
  top: 70px;
  left: var(--gc-spacing-md);
  right: var(--gc-spacing-md);
  z-index: 10;
  display: flex;
  gap: var(--gc-spacing-xs);
}

.progress-item {
  flex: 1;
  height: 3px;
  background: rgba(255,255,255,0.3);
  border-radius: 3px;
  overflow: hidden;
}

.progress-bar.active .progress-fill {
  height: 100%;
  background: white;
  animation: progress linear forwards;
}

.progress-bar.viewed {
  background: white;
}

@keyframes progress {
  from { width: 0%; }
  to { width: 100%; }
}

.status-content {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.media-full {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.status-text-content {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: var(--gc-spacing-xl);
}

.status-text {
  color: white;
  font-size: var(--gc-font-size-xl);
  text-align: center;
  line-height: 1.5;
}

.caption-overlay {
  position: absolute;
  bottom: 100px;
  left: var(--gc-spacing-md);
  right: var(--gc-spacing-md);
  color: white;
  text-align: center;
  padding: var(--gc-spacing-md);
  background: rgba(0,0,0,0.5);
  border-radius: var(--gc-radius-lg);
}

.navigation-overlay {
  position: absolute;
  inset: 0;
  display: flex;
}

.nav-left, .nav-right {
  flex: 1;
}
</style>
