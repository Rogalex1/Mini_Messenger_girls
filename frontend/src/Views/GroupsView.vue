<script setup>
import { ref, onMounted, computed } from 'vue'
import { useGroupeStore } from '@/stores/Groupe'
import { useAuthStore } from '@/stores/auth'
import api from '@/api/axios'

const groupeStore = useGroupeStore()
const authStore = useAuthStore()

const showCreateModal = ref(false)
const showDetailsModal = ref(false)
const selectedGroup = ref(null)
const allUsers = ref([])
const searchUser = ref('')

const newGroup = ref({
  name: '',
  description: '',
  member_ids: []
})

const editingGroup = ref({
  name: '',
  description: ''
})

onMounted(async () => {
  await groupeStore.fetchGroupes()
  fetchUsers()
})

const fetchUsers = async () => {
  try {
    const response = await api.get('/users')
    allUsers.value = response.data.data
  } catch (err) {
    console.error("Erreur lors de la récupération des utilisateurs", err)
  }
}

const filteredUsers = computed(() => {
  return allUsers.value.filter(u => 
    u.username.toLowerCase().includes(searchUser.value.toLowerCase()) ||
    u.email.toLowerCase().includes(searchUser.value.toLowerCase())
  )
})

const isGroupAdmin = (group) => {
  if (!group || !authStore.user) return false
  return group.members?.find(m => m.id === authStore.user.id)?.pivot?.role === 'admin'
}

const notification = ref({ show: false, message: '', type: 'success' })

const showNotification = (message, type = 'success') => {
  notification.value = { show: true, message, type }
  setTimeout(() => {
    notification.value.show = false
  }, 3000)
}

const handleCreateGroup = async () => {
  try {
    await groupeStore.addGroup(newGroup.value)
    showCreateModal.value = false
    newGroup.value = { name: '', description: '', member_ids: [] }
    showNotification("Groupe créé avec succès !")
  } catch (err) {
    showNotification("Erreur lors de la création du groupe", "error")
  }
}

const openDetails = (group) => {
  selectedGroup.value = group
  editingGroup.value = { name: group.name, description: group.description }
  showDetailsModal.value = true
}

const handleUpdateGroup = async () => {
  try {
    await groupeStore.updateGroup(selectedGroup.value.id, editingGroup.value)
    selectedGroup.value.name = editingGroup.value.name
    selectedGroup.value.description = editingGroup.value.description
    showNotification("Groupe mis à jour !")
  } catch (err) {
    showNotification("Erreur lors de la mise à jour", "error")
  }
}

const showConfirmModal = ref(false)
const confirmAction = ref(null)
const confirmMessage = ref('')

const triggerConfirm = (message, action) => {
  confirmMessage.value = message
  confirmAction.value = action
  showConfirmModal.value = true
}

const executeConfirm = async () => {
  if (confirmAction.value) {
    await confirmAction.value()
  }
  showConfirmModal.value = false
  confirmAction.value = null
}

const handleDeleteGroup = () => {
  triggerConfirm(
    "Êtes-vous sûr de vouloir supprimer ce groupe ? Cette action est irréversible.",
    async () => {
      const success = await groupeStore.destroyGroup(selectedGroup.value.id)
      if (success) {
        showDetailsModal.value = false
        selectedGroup.value = null
        showNotification("Groupe supprimé")
      }
    }
  )
}

const handleLeaveGroup = () => {
  triggerConfirm(
    "Voulez-vous vraiment quitter ce groupe ?",
    async () => {
      try {
        await groupeStore.leaveGroup(selectedGroup.value.id)
        showDetailsModal.value = false
        selectedGroup.value = null
        showNotification("Vous avez quitté le groupe")
      } catch (err) {
        showNotification(err.response?.data?.message || "Erreur lors de la sortie du groupe", "error")
      }
    }
  )
}

const handleAddMember = async (userId) => {
  try {
    const updatedGroup = await groupeStore.addMembers(selectedGroup.value.id, [userId])
    selectedGroup.value = updatedGroup
    showNotification("Membre ajouté !")
  } catch (err) {
    showNotification("Erreur lors de l'ajout du membre", "error")
  }
}

