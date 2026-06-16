<template>
  <div class="status-view">
    <div class="page-header">
      <h1 class="page-title">📱 Statuts</h1>
      <button @click="showAddModal = true" class="gc-btn gc-btn-primary add-btn">
        <span class="btn-icon">+</span>
        <span class="btn-text">Ajouter</span>
      </button>
    </div>

    <div v-if="loading" class="loading">
      Chargement...
    </div>

    <div v-else class="content">
      <!-- Mes statuts -->
      <div v-if="myStatuses.length > 0" class="section">
        <h2 class="section-title">Mes statuts</h2>
        <div class="my-statuses">
          <div v-for="status in myStatuses" :key="status.id" class="my-status-item gc-card">
            <div v-if="status.caption" class="my-status-caption">{{ status.caption }}</div>
            <div v-if="status.media_url" class="my-status-media">
              <img v-if="status.type === 'image'" :src="status.media_url" alt="My status" class="media-thumb" />
              <video v-else-if="status.type === 'video'" :src="status.media_url" class="media-thumb"></video>
            </div>
            <button @click="deleteStatus(status.id)" class="delete-status-btn">🗑️</button>
          </div>
        </div>
      </div>

      <!-- Statuts récents (non vus) -->
      <div v-if="recentStatuses.length > 0" class="section">
        <h2 class="section-title">Récents</h2>
        <div class="users-grid">
          <div 
            v-for="user in recentStatuses" 
            :key="user.id" 
            @click="viewUserStatuses(user.id)"
            class="user-item gc-card"
          >
            <div class="user-avatar-wrapper">
              <div class="user-avatar" :style="{ background: getGradientColor(user.id) }">
                {{ user.profile?.first_name?.[0] || user.username?.[0] }}
              </div>
              <div class="status-count-badge">{{ user.statuses.length }}</div>
            </div>
            <span class="user-name">
              {{ user.profile?.first_name }} {{ user.profile?.last_name || user.username }}
            </span>
          </div>
        </div>
      </div>

      <!-- Statuts vus -->
      <div v-if="viewedStatuses.length > 0" class="section">
        <h2 class="section-title">Vus</h2>
        <div class="users-grid">
          <div 
            v-for="user in viewedStatuses" 
            :key="user.id" 
            @click="viewUserStatuses(user.id)"
            class="user-item gc-card viewed"
          >
            <div class="user-avatar-wrapper">
              <div class="user-avatar" :style="{ background: getGradientColor(user.id) }">
                {{ user.profile?.first_name?.[0] || user.username?.[0] }}
              </div>
              <div class="status-count-badge">{{ user.statuses.length }}</div>
            </div>
            <span class="user-name">
              {{ user.profile?.first_name }} {{ user.profile?.last_name || user.username }}
            </span>
          </div>
        </div>
      </div>

      <!-- État vide -->
      <div v-if="usersWithStatuses.length === 0 && myStatuses.length === 0" class="empty-state">
        <div class="empty-icon">📱</div>
        <p>Aucun statut pour le moment</p>
      </div>
    </div>

    <!-- Modal d'ajout de statut -->
    <div v-if="showAddModal" class="modal-overlay" @click.self="showAddModal = false">
      <div class="modal-content gc-card">
        <div class="modal-header">
          <h2>Nouveau statut</h2>
          <button @click="showAddModal = false" class="close-btn">✕</button>
        </div>
        <div class="modal-body">
          <textarea
            v-model="newStatus.caption"
            placeholder="Qu'est-ce qui se passe ?"
            class="gc-input"
            rows="3"
          ></textarea>
          <input
            v-model="newStatus.media_url"
            placeholder="URL du média (optionnel)"
            class="gc-input"
          />
          <select v-model="newStatus.type" class="gc-input">
            <option value="text">✍️ Texte</option>
            <option value="image">🖼️ Image</option>
            <option value="video">🎬 Vidéo</option>
          </select>
        </div>
        <div class="modal-footer">
          <button @click="showAddModal = false" class="gc-btn gc-btn-secondary">
            Annuler
          </button>
          <button @click="addStatus" class="gc-btn gc-btn-primary">
            Publier
          </button>
        </div>
      </div>
    </div>

    <!-- Viewer des statuts d'un utilisateur -->
    <UserStatusViewer 
      v-if="selectedUser" 
      :user="selectedUser"
      @close="clearSelectedUser"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useStatusStore } from '@/stores/status'
