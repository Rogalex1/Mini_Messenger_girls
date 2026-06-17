<template>
  <div class="post-card gc-card">
    <div class="post-header">
      <div class="user-info">
        <div class="avatar" :style="{ background: getGradientColor(post.user.id) }">
          {{ post.user.profile?.first_name?.[0] || post.user.username?.[0] }}
        </div>
        <div class="user-details">
          <span class="username">
            {{ post.user.profile?.first_name }} {{ post.user.profile?.last_name || post.user.username }}
          </span>
          <span class="time">{{ formatTime(post.created_at) }}</span>
        </div>
      </div>
      <div v-if="isOwner" class="post-actions-header">
        <button @click="handleEdit" class="edit-btn">✏️</button>
        <button @click="handleDelete" class="delete-btn">🗑️</button>
      </div>
    </div>

    <div v-if="post.content" class="post-content">
      {{ post.content }}
    </div>

    <div v-if="post.media_url" class="post-media">
      <img v-if="post.type === 'image'" :src="post.media_url" alt="Post media" class="media-img" loading="lazy" />
      <video v-else-if="post.type === 'video'" :src="post.media_url" controls class="media-vid"></video>
    </div>

    <div class="post-actions">
      <button @click="handleLike" class="action-btn" :class="{ active: isLiked }">
        ❤️ <span>{{ post.likes_count }}</span>
      </button>
      <button class="action-btn">
        💬 <span>{{ post.comments_count }}</span>
      </button>
      <button @click="handleShare" class="action-btn">
        📤 <span v-if="post.shares_count > 0">{{ post.shares_count }}</span>
      </button>
    </div>

    <div class="post-stats">
      <span class="stat-item">👁️ {{ post.views_count || 0 }} vues</span>
    </div>

    <div class="comments-section">
      <div v-for="comment in post.comments" :key="comment.id" class="comment">
        <span class="comment-author">{{ comment.user.profile?.first_name || comment.user.username }}:</span>
        <span class="comment-text">{{ comment.comment }}</span>
        <button v-if="isMyComment(comment)" @click="handleDeleteComment(comment.id)" class="delete-comment-btn">×</button>
      </div>
    </div>

    <div class="add-comment">
      <input
        v-model="newComment"
        @keyup.enter="handleAddComment"
        type="text"
        placeholder="Ajouter un commentaire..."
        class="gc-input"
      />
      <button @click="handleAddComment" class="gc-btn gc-btn-primary send-btn">
        Envoyer
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const props = defineProps({
  post: {
    type: Object,
    required: true
  },
  isOwner: {
    type: Boolean,
    default: false
  },
  currentUserId: {
    type: Number,
    default: null
  }
})

const emit = defineEmits(['delete', 'like', 'add-comment', 'delete-comment', 'edit', 'share'])

const newComment = ref('')
const showEditModal = ref(false)
const showShareModal = ref(false)
const editContent = ref('')
const editMediaUrl = ref('')