const handleRemoveMember = (userId) => {
  triggerConfirm(
    "Retirer ce membre du groupe ?",
    async () => {
      try {
        const updatedGroup = await groupeStore.removeMember(selectedGroup.value.id, userId)
        selectedGroup.value = updatedGroup
        showNotification("Membre retiré")
      } catch (err) {
        showNotification(err.response?.data?.message || "Erreur lors de la suppression", "error")
      }
    }
  )
}

const handlePromote = async (userId) => {
  try {
    const updatedGroup = await groupeStore.promoteToAdmin(selectedGroup.value.id, userId)
    selectedGroup.value = updatedGroup
    showNotification("Membre promu admin !")
  } catch (err) {
    showNotification("Erreur lors de la promotion", "error")
  }
}

const handleDemote = async (userId) => {
  try {
    const updatedGroup = await groupeStore.demoteFromAdmin(selectedGroup.value.id, userId)
    selectedGroup.value = updatedGroup
    showNotification("Admin rétrogradé")
  } catch (err) {
    showNotification(err.response?.data?.message || "Erreur lors de la rétrogradation", "error")
  }
}

const toggleMemberSelection = (userId) => {
  const index = newGroup.value.member_ids.indexOf(userId)
  if (index > -1) {
    newGroup.value.member_ids.splice(index, 1)
  } else {
    newGroup.value.member_ids.push(userId)
  }
}
</script>

