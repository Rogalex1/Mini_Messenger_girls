<template>
  <div class="gc-home">

    <!-- Panel conversations -->
    <aside class="gc-panel">
      <div class="gc-panel-header">
        <h1>Messages</h1>
        <button class="gc-add-btn" @click="showNewConv = true">
          <i class="ti ti-plus"></i>
        </button>
      </div>

      <!-- Recherche -->
      <div class="gc-search">
        <i class="ti ti-search"></i>
        <input v-model="search" placeholder="Rechercher..." />
      </div>

      <!-- Stories (actifs récents) -->
      <div class="gc-stories">
        <div
          v-for="user in onlineUsers"
          :key="user.id"
          class="gc-story"
          @click="openConversation(user)"
        >
          <div class="gc-story-av" :style="avatarGradient(user.id)">
            {{ initials(user) }}
            <div class="gc-online-dot"></div>
          </div>
          <span>{{ user.profile?.first_name || user.username }}</span>
        </div>
      </div>

      <!-- Liste conversations -->
      <div class="gc-conv-list">
        <div
          v-for="conv in filteredConversations"
          :key="conv.id"
          class="gc-conv-item"
          :class="{ active: activeConvId === conv.id }"
          @click="selectConversation(conv)"
        >
          <div class="gc-conv-av" :style="avatarGradient(conv.other_user?.id)">
            {{ initials(conv.other_user) }}
            <div v-if="conv.other_user?.is_online" class="gc-online-dot"></div>
          </div>
          <div class="gc-conv-info">
            <div class="gc-conv-top">
              <span class="gc-conv-name">
                <template v-if="conv.other_user?.profile?.first_name || conv.other_user?.profile?.last_name">
                  {{ conv.other_user?.profile?.first_name }} {{ conv.other_user?.profile?.last_name }}
                </template>
                <template v-else>
                  {{ conv.other_user?.username }}
                </template>
              </span>
              <span class="gc-conv-time">{{ formatTime(conv.last_message?.created_at) }}</span>
            </div>
            <div class="gc-conv-bottom">
              <span class="gc-conv-preview">{{ conv.last_message?.message || '...' }}</span>
              <span v-if="conv.unread_count" class="gc-conv-badge">
                {{ conv.unread_count }}
              </span>
            </div>
          </div>
        </div>

        <!-- État vide -->
        <div v-if="filteredConversations.length === 0" class="gc-empty">
          <i class="ti ti-message-off"></i>
          <p>Aucune conversation</p>
        </div>
      </div>
    </aside>

    <!-- Zone chat ou écran d'accueil -->
    <div class="gc-main">
      <RouterView v-if="activeConvId" />
      <div v-else class="gc-welcome">
        <div class="gc-welcome-icon">
          <i class="ti ti-message-circle"></i>
        </div>
        <h2>Bienvenue sur GlowChat</h2>
        <p>Sélectionnez une conversation pour commencer</p>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useChatStore } from '@/stores/chat'
import { useAuthStore } from '@/stores/auth'

const router    = useRouter()
const route     = useRoute()
const chatStore = useChatStore()
const auth      = useAuthStore()

const search      = ref('')
const activeConvId = ref(route.params.id)

// Surveiller les changements d'URL pour mettre à jour la conversation active
watch(() => route.params.id, (newId) => {
  activeConvId.value = newId
})
const showNewConv  = ref(false)

onMounted(() => chatStore.fetchConversations())

const filteredConversations = computed(() => {
  if (!search.value) return chatStore.conversations
  const q = search.value.toLowerCase()
  return chatStore.conversations.filter(c => {
    const name = `${c.other_user?.profile?.first_name} ${c.other_user?.profile?.last_name}`.toLowerCase()
    return name.includes(q)
  })
})

const onlineUsers = computed(() =>
  chatStore.conversations
    .filter(c => c.other_user?.is_online)
    .map(c => c.other_user)
    .slice(0, 6)
)

function selectConversation(conv) {
  activeConvId.value = conv.id
  router.push(`/messages/${conv.id}`)
}

function openConversation(user) {
  const conv = chatStore.conversations.find(c => c.other_user?.id === user.id)
  if (conv) selectConversation(conv)
}

// Helpers
function initials(user) {
  if (!user) return '?'
  const f = user.profile?.first_name?.[0] ?? ''
  const l = user.profile?.last_name?.[0]  ?? ''
  return (f + l).toUpperCase() || user.username?.[0]?.toUpperCase() || '?'
}

