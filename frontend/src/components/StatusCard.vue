<template>
  <div class="status-card gc-card">
    <div class="status-header">
      <div class="user-info">
        <div class="avatar" :style="{ background: getGradientColor(status.user.id) }">
          {{ status.user.profile?.first_name?.[0] || status.user.username?.[0] }}
        </div>
        <div class="user-details">
          <span class="username">
            {{ status.user.profile?.first_name }} {{ status.user.profile?.last_name || status.user.username }}
          </span>
          <span class="time">{{ formatTime(status.created_at) }}</span>
        </div>
      </div>
      <button v-if="isOwner" @click="handleDelete" class="delete-btn">🗑️</button>
    </div>

    <div v-if="status.caption" class="status-caption">
      {{ status.caption }}
    </div>

    <div v-if="status.media_url" class="status-media">
      <img v-if="status.type === 'image'" :src="status.media_url" alt="Status media" class="media-img" loading="lazy" />
      <video v-else-if="status.type === 'video'" :src="status.media_url" controls class="media-vid"></video>
    </div>

    <div class="status-footer">
      <div class="view-count">
        👁️ {{ status.views?.length || 0 }} vues
      </div>
      <button v-if="!isOwner && !isViewed" @click="handleView" class="view-btn gc-btn gc-btn-primary">
        Voir
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: {
    type: Object,
    required: true
  },
  isOwner: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['delete', 'view'])

const isViewed = computed(() => {
  // TODO: Implement actual viewed status check
  return false
})

const getGradientColor = (id) => {
  const colors = [
    'linear-gradient(135deg, #ec4899 0%, #db2777 100%)',
    'linear-gradient(135deg, #f472b6 0%, #ec4899 100%)',
    'linear-gradient(135deg, #be185d 0%, #9f1239 100%)',
    'linear-gradient(135deg, #fdf2f8 0%, #fbcfe8 100%)',
  ]
  return colors[id % colors.length]
}

const formatTime = (dateStr) => {
  const date = new Date(dateStr)
  return date.toLocaleString('fr-FR', {
    day: '2-digit',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const handleDelete = () => {
  emit('delete', props.status.id)
}

const handleView = () => {
  emit('view', props.status.id)
}
</script>

<style scoped>
/* Mobile First */
.status-card {
  margin-bottom: var(--gc-spacing-md);
}

.status-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--gc-spacing-md);
  gap: var(--gc-spacing-sm);
}

.user-info {
  display: flex;
  align-items: center;
  gap: var(--gc-spacing-sm);
  min-width: 0;
  flex: 1;
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
  font-size: var(--gc-font-size-lg);
  flex-shrink: 0;
}

.user-details {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.username {
  font-weight: 600;
  color: var(--gc-gray-800);
  font-size: var(--gc-font-size-sm);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.time {
  font-size: var(--gc-font-size-xs);
  color: var(--gc-gray-500);
}

.delete-btn {
  background: none;
  border: none;
  font-size: var(--gc-font-size-lg);
  cursor: pointer;
  opacity: 0.6;
  transition: opacity var(--gc-transition-fast);
  padding: var(--gc-spacing-xs);
}

.delete-btn:hover {
  opacity: 1;
}

.status-caption {
  margin-bottom: var(--gc-spacing-md);
  font-size: var(--gc-font-size-sm);
  color: var(--gc-gray-700);
  line-height: 1.5;
}

.status-media {
  margin-bottom: var(--gc-spacing-md);
  border-radius: var(--gc-radius-lg);
  overflow: hidden;
}

.media-img, .media-vid {
  width: 100%;
  max-height: 300px;
  object-fit: cover;
  display: block;
}

.status-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: var(--gc-spacing-sm);
  border-top: 1px solid var(--gc-gray-100);
}

.view-count {
  color: var(--gc-gray-500);
  font-size: var(--gc-font-size-xs);
}

.view-btn {
  padding: var(--gc-spacing-xs) var(--gc-spacing-md);
  font-size: var(--gc-font-size-xs);
}

/* Tablet and Up */
@media (min-width: 768px) {
  .status-card {
    margin-bottom: var(--gc-spacing-lg);
  }

  .user-info {
    gap: var(--gc-spacing-md);
  }

  .avatar {
    width: 48px;
    height: 48px;
  }

  .username {
    font-size: var(--gc-font-size-base);
  }

  .time {
    font-size: var(--gc-font-size-sm);
  }

  .status-caption {
    font-size: var(--gc-font-size-base);
  }

  .media-img, .media-vid {
    max-height: 400px;
  }

  .view-count {
    font-size: var(--gc-font-size-sm);
  }

  .view-btn {
    font-size: var(--gc-font-size-sm);
    padding: var(--gc-spacing-sm) var(--gc-spacing-md);
  }
}
</style>
