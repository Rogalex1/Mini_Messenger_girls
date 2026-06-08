<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useGroupeStore } from '@/stores/Groupe'
import { useAuthStore } from '@/stores/auth'
import echo from '@/echo'

const route        = useRoute()
const router       = useRouter()
const groupeStore  = useGroupeStore()
const auth         = useAuthStore()

const groupId          = computed(() => Number(route.params.id))
const messageText      = ref('')
const messagesContainer = ref(null)

const currentGroup = computed(() =>
  groupeStore.groupes.find(g => g.id === groupId.value)
)

onMounted(async () => {
  await groupeStore.fetchGroupMessage(groupId.value)

  echo.private(`group.${groupId.value}`)
    .listen('.group.message.sent', (e) => {
      groupeStore.currentMessages.push(e)
      nextTick(scrollToBottom)
    })

  nextTick(scrollToBottom)
})

onUnmounted(() => {
  echo.leave(`group.${groupId.value}`)
})

async function sendMessage() {
  if (!messageText.value.trim()) return
  const text = messageText.value.trim()
  messageText.value = ''
  await groupeStore.sendGroupMessages(groupId.value, text)
  nextTick(scrollToBottom)
}

function scrollToBottom() {
  if (messagesContainer.value)
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
}

function formatTime(dateStr) {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleTimeString('fr', { hour: '2-digit', minute: '2-digit' })
}

const gradients = [
  '135deg, #f472b6, #a855f7',
  '135deg, #f9a8d4, #e879f9',
  '135deg, #c084fc, #818cf8',
  '135deg, #fda4af, #fb7185',
  '135deg, #a5b4fc, #818cf8',
]
function avatarGradient(id = 0) {
  return `background: linear-gradient(${gradients[id % gradients.length]})`
}

function initials(sender) {
  if (!sender) return '?'
  return sender.username?.[0]?.toUpperCase() || '?'
}
</script>

<template>
  <div class="gc-chat">

    <!-- Header -->
    <div class="gc-chat-header">
      <div class="gc-group-av" :style="avatarGradient(groupId)">
        {{ currentGroup?.name?.[0]?.toUpperCase() || '#' }}
      </div>
      <div class="gc-chat-info">
        <div class="gc-chat-name">{{ currentGroup?.name || 'Groupe' }}</div>
        <div class="gc-chat-sub">{{ currentGroup?.members?.length || 0 }} membres</div>
      </div>
    </div>

    <!-- Messages -->
    <div class="gc-messages" ref="messagesContainer">

      <div v-if="groupeStore.loading" class="gc-loading">
        <div class="spinner"></div>
      </div>

      <div v-else-if="groupeStore.currentMessages.length === 0" class="gc-empty-msg">
        <p>Aucun message pour l'instant. Soyez le premier à écrire !</p>
      </div>

      <div
        v-for="msg in groupeStore.currentMessages"
        :key="msg.id"
        class="gc-msg-row"
        :class="{ me: msg.sender_id === auth.user?.id }"
      >
        <!-- Avatar expéditeur (autres seulement) -->
        <div
          v-if="msg.sender_id !== auth.user?.id"
          class="gc-msg-av"
          :style="avatarGradient(msg.sender_id)"
        >
          {{ initials(msg.sender) }}
        </div>

        <div class="gc-bubble-wrap">
          <!-- Nom de l'expéditeur (autres seulement) -->
          <span class="gc-sender-name" v-if="msg.sender_id !== auth.user?.id">
            {{ msg.sender?.username }}
          </span>

          <!-- Bulle -->
          <div class="gc-bubble" :class="msg.sender_id === auth.user?.id ? 'me' : 'them'">
            <p>{{ msg.message }}</p>
            <span class="gc-time">{{ formatTime(msg.created_at) }}</span>
          </div>
        </div>
      </div>

    </div>

    <!-- Input -->
    <div class="gc-input-bar">
      <input
        v-model="messageText"
        class="gc-input-field"
        placeholder="Écrire un message..."
        @keyup.enter.exact="sendMessage"
      />
      <button class="gc-send-btn" :disabled="!messageText.trim()" @click="sendMessage">
        <i class="ti ti-send"></i>
      </button>
    </div>

  </div>
