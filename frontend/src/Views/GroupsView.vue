<script setup>
import { ref, onMounted, computed } from 'vue'
import { useGroupeStore } from '@/stores/Groupe'
import { useAuthStore } from '@/stores/auth'
import { useRouter, useRoute } from 'vue-router'
import api from '@/api/axios'

const groupeStore = useGroupeStore()
const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()

const showCreateModal = ref(false)
const showDetailsModal = ref(false)
const selectedGroup = ref(null)
const friends = ref([])
const searchUser = ref('')
const searchGroup = ref('')
const activeGroupId = ref(route.params.id ? Number(route.params.id) : null)

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
  fetchFriends()
})

const fetchFriends = async () => {
  try {
    const response = await api.get('/friends')
    friends.value = response.data.friends
  } catch (err) {
    console.error("Erreur lors de la récupération des amis", err)
  }
}

const filteredFriends = computed(() => {
  return friends.value.filter(u => 
    u.username.toLowerCase().includes(searchUser.value.toLowerCase()) ||
    u.email.toLowerCase().includes(searchUser.value.toLowerCase())
  )
})

const filteredGroupes = computed(() => {
  if (!searchGroup.value) return groupeStore.groupes
  const q = searchGroup.value.toLowerCase()
  return groupeStore.groupes.filter(g => g.name.toLowerCase().includes(q))
})

const selectGroup = (groupe) => {
  activeGroupId.value = groupe.id
  router.push(`/groups/${groupe.id}`)
}

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

const getGradientColor = (id) => {
  const colors = [
    'linear-gradient(135deg, #ec4899 0%, #db2777 100%)',
    'linear-gradient(135deg, #f472b6 0%, #ec4899 100%)',
    'linear-gradient(135deg, #be185d 0%, #9f1239 100%)',
    'linear-gradient(135deg, #a855f7 0%, #7c3aed 100%)',
  ]
  return colors[id % colors.length]
}
</script>

<template>
  <div class="gc-groups">

    <!-- Notification Toast -->
    <Transition name="toast">
      <div v-if="notification.show" :class="['toast', notification.type]">
        {{ notification.message }}
      </div>
    </Transition>

    <!-- Panel gauche -->
    <aside class="gc-panel">
      <div class="gc-panel-header">
        <h1>Groupes</h1>
        <button class="gc-add-btn" @click="showCreateModal = true" title="Créer un groupe">
          <i class="ti ti-plus"></i>
        </button>
      </div>

      <div class="gc-search">
        <i class="ti ti-search"></i>
        <input v-model="searchGroup" placeholder="Rechercher..." />
      </div>

      <div class="gc-group-list">
        <div v-if="groupeStore.loading && !groupeStore.groupes.length" class="gc-loading">
          <div class="spinner"></div>
        </div>

        <div v-else-if="groupeStore.groupes?.length === 0" class="gc-empty">
          <i class="ti ti-users-group"></i>
          <p>Aucun groupe</p>
        </div>

        <div
          v-for="groupe in filteredGroupes"
          :key="groupe.id"
          class="gc-group-item"
          :class="{ active: activeGroupId === groupe.id }"
          @click="selectGroup(groupe)"
        >
          <div class="gc-group-av" :style="{ background: getGradientColor(groupe.id) }">
            {{ groupe.name.charAt(0).toUpperCase() }}
            <span v-if="isGroupAdmin(groupe)" class="crown">👑</span>
          </div>
          <div class="gc-group-info">
            <div class="gc-group-top">
              <span class="gc-group-name">{{ groupe.name }}</span>
              <span class="gc-group-count">👥 {{ groupe.members?.length || 0 }}</span>
            </div>
            <!-- <p class="gc-group-desc">{{ groupe.description || 'Pas de description' }}</p> -->
          </div>
        </div>
      </div>
    </aside>

    <!-- Panel droit -->
    <div class="gc-main">
      <RouterView v-if="activeGroupId" />
      <div v-else class="gc-welcome">
        <div class="gc-welcome-icon">
          <i class="ti ti-users-group"></i>
        </div>
        <h2>Vos Groupes</h2>
        <p>Sélectionnez un groupe pour commencer à chatter</p>
      </div>
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
            </div>0
            
            <div class="form-group">
              <label>Inviter des amis</label>
              <div class="search-container">
                <input v-model="searchUser" placeholder="Rechercher parmi vos amis..." class="gc-input search-input">
              </div>
              <div class="user-selection-list">
                <div v-if="filteredFriends.length === 0" class="empty-list-info">
                  Aucun ami trouvé.
                </div>
                <div v-for="user in filteredFriends" :key="user.id" 
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
             <input v-model="searchUser" placeholder="Rechercher un ami à ajouter..." class="gc-input">
             <TransitionGroup name="list" tag="div" class="mini-user-list" v-if="searchUser">
                <div v-for="user in filteredFriends.filter(u => !selectedGroup.members.find(m => m.id === u.id))" 
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
    </div><!-- fin gc-groups -->
