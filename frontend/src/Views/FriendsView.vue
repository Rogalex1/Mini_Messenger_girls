<template>
  <div class="friends-view">
    <div class="tabs">
      <button :class="['tab', activeTab === 'friends' ? 'active' : '']" @click="activeTab = 'friends'">
        Amis ({{ friends.length }})
      </button>
      <button :class="['tab', activeTab === 'requests' ? 'active' : '']" @click="activeTab = 'requests'">
        Demandes ({{ requests.length }})
      </button>
    </div>

    <!-- Friends List -->
    <div v-if="activeTab === 'friends'" class="friends-list">
      <div v-if="loading" class="sidebar-loading">
        <i class="ti ti-loader-2 spin"></i>
        <p>Chargement des amis...</p>
      </div>
      <div v-else-if="friends.length === 0" class="empty-state">
        <i class="ti ti-users-minus"></i>
        <p>Vous n'avez pas encore d'amis.</p>
      </div>
      <div v-for="friend in friends" :key="friend.id" class="friend-item gc-card">
        <div class="avatar" :style="{ background: getGradientColor(friend.id) }">
          {{ friend.profile?.first_name?.[0] || friend.username.charAt(0).toUpperCase() }}
        </div>
        <div class="friend-info">
          <span class="name">{{ friend.profile?.first_name }} {{ friend.profile?.last_name || '@'+friend.username }}</span>
          <span class="status" :class="{ online: friend.is_online }">{{ friend.is_online ? 'En ligne' : 'Hors ligne' }}</span>
        </div>
        <button @click="openChat(friend.id)" class="gc-btn gc-btn-secondary" style="padding: var(--gc-spacing-xs) var(--gc-spacing-md); font-size: var(--gc-font-size-sm);">💬</button>
      </div>
    </div>

    <!-- Requests List -->
    <div v-if="activeTab === 'requests'" class="requests-list">
      <div v-if="loadingRequests" class="sidebar-loading">
        <i class="ti ti-loader-2 spin"></i>
        <p>Chargement des demandes...</p>
      </div>
      <div v-else-if="requests.length === 0" class="empty-state">
        <i class="ti ti-user-plus"></i>
        <p>Aucune demande en attente.</p>
      </div>
      <div v-for="req in requests" :key="req.id" class="request-item gc-card">
        <div class="avatar" :style="{ background: getGradientColor(req.user.id) }">
          {{ req.user.username.charAt(0).toUpperCase() }}
        </div>
        <div class="request-info">
          <span class="name">@{{ req.user.username }}</span>
          <span class="mutual">Souhaite discuter avec vous</span>
        </div>
        <div class="request-actions">
          <button @click="handleRequest(req.id, 'rejected')" class="gc-btn gc-btn-secondary" style="padding: var(--gc-spacing-xs) var(--gc-spacing-md);">✖</button>
          <button @click="handleRequest(req.id, 'accepted')" class="gc-btn gc-btn-primary" style="padding: var(--gc-spacing-xs) var(--gc-spacing-md);">✓</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api/axios'
import { useChatStore } from '@/stores/chat'

const router = useRouter()
const chatStore = useChatStore()
const activeTab = ref('friends')
const friends = ref([])
const requests = ref([])
const loading = ref(true)
const loadingRequests = ref(true)

const fetchFriends = async () => {
  try {
    loading.value = true
    const res = await api.get('/friends')
    if (res.data.success) {
      friends.value = res.data.friends
    }
  } catch (e) {
    console.error("Erreur amis", e)
  } finally {
    loading.value = false
  }
}

const fetchRequests = async () => {
  try {
    loadingRequests.value = true
    const res = await api.get('/friend-requests')
    if (res.data.success) {
      requests.value = res.data.requests
    }
  } catch (e) {
    console.error("Erreur demandes", e)
  } finally {
    loadingRequests.value = false
  }
}

const handleRequest = async (id, action) => {
  try {
    await api.post(`/friend-requests/${id}`, { action })
    await fetchRequests()
    if (action === 'accepted') {
      await fetchFriends()
      await chatStore.fetchConversations()
    }
  } catch (e) {
    console.error("Erreur action demande", e)
  }
}

const openChat = async (userId) => {
  try {
    const res = await api.post('/conversations', { receiver_id: userId })
    if (res.data.conversation) {
      router.push(`/messages/${res.data.conversation.id}`)
    }
  } catch (e) {
    console.error("Erreur ouverture chat", e)
  }
}

const getGradientColor = (id) => {
  const colors = [
    'linear-gradient(135deg, #ec4899 0%, #db2777 100%)',
    'linear-gradient(135deg, #f472b6 0%, #ec4899 100%)',
    'linear-gradient(135deg, #be185d 0%, #9f1239 100%)',
    'linear-gradient(135deg, #fdf2f8 0%, #fbcfe8 100%)',
  ]
  return colors[id % colors.length]
}

onMounted(() => {
  fetchFriends()
  fetchRequests()
})
</script>

<style scoped>
/* Mobile First */
.friends-view {
  max-width: 100%;
  padding: var(--gc-spacing-md);
}

.sidebar-loading, .empty-state {
  text-align: center;
  padding: 40px 20px;
  color: var(--gc-gray-400);
}

.sidebar-loading i, .empty-state i {
  font-size: 32px;
  margin-bottom: 12px;
  display: block;
  color: var(--gc-primary);
}

.spin {
  animation: spin 1s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

.tabs {
  display: flex;
  gap: var(--gc-spacing-sm);
  margin-bottom: var(--gc-spacing-lg);
  background: var(--gc-gray-100);
  padding: 4px;
  border-radius: var(--gc-radius-lg);
}

.tab {
  flex: 1;
  padding: var(--gc-spacing-sm);
  border: none;
  border-radius: var(--gc-radius-md);
  font-weight: 600;
  cursor: pointer;
  background: transparent;
  color: var(--gc-gray-600);
  font-size: var(--gc-font-size-sm);
  transition: all var(--gc-transition-fast);
}

.tab.active {
  background: white;
  color: var(--gc-primary);
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.friends-list, .requests-list {
  display: flex;
  flex-direction: column;
  gap: var(--gc-spacing-sm);
}

.friend-item, .request-item {
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

.friend-info, .request-info {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}

.name {
  font-weight: 600;
  color: var(--gc-gray-800);
  font-size: var(--gc-font-size-sm);
}

.status, .mutual {
  font-size: var(--gc-font-size-xs);
  color: var(--gc-gray-500);
}

.status.online {
  color: #10b981;
}

.request-actions {
  display: flex;
  gap: var(--gc-spacing-sm);
}

/* Desktop */
@media (min-width: 768px) {
  .friends-view {
    max-width: 700px;
    margin: 0 auto;
  }
  
  .tab {
    font-size: var(--gc-font-size-base);
  }
}
</style>
