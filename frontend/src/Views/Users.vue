<template>
  <div class="gc-users-page">
    <div class="messenger-sidebar">
      <div class="sidebar-header">
        <h3>Membres GlowChat</h3>
        <span class="user-count">{{ users.length }} membre(s)</span>
      </div>

    <div v-if="loading" class="sidebar-loading">
      <i class="ti ti-loader-2 spin"></i>
      <p>Chargement des membres...</p>
    </div>

    <div v-else-if="errorMsg" class="error-state">
      <i class="ti ti-alert-circle"></i>
      <p>{{ errorMsg }}</p>
      <button @click="fetchUsers" class="btn-retry">Réessayer</button>
    </div>

    <ul v-else class="user-list">
      <li v-for="user in users" :key="user.id" class="user-item">
        
        <div class="avatar-wrapper">
          <div class="avatar-fallback">
            {{ user.username.substring(0, 2).toUpperCase() }}
          </div>
          <span :class="user.is_online ? 'status-online' : 'status-offline'"></span>
        </div>

        <div class="user-info">
          <h4 class="user-name">@{{ user.username }}</h4>
          <p class="user-sub">
            <span :class="{ 'text-online': user.is_online }">
              {{ user.is_online ? 'En ligne' : 'Hors ligne' }}
            </span>
          </p>
        </div>

        <button 
          @click="addUserToChat(user.id)" 
          class="btn-add-user"
          :disabled="addingId === user.id"
        >
          <i v-if="addingId === user.id" class="ti ti-loader-2 spin"></i>
          <i v-else class="ti ti-plus"></i>
          <span>Ajouter</span>
        </button>

      </li>

      <div v-if="users.length === 0" class="empty-state">
        <i class="ti ti-users-minus"></i>
        <p>Aucun autre utilisateur trouvé.</p>
      </div>
    </ul>
  </div>
</div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/api/axios'; 
import { useChatStore } from '@/stores/chat';

const router = useRouter();
const chatStore = useChatStore();
const users = ref([]);
const loading = ref(true);
const addingId = ref(null);
const errorMsg = ref('');

// Récupération des utilisateurs grâce à ton instance "api"
const fetchUsers = async () => {
  try {
    loading.value = true;
    errorMsg.value = '';
    const response = await api.get('/all-users'); 

    if (response.data && response.data.success) {
      users.value = response.data.users;
    } else {
      errorMsg.value = "Impossible de charger les membres.";
    }
  } catch (error) {
    console.error("Erreur lors du chargement des utilisateurs :", error);
    errorMsg.value = "Erreur de connexion au serveur.";
  } finally {
    loading.value = false;
  }
};

// Action du bouton ajouter : on crée une conversation
const addUserToChat = async (userId) => {
  addingId.value = userId;
  try {
    // On utilise l'endpoint des conversations pour créer la discussion
    const response = await api.post('/conversations', { receiver_id: userId });
    
    if (response.data.conversation) {
      // Retirer immédiatement l'utilisateur de la liste locale pour qu'il ne soit plus affiché
      users.value = users.value.filter(u => u.id !== userId);
      
      // On rafraîchit la liste des conversations dans le store
      await chatStore.fetchConversations();
      
      // On redirige vers la nouvelle conversation
      router.push(`/messages/${response.data.conversation.id}`);
    }
  } catch (error) {
    console.error("Impossible d'ajouter l'utilisateur :", error);
    alert("Erreur lors de la création de la conversation.");
  } finally {
    addingId.value = null;
  }
};

onMounted(() => {
  fetchUsers();
});
</script>

<style scoped>
.gc-users-page {
  padding: 40px 20px;
  display: flex;
  justify-content: center;
  background: #fdf2f8;
  height: 100%;
  overflow-y: auto;
}

.messenger-sidebar {
  background: #ffffff;
  border-radius: 24px;
  padding: 24px;
  width: 100%;
  /* max-width: 380px; */
  box-shadow: 0 10px 40px rgba(154, 131, 163, 0.06);
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.sidebar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
  padding-bottom: 12px;
  border-bottom: 1px solid #fdf4f8;
}

.sidebar-header h3 {
  font-size: 16px;
  font-weight: 700;
  color: #2a2f45;
  margin: 0;
}

.user-count {
  font-size: 12px;
  font-weight: 600;
  color: #b266f8;
  background: #f4f3ff;
  padding: 4px 10px;
  border-radius: 20px;
}

.user-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.user-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px;
  border-radius: 14px;
  transition: background-color 0.2s ease;
}

.user-item:hover {
  background-color: #fffafd;
}

.avatar-wrapper {
  position: relative;
  width: 44px;
  height: 44px;
  flex-shrink: 0;
}

.avatar-fallback {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #fbe3f2 0%, #f4f3ff 100%);
  border: 1px solid #fbe3f2;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 700;
  color: #b266f8;
}

/* --- Badges de statut --- */
.status-online, .status-offline {
  position: absolute;
  bottom: -2px;
  right: -2px;
  width: 12px;
  height: 12px;
  border: 2px solid #ffffff;
  border-radius: 50%;
}
.status-online { background-color: #32d74b; }
.status-offline { background-color: #cbd5e1; } /* Gris quand déconnecté */

.user-info {
  flex: 1;
  min-width: 0;
}

.user-name {
  font-size: 14px;
  font-weight: 600;
  color: #3c4257;
  margin: 0 0 2px 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-sub {
  font-size: 12px;
  color: #94a3b8;
  margin: 0;
}
.text-online {
  color: #22c55e;
  font-weight: 500;
}

/* Bouton Ajouter */
.btn-add-user {
  display: flex;
  align-items: center;
  gap: 6px;
  background: #ffffff;
  border: 1px solid #fbe3f2;
  color: #e251b2;
  padding: 6px 12px;
  border-radius: 10px;
  font-size: 12.5px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-add-user:hover {
  background: linear-gradient(to right, #ec5bb0, #b266f8);
  color: #ffffff;
  border-color: transparent;
  box-shadow: 0 4px 12px rgba(236, 91, 176, 0.15);
}

.btn-add-user:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.sidebar-loading, .empty-state, .error-state {
  text-align: center;
  padding: 30px 10px;
  color: #9098b1;
}
.sidebar-loading i, .empty-state i, .error-state i {
  font-size: 24px;
  color: #b266f8;
  margin-bottom: 8px;
}
.error-state i { color: #ef4444; }

.btn-retry {
  margin-top: 12px;
  background: #fdf2f8;
  border: 1px solid #fbe3f2;
  color: #e251b2;
  padding: 6px 16px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
}
.sidebar-loading p, .empty-state p {
  font-size: 13px;
  margin: 0;
}
.spin {
  animation: spin 1s linear infinite;
  display: inline-block;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>