</template>

<style scoped>
/* ─── Layout deux colonnes ────────────────── */
.gc-groups {
  display: grid;
  grid-template-columns: 300px 1fr;
  height: 100vh;
  overflow: hidden;
}

/* ─── Panel gauche ────────────────────────── */
.gc-panel {
  background: #fff;
  border-right: .5px solid #f0d6f5;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.gc-panel-header {
  padding: 20px 16px 12px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.gc-panel-header h1 {
  font-size: 20px;
  font-weight: 600;
  color: #1a1a2e;
}

.gc-add-btn {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  border: .5px solid #e5e7eb;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #9ca3af;
  transition: all .15s;
}
.gc-add-btn:hover { border-color: #a855f7; color: #a855f7; }
.gc-add-btn i { font-size: 16px; }

.gc-search {
  margin: 0 12px 12px;
  background: #fdf4ff;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
}
.gc-search i { font-size: 15px; color: #c4b5fd; }
.gc-search input {
  border: none; background: transparent;
  font-size: 13px; color: #1a1a2e; outline: none; flex: 1;
}
.gc-search input::placeholder { color: #c4b5fd; }

/* Liste groupes */
.gc-group-list {
  flex: 1;
  overflow-y: auto;
  padding: 0 6px 10px;
}
.gc-group-list::-webkit-scrollbar { width: 3px; }
.gc-group-list::-webkit-scrollbar-thumb { background: #f0d6f5; border-radius: 99px; }

.gc-group-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px;
  border-radius: 14px;
  cursor: pointer;
  transition: background .15s;
}
.gc-group-item:hover,
.gc-group-item.active { background: #fdf4ff; }

.gc-group-av {
  width: 46px; height: 46px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 14px; font-weight: 600; color: #fff;
  flex-shrink: 0; position: relative;
}

.crown {
  position: absolute;
  bottom: -3px; right: -3px;
  font-size: 10px;
}

.gc-group-info { flex: 1; min-width: 0; }

.gc-group-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2px;
}

.gc-group-name { font-size: 14px; font-weight: 500; color: #1a1a2e; }
.gc-group-count { font-size: 11px; color: #9ca3af; flex-shrink: 0; }

.gc-group-desc {
  font-size: 12px; color: #9ca3af;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

.gc-loading {
  display: flex; justify-content: center; padding: 30px;
}

.gc-empty {
  display: flex; flex-direction: column;
  align-items: center; gap: 8px;
  padding: 40px 20px; color: #c4b5fd;
}
.gc-empty i { font-size: 36px; }
.gc-empty p { font-size: 13px; color: #9ca3af; }

/* ─── Panel droit ─────────────────────────── */
.gc-main {
  background: #fdf0f8;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.gc-welcome {
  flex: 1;
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  gap: 12px;
}

.gc-welcome-icon {
  width: 72px; height: 72px;
  border-radius: 24px;
  background: linear-gradient(135deg, #f472b6, #a855f7);
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 8px;
}
.gc-welcome-icon i { font-size: 36px; color: #fff; }
.gc-welcome h2 { font-size: 20px; font-weight: 600; color: #1a1a2e; }
.gc-welcome p { font-size: 14px; color: #9ca3af; }

/* ─── Spinner ─────────────────────────────── */
.spinner {
  width: 32px; height: 32px;
  border: 3px solid #f0d6f5;
  border-top: 3px solid #ec4899;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ─── Toast ───────────────────────────────── */
.toast {
  position: fixed; top: 20px; right: 20px;
  padding: 12px 24px; border-radius: 12px;
  color: white; z-index: 2000;
  box-shadow: 0 10px 15px -3px rgba(0,0,0,.1);
  font-weight: 600;
}
.toast.success { background: #10b981; }
.toast.error { background: #ef4444; }

/* ─── Modals ──────────────────────────────── */
.modal-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(236, 72, 153, 0.1);
  backdrop-filter: blur(8px);
  display: flex; align-items: center; justify-content: center;
  z-index: 1000; padding: 16px;
}

.modal {
  width: 100%; max-width: 500px; max-height: 90vh;
  border-radius: 24px; overflow: hidden;
  display: flex; flex-direction: column;
  background: #fff;
  box-shadow: 0 20px 40px rgba(236,72,153,.15);
}

.modal-header {
  padding: 20px; display: flex;
  justify-content: space-between; align-items: center;
  border-bottom: 1px solid #f3f4f6;
}

.header-info { display: flex; align-items: center; gap: 12px; }
.header-avatar { width: 40px; height: 40px; border-radius: 10px; }

.modal-body { padding: 20px; overflow-y: auto; }

.modal-footer {
  padding: 16px 20px; display: flex;
  justify-content: flex-end; gap: 12px;
  background: #f9fafb; border-top: 1px solid #f3f4f6;
}

.form-group { margin-bottom: 16px; }
.form-group label {
  display: block; margin-bottom: 6px;
  font-weight: 600; color: #374151; font-size: 13px;
}

.btn-close {
  background: none; border: none;
  font-size: 1.5rem; color: #9ca3af; cursor: pointer;
}
.btn-close:hover { color: #ec4899; }

.user-selection-list {
  max-height: 250px; overflow-y: auto;
  border: 1px solid #f3f4f6; border-radius: 12px; background: #fff;
}

.user-select-item {
  padding: 8px 12px; display: flex;
  align-items: center; gap: 12px; cursor: pointer;
}
.user-select-item:hover { background: #f9fafb; }
.user-select-item.selected { background: #fdf4ff; }

.empty-list-info {
  padding: 16px; text-align: center;
  color: #9ca3af; font-size: 13px;
}

.user-avatar-mini {
  width: 32px; height: 32px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: white; font-weight: bold; font-size: 13px;
}

.user-info-mini { flex: 1; display: flex; flex-direction: column; }
.user-info-mini .username { font-weight: 600; font-size: 14px; }
.user-info-mini .email { font-size: 12px; color: #9ca3af; }
.check-icon { color: #ec4899; font-weight: bold; }

.members-section { margin-top: 20px; }
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }

.member-item {
  display: flex; justify-content: space-between; align-items: center;
  padding: 8px 0; border-bottom: 1px solid #f9fafb;
}

.member-info-container { display: flex; align-items: center; gap: 10px; }

.member-avatar-mini {
  width: 36px; height: 36px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: white; font-weight: bold;
}

.member-text { display: flex; flex-direction: column; }
.member-name { font-weight: 600; font-size: 14px; }
.member-role-tag { font-size: 11px; padding: 1px 6px; border-radius: 4px; width: fit-content; }
.member-role-tag.admin { background: #fdf4ff; color: #ec4899; }
.member-role-tag.member { background: #f3f4f6; color: #9ca3af; }

.action-btn {
  background: none; border: none; cursor: pointer;
  padding: 5px; border-radius: 6px;
}
.action-btn:hover { background: #f3f4f6; }
.action-btn.remove:hover { color: #dc3545; }

.add-member-search { margin-bottom: 12px; }
.mini-user-list { margin-top: 8px; }
.mini-user-item {
  display: flex; align-items: center; justify-content: space-between;
  padding: 6px 8px; border-radius: 8px; background: #f9fafb; margin-bottom: 4px;
}
.btn-add-mini {
  width: 24px; height: 24px; border-radius: 50%;
  border: none; background: #ec4899; color: white;
  cursor: pointer; font-size: 16px; line-height: 1;
}

.danger-zone { flex-direction: column; gap: 8px; }
.btn-delete { background: #fee2e2; color: #dc3545; width: 100%; border: 1px solid #fecaca; padding: 10px; border-radius: 10px; cursor: pointer; }
.btn-delete:hover { background: #fecaca; }
.btn-leave { background: #f3f4f6; color: #374151; width: 100%; border: none; padding: 10px; border-radius: 10px; cursor: pointer; }
.btn-leave:hover { background: #e5e7eb; }
.btn-full { width: 100%; margin-top: 12px; }

.confirm-modal { max-width: 350px; text-align: center; }
.confirm-icon { font-size: 3rem; margin-bottom: 12px; }
.confirm-overlay { z-index: 2100; }

.group-info-display { margin-bottom: 16px; }
.group-info-display h3 { font-size: 18px; font-weight: 600; color: #1a1a2e; }
.group-info-display p { color: #9ca3af; font-size: 14px; margin-top: 4px; }

/* Transitions */
.toast-enter-active, .toast-leave-active { transition: all 0.3s ease; }
.toast-enter-from, .toast-leave-to { transform: translateX(100px); opacity: 0; }

.modal-enter-active, .modal-leave-active { transition: all 0.3s ease; }
.modal-enter-from, .modal-leave-to { transform: scale(0.9); opacity: 0; }

.list-enter-active, .list-leave-active { transition: all 0.3s ease; }
.list-enter-from, .list-leave-to { opacity: 0; transform: translateY(-10px); }
</style>