</template>

<style scoped>
.gc-chat {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background: #fdf0f8;
  overflow: hidden;
}

/* ── Header ───────────────────────────────────────────── */
.gc-chat-header {
  background: #fff;
  border-bottom: .5px solid #f0d6f5;
  padding: 12px 20px;
  display: flex;
  align-items: center;
  gap: 12px;
  flex-shrink: 0;
}

.gc-group-av {
  width: 40px; height: 40px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 14px; font-weight: 700; color: #fff;
  flex-shrink: 0;
}

.gc-chat-info { flex: 1; }
.gc-chat-name { font-size: 15px; font-weight: 600; color: #1a1a2e; }
.gc-chat-sub  { font-size: 12px; color: #9ca3af; }

/* ── Messages ─────────────────────────────────────────── */
.gc-messages {
  flex: 1;
  overflow-y: auto;
  padding: 16px 20px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.gc-messages::-webkit-scrollbar { width: 3px; }
.gc-messages::-webkit-scrollbar-thumb { background: #f0d6f5; border-radius: 99px; }

.gc-msg-row {
  display: flex;
  align-items: flex-end;
  gap: 8px;
  margin-bottom: 6px;
}
.gc-msg-row.me { flex-direction: row-reverse; }

.gc-msg-av {
  width: 30px; height: 30px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 11px; font-weight: 600; color: #fff;
  flex-shrink: 0;
}

.gc-bubble-wrap {
  display: flex;
  flex-direction: column;
  gap: 2px;
  max-width: 65%;
}

.gc-sender-name {
  font-size: 11px;
  font-weight: 600;
  color: #a855f7;
  padding-left: 4px;
}

.gc-bubble {
  padding: 10px 14px;
  border-radius: 18px;
  font-size: 13px;
  line-height: 1.5;
  word-break: break-word;
}

.gc-bubble.them {
  background: #fff;
  color: #1a1a2e;
  border-bottom-left-radius: 4px;
  box-shadow: 0 1px 4px rgba(0,0,0,.06);
}

.gc-bubble.me {
  background: linear-gradient(135deg, #f472b6, #a855f7);
  color: #fff;
  border-bottom-right-radius: 4px;
}

.gc-bubble p { margin: 0 0 4px; }

.gc-time {
  font-size: 10px;
  opacity: 0.65;
  display: block;
  text-align: right;
}

/* ── États ────────────────────────────────────────────── */
.gc-loading, .gc-empty-msg {
  display: flex;
  align-items: center;
  justify-content: center;
  flex: 1;
  color: #9ca3af;
  font-size: 13px;
  padding: 40px;
}

.spinner {
  width: 32px; height: 32px;
  border: 3px solid #f0d6f5;
  border-top: 3px solid #ec4899;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Input ────────────────────────────────────────────── */
.gc-input-bar {
  background: #fff;
  border-top: .5px solid #f0d6f5;
  padding: 12px 16px;
  display: flex;
  align-items: center;
  gap: 10px;
  flex-shrink: 0;
}

.gc-input-field {
  flex: 1;
  background: #fdf4ff;
  border: none;
  border-radius: 24px;
  padding: 10px 16px;
  font-size: 13px;
  color: #1a1a2e;
  outline: none;
}
.gc-input-field::placeholder { color: #c4b5fd; }

.gc-send-btn {
  width: 40px; height: 40px;
  border-radius: 50%;
  border: none;
  background: linear-gradient(135deg, #f472b6, #a855f7);
  color: #fff;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: opacity .15s;
  flex-shrink: 0;
}
.gc-send-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.gc-send-btn i { font-size: 18px; }
</style>