<template>
  <div class="group-page">
    <!-- Notification Toast -->
    <Transition name="toast">
      <div v-if="notification.show" :class="['toast', notification.type]">
        {{ notification.message }}
      </div>
    </Transition>

    <div class="header">
      <h2 class="gc-text-primary">Mes Groupes</h2>
      <button @click="showCreateModal = true" class="gc-btn gc-btn-primary">
        <span class="icon">+</span> Créer un groupe
      </button>
    </div>

    <div v-if="groupeStore.loading && !groupeStore.groupes.length" class="loading-state">
      <div class="spinner"></div>
      <p>Chargement de vos groupes...</p>
    </div>
    
    <div v-else-if="groupeStore.error" class="error gc-glass">
      <p>{{ groupeStore.error }}</p>
    </div>

    <div v-else class="group-grid">
      <div v-for="groupe in groupeStore.groupes" :key="groupe.id" 
           class="group-card gc-glass" @click="openDetails(groupe)">
        <div class="group-avatar-container">
          <img :src="groupe.photo || `https://ui-avatars.com/api/?name=${groupe.name}&background=ec4899&color=fff`" 
               alt="Avatar" class="group-avatar">
          <div class="admin-badge" v-if="isGroupAdmin(groupe)">
            👑
          </div>
        </div>
        <div class="group-content">
          <h3>{{ groupe.name }}</h3>
          <p class="description">{{ groupe.description || 'Pas de description' }}</p>
          <div class="footer">
             <span class="member-count">👥 {{ groupe.members?.length || 0 }} membres</span>
          </div>
        </div>
      </div>
    </div>

    <div v-if="!groupeStore.loading && groupeStore.groupes?.length === 0" class="empty-state gc-glass">
      <div class="empty-icon">💬</div>
      <p>Vous n'avez rejoint aucun groupe pour le moment.</p>
      <button @click="showCreateModal = true" class="gc-btn gc-btn-secondary">Créer mon premier groupe</button>
    </div>

    <!-- Modal Création -->
    <Transition name="modal">
      <div v-if="showCreateModal" class="modal-overlay" @click.self="showCreateModal = false">
        <div class="modal gc-glass">
          <div class="modal-header">
            <h3>Nouveau Groupe</h3>
            <button @click="showCreateModal = false" class="btn-close">&times;</button>
          </div>
          
          <div class="modal-body">
            <div class="form-group">
              <label>Nom du groupe</label>
              <input v-model="newGroup.name" placeholder="Ex: Les Copines 💖" class="gc-input">
            </div>
            
            <div class="form-group">
              <label>Description</label>
              <textarea v-model="newGroup.description" placeholder="De quoi parle ce groupe ?" class="gc-input"></textarea>
            </div>
            
            <div class="form-group">
              <label>Inviter des membres</label>
              <div class="search-container">
                <input v-model="searchUser" placeholder="Rechercher par nom ou email..." class="gc-input search-input">
              </div>
              <div class="user-selection-list">
                <div v-for="user in filteredUsers" :key="user.id" 
                     @click="toggleMemberSelection(user.id)"
                     :class="['user-select-item', newGroup.member_ids.includes(user.id) ? 'selected' : '']">
                  <div class="user-avatar-mini" :style="{ background: `linear-gradient(135deg, #ec4899 0%, #db2777 100%)` }">
                    {{ user.username.charAt(0).toUpperCase() }}
                  </div>
                  <div class="user-info-mini">
                    <span class="username">{{ user.username }}</span>
                    <span class="email">{{ user.email }}</span>
                  </div>
                  <div class="check-icon" v-if="newGroup.member_ids.includes(user.id)">✓</div>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button @click="showCreateModal = false" class="gc-btn gc-btn-secondary">Annuler</button>
            <button @click="handleCreateGroup" :disabled="!newGroup.name" class="gc-btn gc-btn-primary">
              Créer le groupe
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Modal Détails & Gestion -->
    <Transition name="modal">
      <div v-if="showDetailsModal" class="modal-overlay" @click.self="showDetailsModal = false">
        <div class="modal details-modal gc-glass">
          <div class="modal-header">
            <div class="header-info">
              <img :src="selectedGroup.photo || `https://ui-avatars.com/api/?name=${selectedGroup.name}&background=ec4899&color=fff`" 
                   class="header-avatar">
              <h3>Détails du Groupe</h3>
            </div>
            <button @click="showDetailsModal = false" class="btn-close">&times;</button>
          </div>

          <div class="modal-body">
            <div v-if="isGroupAdmin(selectedGroup)" class="admin-section">
              <div class="form-group">
                <label>Nom du groupe</label>
                <input v-model="editingGroup.name" class="gc-input">
              </div>
              <div class="form-group">
                <label>Description</label>
                <textarea v-model="editingGroup.description" class="gc-input"></textarea>
              </div>
              <button @click="handleUpdateGroup" class="gc-btn gc-btn-primary btn-full">
                Mettre à jour les infos
              </button>
            </div>
            <div v-else class="group-info-display">
               <h3>{{ selectedGroup.name }}</h3>
               <p>{{ selectedGroup.description || 'Pas de description' }}</p>
            </div>

            <div class="members-section">
              <div class="section-header">
                <h4>Membres ({{ selectedGroup.members?.length }})</h4>
              </div>
              
              <div v-if="isGroupAdmin(selectedGroup)" class="add-member-search">
                 <input v-model="searchUser" placeholder="Ajouter un membre..." class="gc-input">
                 <TransitionGroup name="list" tag="div" class="mini-user-list" v-if="searchUser">
                    <div v-for="user in filteredUsers.filter(u => !selectedGroup.members.find(m => m.id === u.id))" 
                         :key="user.id" class="mini-user-item">
                       <div class="user-info">
                         <span class="name">{{ user.username }}</span>
                       </div>
                       <button @click="handleAddMember(user.id)" class="btn-add-mini">+</button>
                    </div>
                 </TransitionGroup>
              </div>

              <div class="members-list">
                <div v-for="member in selectedGroup.members" :key="member.id" class="member-item">
                  <div class="member-info-container">
                    <div class="member-avatar-mini" :style="{ background: member.pivot.role === 'admin' ? 'var(--gc-primary)' : 'var(--gc-gray-400)' }">
                      {{ member.username.charAt(0).toUpperCase() }}
                    </div>
                    <div class="member-text">
                      <span class="member-name">{{ member.username }}</span>
                      <span class="member-role-tag" :class="member.pivot.role">
                        {{ member.pivot.role === 'admin' ? 'Administrateur' : 'Membre' }}
                      </span>
                    </div>
                  </div>
                  
                  <div v-if="isGroupAdmin(selectedGroup) && member.id !== authStore.user.id" class="member-actions">
                    <button v-if="member.pivot.role === 'member'" @click="handlePromote(member.id)" 
                            class="action-btn promote" title="Promouvoir admin">👑</button>
                    <button v-if="member.pivot.role === 'admin' && member.id !== selectedGroup.created_by" 
                            @click="handleDemote(member.id)" class="action-btn demote" title="Rétrograder">👤</button>
                    <button @click="handleRemoveMember(member.id)" class="action-btn remove" title="Retirer">✕</button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer danger-zone">
            <button v-if="selectedGroup.created_by !== authStore.user.id" 
                    @click="handleLeaveGroup" class="gc-btn btn-leave">
              Quitter le groupe
            </button>
            <button v-if="isGroupAdmin(selectedGroup)" 
                    @click="handleDeleteGroup" class="gc-btn btn-delete">
              Supprimer le groupe
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Modal Confirmation -->
    <Transition name="modal">
      <div v-if="showConfirmModal" class="modal-overlay confirm-overlay" @click.self="showConfirmModal = false">
        <div class="modal confirm-modal gc-glass">
          <div class="modal-body">
            <div class="confirm-icon">⚠️</div>
            <h3>Confirmation</h3>
            <p>{{ confirmMessage }}</p>
          </div>
          <div class="modal-footer">
            <button @click="showConfirmModal = false" class="gc-btn gc-btn-secondary">Annuler</button>
            <button @click="executeConfirm" class="gc-btn gc-btn-primary">Confirmer</button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.group-page {
  padding: var(--gc-spacing-lg);
  max-width: 1000px;
  margin: 0 auto;
  min-height: 100vh;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--gc-spacing-xl);
}

