<template>
  <div class="posts-view">
    <div class="page-header">
      <h1 class="page-title">📝 Publications</h1>
      <button @click="showAddModal = true" class="gc-btn gc-btn-primary add-btn">
        <span class="btn-icon">+</span>
        <span class="btn-text">Publier</span>
      </button>
    </div>

    <div v-if="loading" class="loading">
      Chargement...
    </div>

    <div v-else-if="posts.length === 0" class="empty-state">
      <div class="empty-icon">📝</div>
      <p>Aucune publication pour le moment</p>
    </div>

    <div v-else class="posts-list">
      <PostCard
        v-for="post in posts"
        :key="post.id"
        :post="post"
        :is-owner="post.user_id === currentUserId"
        :current-user-id="currentUserId"
        @delete="deletePost"
        @like="toggleLike"
        @add-comment="addComment"
        @delete-comment="deleteComment"
         @edit="editPost"
        @share="sharePost"
      />
    </div>

    <!-- Modal -->
    <div v-if="showAddModal" class="modal-overlay" @click.self="showAddModal = false">
      <div class="modal-content gc-card">
        <div class="modal-header">
          <h2>Nouvelle publication</h2>
          <button @click="showAddModal = false" class="close-btn">✕</button>
        </div>
        <div class="modal-body">
          <textarea
            v-model="newPost.content"
            placeholder="Qu'est-ce qui se passe ?"
            class="gc-input"
            rows="4"
          ></textarea>
          <input
            v-model="newPost.media_url"
            placeholder="URL du média (optionnel)"
            class="gc-input"
          />
          <select v-model="newPost.type" class="gc-input">
            <option value="image">🖼️ Image</option>
            <option value="video">🎬 Vidéo</option>
          </select>
        </div>
        <div class="modal-footer">
          <button @click="showAddModal = false" class="gc-btn gc-btn-secondary">
            Annuler
          </button>
          <button @click="addPost" class="gc-btn gc-btn-primary">
            Publier
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { usePostStore } from '@/stores/post'
import { useAuthStore } from '@/stores/auth'
import PostCard from '@/components/PostCard.vue'

const postStore = usePostStore()
const authStore = useAuthStore()
const loading = ref(true)
const showAddModal = ref(false)

const newPost = ref({
  content: '',
  media_url: '',
  type: 'image'
})

const posts = computed(() => postStore.posts)
const currentUserId = computed(() => authStore.user?.id || null)

onMounted(async () => {
   await Promise.all([
    postStore.fetchPosts(),
    authStore.fetchMe()
  ])
  loading.value = false
})

const addPost = async () => {
  await postStore.createPost(newPost.value)
  showAddModal.value = false
  newPost.value = { content: '', media_url: '', type: 'image' }
}

const deletePost = async (id) => {
  await postStore.deletePost(id)
}

const toggleLike = async (id) => {
  await postStore.toggleLike(id)
}

const addComment = async ({ postId, comment }) => {
  await postStore.addComment(postId, comment)
}

const deleteComment = async ({ postId, commentId }) => {
  await postStore.deleteComment(postId, commentId)
}
const editPost = async ({ postId, data }) => {
  await postStore.updatePost(postId, data)
}

const sharePost = async (postId) => {
  await postStore.sharePost(postId)
}
// const viewPost = async (postId) => {
//   await postStore.incrementViews(postId)
// }
</script>

<style scoped>
/* Mobile First */
.posts-view {
  max-width: 100%;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--gc-spacing-lg);
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

.add-btn {
  padding: var(--gc-spacing-sm) var(--gc-spacing-md);
  font-size: var(--gc-font-size-sm);
  display: flex;
  align-items: center;
  gap: var(--gc-spacing-xs);
}

.add-btn .btn-icon {
  font-size: var(--gc-font-size-lg);
  line-height: 1;
}

.add-btn .btn-text {
  display: none;
}

.loading, .empty-state {
  text-align: center;
  padding: var(--gc-spacing-4xl) var(--gc-spacing-md);
  color: var(--gc-gray-500);
  font-size: var(--gc-font-size-sm);
}

.empty-icon {
  font-size: var(--gc-font-size-4xl);
  margin-bottom: var(--gc-spacing-md);
}

.posts-list {
  display: flex;
  flex-direction: column;
  gap: var(--gc-spacing-md);
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: flex-end;
  justify-content: center;
  z-index: 1000;
  padding: 0;
}

.modal-content {
  width: 100%;
  max-width: 100%;
  border-radius: var(--gc-radius-xl) var(--gc-radius-xl) 0 0;
  padding: var(--gc-spacing-lg);
  animation: slideUp 0.3s ease;
}

@keyframes slideUp {
  from {
    transform: translateY(100%);
  }
  to {
    transform: translateY(0);
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--gc-spacing-lg);
  padding-bottom: var(--gc-spacing-md);
  border-bottom: 1px solid var(--gc-gray-100);
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

.modal-footer .gc-btn {
  flex: 1;
}

/* Tablet and Up */
@media (min-width: 768px) {
  .page-title {
    font-size: var(--gc-font-size-2xl);
  }

  .add-btn .btn-text {
    display: inline;
  }

  .add-btn {
    padding: var(--gc-spacing-md) var(--gc-spacing-lg);
    font-size: var(--gc-font-size-base);
  }

  .loading, .empty-state {
    font-size: var(--gc-font-size-base);
  }

  .posts-list {
    gap: var(--gc-spacing-lg);
  }

  .modal-overlay {
    align-items: center;
    padding: var(--gc-spacing-xl);
  }

  .modal-content {
    width: 90%;
    max-width: 500px;
    border-radius: var(--gc-radius-xl);
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

  .modal-header h2 {
    font-size: var(--gc-font-size-xl);
  }

  .modal-footer .gc-btn {
    flex: none;
  }
}

/* Desktop */
@media (min-width: 1024px) {
  .posts-view {
    max-width: 700px;
    margin: 0 auto;
  }
}
</style>
