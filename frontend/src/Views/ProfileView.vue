<template>
  <div class="profile-view">
    <div class="profile-card gc-card">
      <div class="profile-header">
        <div class="avatar" :style="{ background: getGradientColor(1) }">
          {{ userInitial }}
        </div>
        <div class="profile-info">
          <h1 class="username">{{ userData.first_name }} {{ userData.last_name }}</h1>
          <p class="username-handle">@{{ userData.username }}</p>
          <p class="bio">{{ userData.bio || 'Aucune bio' }}</p>
        </div>
      </div>

      <div class="profile-stats">
        <div class="stat">
          <span class="stat-number">{{ stats.posts }}</span>
          <span class="stat-label">Posts</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat">
          <span class="stat-number">{{ stats.statuses }}</span>
          <span class="stat-label">Statuts</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat">
          <span class="stat-number">{{ stats.friends }}</span>
          <span class="stat-label">Amis</span>
        </div>
      </div>

      <button @click="showEditModal = true" class="gc-btn gc-btn-primary edit-btn">
        <span class="btn-icon">✏️</span>
        Modifier le profil
      </button>
    </div>

    <!-- Modal -->
    <div v-if="showEditModal" class="modal-overlay" @click.self="showEditModal = false">
      <div class="modal-content gc-card">
        <div class="modal-header">
          <h2>Modifier le profil</h2>
          <button @click="showEditModal = false" class="close-btn">✕</button>
        </div>
        <div class="modal-body">
          <input
            v-model="editForm.first_name"
            placeholder="Prénom"
            class="gc-input"
          />
          <input
            v-model="editForm.last_name"
            placeholder="Nom"
            class="gc-input"
          />
          <input
            v-model="editForm.username"
            placeholder="Nom d'utilisateur"
            class="gc-input"
          />
          <textarea
            v-model="editForm.bio"
            placeholder="Bio"
            class="gc-input"
            rows="3"
          ></textarea>
        </div>
        <div class="modal-footer">
          <button @click="showEditModal = false" class="gc-btn gc-btn-secondary">
            Annuler
          </button>
          <button @click="saveProfile" class="gc-btn gc-btn-primary">
            Enregistrer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const showEditModal = ref(false)

const userData = ref({
  first_name: 'Jane',
  last_name: 'Doe',
  username: 'janedoe',
  bio: 'Passionnée de tech et de design 💖'
})

const stats = ref({
  posts: 12,
  statuses: 5,
  friends: 48
})

const editForm = ref({ ...userData.value })

const userInitial = computed(() => {
  return userData.value.first_name?.[0] || '?'
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

const saveProfile = () => {
  userData.value = { ...editForm.value }
  showEditModal.value = false
}
</script>

<style scoped>
/* Mobile First */
.profile-view {
  max-width: 100%;
}

.profile-card {
  padding: var(--gc-spacing-lg);
}

.profile-header {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: var(--gc-spacing-md);
  margin-bottom: var(--gc-spacing-xl);
}

.avatar {
  width: 100px;
  height: 100px;
  border-radius: var(--gc-radius-full);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 800;
  font-size: var(--gc-font-size-3xl);
  box-shadow: var(--gc-shadow-lg);
}

.profile-info {
  width: 100%;
}

.username {
  font-size: var(--gc-font-size-xl);
  font-weight: 700;
  color: var(--gc-gray-800);
  margin: 0 0 var(--gc-spacing-xs) 0;
}

.username-handle {
  font-size: var(--gc-font-size-sm);
  color: var(--gc-gray-500);
  margin: 0 0 var(--gc-spacing-sm) 0;
}

.bio {
  color: var(--gc-gray-600);
  font-size: var(--gc-font-size-sm);
  margin: 0;
}

.profile-stats {
  display: flex;
  justify-content: space-around;
  align-items: center;
  padding: var(--gc-spacing-lg) 0;
  margin-bottom: var(--gc-spacing-lg);
  border-top: 1px solid var(--gc-gray-100);
  border-bottom: 1px solid var(--gc-gray-100);
}

.stat {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: var(--gc-spacing-xs);
}

.stat-divider {
  width: 1px;
  height: 40px;
  background: var(--gc-gray-100);
}

.stat-number {
  font-size: var(--gc-font-size-xl);
  font-weight: 700;
  background: linear-gradient(135deg, var(--gc-primary) 0%, var(--gc-secondary) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  line-height: 1;
}

.stat-label {
  color: var(--gc-gray-500);
  font-size: var(--gc-font-size-xs);
  font-weight: 500;
}

.edit-btn {
  width: 100%;
  padding: var(--gc-spacing-md);
  font-size: var(--gc-font-size-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--gc-spacing-sm);
}

.edit-btn .btn-icon {
  line-height: 1;
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
  .profile-header {
    flex-direction: row;
    align-items: flex-start;
    text-align: left;
    gap: var(--gc-spacing-xl);
  }

  .avatar {
    width: 120px;
    height: 120px;
    font-size: var(--gc-font-size-4xl);
  }

  .username {
    font-size: var(--gc-font-size-2xl);
  }

  .bio {
    font-size: var(--gc-font-size-base);
  }

  .stat-number {
    font-size: var(--gc-font-size-2xl);
  }

  .stat-label {
    font-size: var(--gc-font-size-sm);
  }

  .edit-btn {
    font-size: var(--gc-font-size-base);
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
  .profile-view {
    max-width: 600px;
    margin: 0 auto;
  }

  .profile-card {
    padding: var(--gc-spacing-2xl);
  }
}
</style>