.header h2 {
  font-size: var(--gc-font-size-2xl);
  font-weight: 800;
  background: linear-gradient(135deg, var(--gc-primary) 0%, var(--gc-secondary) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.group-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: var(--gc-spacing-lg);
}

.group-card {
  display: flex;
  padding: var(--gc-spacing-lg);
  border-radius: var(--gc-radius-2xl);
  cursor: pointer;
  transition: all var(--gc-transition-base);
  border: 1px solid var(--gc-glass-border);
}

.group-card:hover {
  transform: translateY(-5px);
  background: var(--gc-white);
  box-shadow: 0 20px 25px -5px rgba(236, 72, 153, 0.1);
}

.group-avatar-container {
  position: relative;
  margin-right: var(--gc-spacing-lg);
}

.group-avatar {
  width: 70px;
  height: 70px;
  border-radius: var(--gc-radius-2xl);
  object-fit: cover;
  border: 3px solid var(--gc-white);
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.admin-badge {
  position: absolute;
  bottom: -5px;
  right: -5px;
  background: var(--gc-white);
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 5px rgba(0,0,0,0.2);
  font-size: 0.9rem;
}

.group-content h3 {
  margin: 0;
  font-size: var(--gc-font-size-lg);
  color: var(--gc-gray-800);
}

.description {
  margin: var(--gc-spacing-xs) 0;
  color: var(--gc-gray-500);
  font-size: var(--gc-font-size-sm);
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.member-count {
  font-size: var(--gc-font-size-xs);
  color: var(--gc-primary);
  font-weight: 600;
  background: var(--gc-accent);
  padding: 2px 8px;
  border-radius: var(--gc-radius-full);
}

/* Modals */
.modal-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(236, 72, 153, 0.1);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: var(--gc-spacing-md);
}

.modal {
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  border-radius: var(--gc-radius-2xl);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.modal-header {
  padding: var(--gc-spacing-lg);
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid var(--gc-gray-100);
}

.header-info {
  display: flex;
  align-items: center;
  gap: var(--gc-spacing-md);
}

.header-avatar {
  width: 40px;
  height: 40px;
  border-radius: var(--gc-radius-md);
}

.modal-body {
  padding: var(--gc-spacing-lg);
  overflow-y: auto;
}

.modal-footer {
  padding: var(--gc-spacing-lg);
  display: flex;
  justify-content: flex-end;
  gap: var(--gc-spacing-md);
  background: var(--gc-gray-50);
  border-top: 1px solid var(--gc-gray-100);
}

.form-group {
  margin-bottom: var(--gc-spacing-lg);
}

.form-group label {
  display: block;
  margin-bottom: var(--gc-spacing-xs);
  font-weight: 600;
  color: var(--gc-gray-700);
  font-size: var(--gc-font-size-sm);
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: var(--gc-gray-400);
  cursor: pointer;
  transition: color var(--gc-transition-fast);
}

.btn-close:hover { color: var(--gc-primary); }

/* User Selection */
.user-selection-list {
  max-height: 250px;
  overflow-y: auto;
  border: 1px solid var(--gc-gray-100);
  border-radius: var(--gc-radius-lg);
  background: var(--gc-white);
}

.user-select-item {
  padding: var(--gc-spacing-sm) var(--gc-spacing-md);
  display: flex;
  align-items: center;
  gap: var(--gc-spacing-md);
  cursor: pointer;
  transition: background var(--gc-transition-fast);
}

.user-select-item:hover { background: var(--gc-gray-50); }
.user-select-item.selected { background: var(--gc-accent); }

.user-avatar-mini {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  font-size: 0.8rem;
}

.user-info-mini {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.user-info-mini .username { font-weight: 600; font-size: 0.9rem; }
.user-info-mini .email { font-size: 0.75rem; color: var(--gc-gray-500); }

.check-icon { color: var(--gc-primary); font-weight: bold; }

/* Member List */
.members-section {
  margin-top: var(--gc-spacing-xl);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--gc-spacing-md);
}

.member-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--gc-spacing-sm) 0;
  border-bottom: 1px solid var(--gc-gray-50);
}

.member-info-container {
  display: flex;
  align-items: center;
  gap: var(--gc-spacing-md);
}

.member-avatar-mini {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
}

.member-text {
  display: flex;
  flex-direction: column;
}

.member-name { font-weight: 600; font-size: 0.95rem; }
.member-role-tag {
  font-size: 0.7rem;
  padding: 1px 6px;
  border-radius: 4px;
  width: fit-content;
}
.member-role-tag.admin { background: var(--gc-accent); color: var(--gc-primary); }
.member-role-tag.member { background: var(--gc-gray-100); color: var(--gc-gray-500); }

.action-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 5px;
  border-radius: var(--gc-radius-sm);
  transition: background var(--gc-transition-fast);
}