const gradients = [
  '135deg, #f472b6, #a855f7',
  '135deg, #f9a8d4, #e879f9',
  '135deg, #c084fc, #818cf8',
  '135deg, #fda4af, #fb7185',
  '135deg, #a5b4fc, #818cf8',
  '135deg, #6ee7b7, #34d399',
  '135deg, #fcd34d, #f59e0b',
]

function avatarGradient(id = 0) {
  const g = gradients[id % gradients.length]
  return `background: linear-gradient(${g})`
}

function formatTime(dateStr) {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  const now  = new Date()
  const diff = now - date
  const day  = 86400000

  if (diff < day)       return date.toLocaleTimeString('fr', { hour: '2-digit', minute: '2-digit' })
  if (diff < 2 * day)   return 'Hier'
  const days = ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam']
  if (diff < 7 * day)   return days[date.getDay()]
  return date.toLocaleDateString('fr', { day: '2-digit', month: '2-digit' })
}
</script>

<style scoped>
.gc-home {
  display: grid;
  grid-template-columns: 300px 1fr;
  height: 100vh;
  overflow: hidden;
}

/* ─── Panel ───────────────────────────────── */
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

/* Recherche */
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
  border: none;
  background: transparent;
  font-size: 13px;
  color: #1a1a2e;
  outline: none;
  flex: 1;
}
.gc-search input::placeholder { color: #c4b5fd; }

/* Stories */
.gc-stories {
  display: flex;
  gap: 14px;
  padding: 0 14px 14px;
  overflow-x: auto;
}
.gc-stories::-webkit-scrollbar { display: none; }

.gc-story {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 5px;
  cursor: pointer;
}

.gc-story-av {
  width: 46px;
  height: 46px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 600;
  color: #fff;
  position: relative;
  flex-shrink: 0;
  transition: transform .15s;
}
.gc-story-av:hover { transform: scale(1.05); }

.gc-online-dot {
  position: absolute;
  bottom: 1px;
  right: 1px;
  width: 11px;
  height: 11px;
  background: #22c55e;
  border-radius: 50%;
  border: 2px solid #fff;
}

.gc-story span {
  font-size: 11px;
  color: #6b7280;
  white-space: nowrap;
}

/* Liste conversations */
.gc-conv-list {
  flex: 1;
  overflow-y: auto;
  padding: 0 6px 10px;
}
.gc-conv-list::-webkit-scrollbar { width: 3px; }
.gc-conv-list::-webkit-scrollbar-thumb { background: #f0d6f5; border-radius: 99px; }

.gc-conv-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 10px;
  border-radius: 14px;
  cursor: pointer;
  transition: background .15s;
}
.gc-conv-item:hover,
.gc-conv-item.active { background: #fdf4ff; }

.gc-conv-av {
  width: 46px;
  height: 46px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: 600;
  color: #fff;
  flex-shrink: 0;
  position: relative;
}

.gc-conv-info { flex: 1; min-width: 0; }

.gc-conv-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2px;
}

.gc-conv-name { font-size: 14px; font-weight: 500; color: #1a1a2e; }
.gc-conv-time { font-size: 11px; color: #9ca3af; flex-shrink: 0; }

.gc-conv-bottom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 6px;
}

.gc-conv-preview {
  font-size: 12px;
  color: #9ca3af;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.gc-conv-badge {
  background: linear-gradient(135deg, #f472b6, #a855f7);
  color: #fff;
  font-size: 10px;
  font-weight: 600;
  padding: 2px 6px;
  border-radius: 99px;
  min-width: 18px;
  text-align: center;
  flex-shrink: 0;
}

/* Vide */
.gc-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 40px 20px;
  color: #c4b5fd;
}
.gc-empty i { font-size: 36px; }
.gc-empty p { font-size: 13px; color: #9ca3af; }

/* ─── Main ────────────────────────────────── */
.gc-main {
  background: #fdf0f8;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.gc-welcome {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
}

.gc-welcome-icon {
  width: 72px;
  height: 72px;
  border-radius: 24px;
  background: linear-gradient(135deg, #f472b6, #a855f7);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 8px;
}
.gc-welcome-icon i { font-size: 36px; color: #fff; }

.gc-welcome h2 { font-size: 20px; font-weight: 600; color: #1a1a2e; }
.gc-welcome p  { font-size: 14px; color: #9ca3af; }
</style>