import { useAuthStore } from '@/stores/auth'
import UserStatusViewer from '@/components/UserStatusViewer.vue'

const statusStore = useStatusStore()
const authStore = useAuthStore()

const loading = ref(true)
const showAddModal = ref(false)
const newStatus = ref({
  caption: '',
  media_url: '',
  type: 'text'
})

const usersWithStatuses = computed(() => statusStore.usersWithStatuses)
const myStatuses = computed(() => statusStore.myStatuses)
const selectedUser = computed(() => statusStore.selectedUser)
const currentUserId = computed(() => authStore.user?.id || null)

// Séparer les statuts non vus et vus
const recentStatuses = computed(() => 
  usersWithStatuses.value.filter(u => u.has_unviewed)
)
const viewedStatuses = computed(() => 
  usersWithStatuses.value.filter(u => !u.has_unviewed)
)

const getGradientColor = (id) => {
  const colors = [
    'linear-gradient(135deg, #ec4899 0%, #db2777 100%)',
    'linear-gradient(135deg, #f472b6 0%, #ec4899 100%)',
    'linear-gradient(135deg, #be185d 0%, #9f1239 100%)',
    'linear-gradient(135deg, #fdf2f8 0%, #fbcfe8 100%)',
  ]
  return colors[id % colors.length]
}

onMounted(async () => {
  await Promise.all([
    statusStore.fetchStatuses(),
    statusStore.fetchMyStatuses(),
    authStore.fetchMe()
  ])
  loading.value = false
})

const addStatus = async () => {
  await statusStore.createStatus(newStatus.value)
  showAddModal.value = false
  newStatus.value = { caption: '', media_url: '', type: 'text' }
}

const deleteStatus = async (id) => {
  await statusStore.deleteStatus(id)
}

const viewUserStatuses = async (userId) => {
  await statusStore.fetchUserStatuses(userId)
}

const clearSelectedUser = () => {
  statusStore.clearSelectedUser()
}
</script>

<style scoped>
/* Mobile First */
.status-view {
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

.section {
  margin-bottom: var(--gc-spacing-xl);
}

.section-title {
  font-size: var(--gc-font-size-sm);
  font-weight: 700;
  color: var(--gc-gray-600);
  margin-bottom: var(--gc-spacing-md);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Mes statuts */
.my-statuses {
  display: flex;
  gap: var(--gc-spacing-sm);
  overflow-x: auto;
  padding-bottom: var(--gc-spacing-xs);
}

.my-status-item {
  min-width: 120px;
  padding: var(--gc-spacing-sm);
  position: relative;
}

.my-status-caption {
  font-size: var(--gc-font-size-xs);
  color: var(--gc-gray-700);
  margin-bottom: var(--gc-spacing-xs);
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.my-status-media {
  width: 100%;
  aspect-ratio: 1;
  border-radius: var(--gc-radius-lg);
  overflow: hidden;
}

.media-thumb {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.delete-status-btn {
  position: absolute;
  top: var(--gc-spacing-xs);
  right: var(--gc-spacing-xs);
  background: rgba(0,0,0,0.5);
  border: none;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  font-size: 12px;
  cursor: pointer;
  color: white;
}

/* Grille des utilisateurs */
.users-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
  gap: var(--gc-spacing-md);
}

.user-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: var(--gc-spacing-xs);
  padding: var(--gc-spacing-md);
  cursor: pointer;
  transition: transform var(--gc-transition-fast);
}

.user-item:hover {
  transform: translateY(-2px);
}

.user-item.viewed {
  opacity: 0.7;
}

.user-avatar-wrapper {
  position: relative;
}

.user-avatar {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: var(--gc-font-size-xl);
  border: 3px solid var(--gc-primary);
}

.user-item.viewed .user-avatar {
  border-color: var(--gc-gray-300);
}

.status-count-badge {
  position: absolute;
  bottom: 0;
  right: 0;
  background: var(--gc-primary);
  color: white;
  font-size: 10px;
  font-weight: 700;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid white;
}

.user-name {
  font-size: var(--gc-font-size-xs);
  color: var(--gc-gray-700);
  text-align: center;
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
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

  .users-grid {
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  }

  .user-avatar {
    width: 80px;
    height: 80px;
    font-size: 28px;
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
  .status-view {
    max-width: 900px;
    margin: 0 auto;
  }
}
</style>