.action-btn:hover { background: var(--gc-gray-100); }
.action-btn.remove:hover { color: #dc3545; }

/* Toast Notification */
.toast {
  position: fixed;
  top: 20px;
  right: 20px;
  padding: var(--gc-spacing-md) var(--gc-spacing-xl);
  border-radius: var(--gc-radius-lg);
  color: white;
  z-index: 2000;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  font-weight: 600;
}

.toast.success { background: #10b981; }
.toast.error { background: #ef4444; }

/* Danger Zone */
.danger-zone {
  flex-direction: column;
  gap: var(--gc-spacing-sm);
}

.btn-delete { background: #fee2e2; color: #dc3545; width: 100%; border: 1px solid #fecaca; }
.btn-delete:hover { background: #fecaca; }

.btn-leave { background: var(--gc-gray-100); color: var(--gc-gray-700); width: 100%; }
.btn-leave:hover { background: var(--gc-gray-200); }

/* Empty State */
.empty-state {
  text-align: center;
  padding: var(--gc-spacing-3xl);
  margin-top: var(--gc-spacing-2xl);
  border-radius: var(--gc-radius-2xl);
}

.empty-icon { font-size: 4rem; margin-bottom: var(--gc-spacing-md); }

/* Transitions */
.toast-enter-active, .toast-leave-active { transition: all 0.3s ease; }
.toast-enter-from { transform: translateX(100px); opacity: 0; }
.toast-leave-to { transform: translateX(100px); opacity: 0; }

.modal-enter-active, .modal-leave-active { transition: all 0.3s ease; }
.modal-enter-from { transform: scale(0.9); opacity: 0; }
.modal-leave-to { transform: scale(0.9); opacity: 0; }

.list-enter-active, .list-leave-active { transition: all 0.3s ease; }
.list-enter-from, .list-leave-to { opacity: 0; transform: translateY(-10px); }

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: var(--gc-spacing-3xl);
  color: var(--gc-primary);
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid var(--gc-accent);
  border-top: 4px solid var(--gc-primary);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: var(--gc-spacing-md);
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.btn-full { width: 100%; margin-top: var(--gc-spacing-md); }

.confirm-modal {
  max-width: 350px;
  text-align: center;
}

.confirm-icon {
  font-size: 3rem;
  margin-bottom: var(--gc-spacing-md);
}

.confirm-overlay {
  z-index: 2100;
}
</style>