const isLiked = computed(() => {
  return props.post.likes?.some(l => l.user_id === props.currentUserId) || false
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

const isMyComment = (comment) => {
  return comment.user_id === props.currentUserId
}

const handleDelete = () => {
  emit('delete', props.post.id)
}

const handleEdit = () => {
  editContent.value = props.post.content || ''
  editMediaUrl.value = props.post.media_url || ''
  showEditModal.value = true
}

const handleSaveEdit = () => {
  const data = {
    content: editContent.value,
    media_url: editMediaUrl.value
  }
  emit('edit', { postId: props.post.id, data })
  showEditModal.value = false
}

const handleShare = () => {
  showShareModal.value = true
}

const copyLink = async () => {
  try {
    const url = window.location.href
    await navigator.clipboard.writeText(url)
    alert('Lien copié !')
    showShareModal.value = false
    emit('share', props.post.id)
  } catch (err) {
    console.error('Erreur lors de la copie:', err)
  }
}

const handleLike = () => {
  emit('like', props.post.id)
}

const handleAddComment = () => {
  if (newComment.value.trim()) {
    emit('add-comment', { postId: props.post.id, comment: newComment.value })
    newComment.value = ''
  }
}

const handleDeleteComment = (commentId) => {
  emit('delete-comment', { postId: props.post.id, commentId })
}

onMounted(() => {
  // Increment views when post is shown
  if (!props.isOwner) {
    emit('view', props.post.id)
  }
})

onMounted(() => {
  // Increment views when post is shown
  if (!props.isOwner) {
    emit('view', props.post.id)
  }
})
</script>

<style scoped>
/* Mobile First */
.post-card {
  margin-bottom: var(--gc-spacing-md);
}

.post-header {
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

.post-actions-header {
  display: flex;
  gap: var(--gc-spacing-xs);
}

.edit-btn,
.delete-btn {
  background: none;
  border: none;
  font-size: var(--gc-font-size-lg);
  cursor: pointer;
  opacity: 0.6;
  transition: opacity var(--gc-transition-fast);
  padding: var(--gc-spacing-xs);
}

.edit-btn:hover,
.delete-btn:hover {
  opacity: 1;
}

.post-stats {
  padding-top: var(--gc-spacing-sm);
  border-top: 1px solid var(--gc-gray-100);
  margin-top: var(--gc-spacing-sm);
}

.stat-item {
  font-size: var(--gc-font-size-xs);
  color: var(--gc-gray-500);
}

.post-content {
  margin-bottom: var(--gc-spacing-md);
  font-size: var(--gc-font-size-sm);
  color: var(--gc-gray-700);
  line-height: 1.6;
}

.post-media {
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

.post-actions {
  display: flex;
  gap: var(--gc-spacing-lg);
  padding: var(--gc-spacing-sm) 0;
  border-top: 1px solid var(--gc-gray-100);
  border-bottom: 1px solid var(--gc-gray-100);
}

.action-btn {
  display: flex;
  align-items: center;
  gap: var(--gc-spacing-xs);
  background: none;
  border: none;
  font-size: var(--gc-font-size-base);
  cursor: pointer;
  color: var(--gc-gray-600);
  transition: color var(--gc-transition-fast);
  padding: var(--gc-spacing-xs) 0;
}

.action-btn:hover, .action-btn.active {
  color: var(--gc-primary);
}

.comments-section {
  padding: var(--gc-spacing-sm) 0;
}

.comment {
  display: flex;
  align-items: flex-start;
  gap: var(--gc-spacing-xs);
  margin-bottom: var(--gc-spacing-sm);
  font-size: var(--gc-font-size-xs);
  flex-wrap: wrap;
}

.comment-author {
  font-weight: 600;
  color: var(--gc-gray-800);
  flex-shrink: 0;
}

.comment-text {
  color: var(--gc-gray-700);
  flex: 1;
  min-width: 0;
}

.delete-comment-btn {
  background: none;
  border: none;
  font-size: var(--gc-font-size-lg);
  cursor: pointer;
  color: var(--gc-gray-400);
  margin-left: auto;
  transition: color var(--gc-transition-fast);
  padding: 0 var(--gc-spacing-xs);
}

.delete-comment-btn:hover {
  color: var(--gc-primary);
}

.add-comment {
  display: flex;
  gap: var(--gc-spacing-sm);
  margin-top: var(--gc-spacing-md);
}

.add-comment input {
  flex: 1;
  min-width: 0;
}

.send-btn {
  padding: var(--gc-spacing-xs) var(--gc-spacing-md);
  font-size: var(--gc-font-size-xs);
  flex-shrink: 0;
}

/* Modal styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: var(--gc-spacing-md);
}

.modal-content {
  width: 100%;
  max-width: 500px;
  border-radius: var(--gc-radius-xl);
  padding: var(--gc-spacing-lg);
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--gc-spacing-lg);
}

.modal-header h2 {
  font-size: var(--gc-font-size-lg);
  color: var(--gc-gray-800);
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  font-size: var(--gc-font-size-xl);
  color: var(--gc-gray-500);
  cursor: pointer;
  padding: var(--gc-spacing-xs);
  line-height: 1;
}

.modal-body .gc-input {
  margin-bottom: var(--gc-spacing-sm);
}

.modal-footer {
  display: flex;
  gap: var(--gc-spacing-sm);
  justify-content: flex-end;
  margin-top: var(--gc-spacing-lg);
}

.share-options {
  display: flex;
  flex-direction: column;
  gap: var(--gc-spacing-sm);
}

.share-option {
  width: 100%;
}

/* Tablet and Up */
@media (min-width: 768px) {
  .post-card {
    margin-bottom: var(--gc-spacing-xl);
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

  .post-content {
    font-size: var(--gc-font-size-base);
  }

  .media-img, .media-vid {
    max-height: 500px;
  }

  .post-actions {
    padding: var(--gc-spacing-md) 0;
  }

  .comment {
    font-size: var(--gc-font-size-sm);
  }

  .send-btn {
    font-size: var(--gc-font-size-sm);
    padding: var(--gc-spacing-sm) var(--gc-spacing-md);
  }
}
</style>