<template>
  <div class="gc-chat">

    <!-- Header -->
    <div class="gc-chat-header">
      <button class="gc-back-btn" @click="router.push('/messages')">
        <i class="ti ti-arrow-left"></i>
      </button>
      <div class="gc-chat-av" :style="avatarGradient(otherUser?.id)">
        {{ initials(otherUser) }}
        <div v-if="otherUser?.is_online" class="gc-online-dot"></div>
      </div>
      <div class="gc-chat-info">
        <div class="gc-chat-name">
          <template v-if="otherUser?.profile?.first_name || otherUser?.profile?.last_name">
            {{ otherUser?.profile?.first_name }} {{ otherUser?.profile?.last_name }}
          </template>
          <template v-else>
            {{ otherUser?.username }}
          </template>
        </div>
        <div class="gc-chat-status" :class="otherUser?.is_online ? 'online' : 'offline'">
          {{ otherUser?.is_online ? 'En ligne' : `Vu ${lastSeen}` }}
        </div>
      </div>
      <div class="gc-chat-actions">
        <button class="gc-action-btn"><i class="ti ti-phone"></i></button>
        <button class="gc-action-btn"><i class="ti ti-video"></i></button>
        <button class="gc-action-btn"><i class="ti ti-dots"></i></button>
      </div>
    </div>

    <!-- Messages -->
    <div class="gc-messages" ref="messagesContainer">

      <template v-for="(group, date) in groupedMessages" :key="date">
        <div class="gc-date-sep">{{ date }}</div>

        <template v-for="msg in group" :key="msg.id">
          <div class="gc-msg-row" :class="{ me: msg.sender_id === auth.user?.id }">

            <!-- Avatar (eux seulement) -->
            <div
              v-if="msg.sender_id !== auth.user?.id"
              class="gc-msg-av"
              :style="avatarGradient(msg.sender_id)"
            >
              {{ initials(otherUser) }}
            </div>

            <!-- Bulle -->
            <div class="gc-bubble-wrap">
              <div class="gc-bubble" :class="msg.sender_id === auth.user?.id ? 'me' : 'them'">
                <!-- Bouton Réaction -->
                <div class="gc-bubble-actions" :class="{ 'forced-show': activeEmojiMsgId === msg.id }">
                  <button class="gc-react-trigger" @click.stop="toggleEmojiPicker(msg.id)">
                    <i class="ti ti-mood-smile"></i>
                  </button>
                  <div v-if="activeEmojiMsgId === msg.id" class="gc-emoji-picker" @click.stop>
                    <span v-for="e in emojis" :key="e" @click="reactToMessage(msg.id, e)">{{ e }}</span>
                  </div>
                </div>

                <!-- Texte -->
                <p v-if="msg.type === 'text'" :class="{ 'optimistic': msg.optimistic }">{{ msg.message }}</p>

                <!-- Réactions affichées -->
                <div v-if="msg.reactions?.length" class="gc-message-reactions">
                  <span 
                    v-for="r in getGroupedReactions(msg.reactions)" 
                    :key="r.reaction"
                    class="gc-reaction-item"
                    :title="r.usernames.join(', ')"
                  >
                    {{ r.reaction }} <small>{{ r.count > 1 ? r.count : '' }}</small>
                  </span>
                </div>

                <!-- Image -->
                <img v-else-if="msg.type === 'image'" :src="msg.file_url" class="gc-media-img" />

                <!-- Audio -->
                <audio v-else-if="msg.type === 'audio'" :src="msg.file_url" controls class="gc-media-audio"></audio>

                <!-- Vidéo -->
                <video v-else-if="msg.type === 'video'" :src="msg.file_url" controls class="gc-media-video"></video>

                <!-- Fichier -->
                <a v-else-if="msg.type === 'file'" :href="msg.file_url" target="_blank" class="gc-file-link">
                  <i class="ti ti-file"></i>
                  {{ msg.message || 'Fichier' }}
                </a>

                <!-- Heure + vu -->
                <div class="gc-bubble-meta">
                  {{ formatTime(msg.created_at) }}
                  <i
                    v-if="msg.sender_id === auth.user?.id"
                    class="ti"
                    :class="msg.is_seen ? 'ti-checks gc-seen' : 'ti-check'"
                  ></i>
                </div>
              </div>

              <!-- Réactions -->
              <div v-if="msg.reactions?.length" class="gc-reactions">
                <span
                  v-for="r in msg.reactions" :key="r.id"
                  class="gc-reaction"
                >{{ r.reaction }}</span>
              </div>
            </div>

          </div>
        </template>
      </template>

      <!-- Typing indicator -->
      <div v-if="isTyping" class="gc-msg-row">
        <div class="gc-msg-av" :style="avatarGradient(otherUser?.id)">
          {{ initials(otherUser) }}
        </div>
        <div class="gc-typing">
          <span></span><span></span><span></span>
        </div>
      </div>

    </div>

    <!-- Bannière accepter/bloquer si en attente -->
    <div v-if="isPending" class="gc-pending-banner">
      <p><strong>{{ otherUser?.profile?.first_name }}</strong> vous a envoyé un message.</p>
      <div class="gc-pending-actions">
        <button class="gc-btn-accept" @click="acceptConversation">Accepter</button>
        <button class="gc-btn-block"  @click="blockConversation">Bloquer</button>
      </div>
    </div>

    <!-- Barre d'envoi -->
    <div class="gc-input-bar" v-if="!isPending && !isBlocked">
      <button class="gc-input-icon" @click="triggerFileInput">
        <i class="ti ti-paperclip"></i>
      </button>
      <input ref="fileInput" type="file" hidden @change="handleFileUpload" />

      <input
        v-model="messageText"
        class="gc-input-field"
        placeholder="Écrire un message..."
        @keyup.enter="sendMessage"
        @input="onTyping"
      />

      <button class="gc-input-icon"><i class="ti ti-mood-smile"></i></button>
      <button class="gc-input-icon"><i class="ti ti-microphone"></i></button>

      <button
        class="gc-send-btn"
        :disabled="!messageText.trim()"
        @click="sendMessage"
      >
        <i class="ti ti-send"></i>
      </button>
    </div>

    <!-- Bloqué -->
    <div v-if="isBlocked" class="gc-blocked-bar">
      <i class="ti ti-ban"></i>
      Vous avez bloqué cette conversation.
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useChatStore } from '@/stores/chat'
import { useAuthStore } from '@/stores/auth'

const route     = useRoute()
const router    = useRouter()
const chatStore = useChatStore()
const auth      = useAuthStore()

const messageText       = ref('')
const messagesContainer = ref(null)
const fileInput         = ref(null)
const activeEmojiMsgId  = ref(null)
const emojis = ['❤️', '😂', '😮', '😢', '😡', '👍', '🙌']

// const isTyping          = ref(false)
let typingTimer         = null

const convId = computed(() => Number(route.params.id))

const loadConversation = async () => {
  if (!convId.value) return
  await chatStore.fetchMessages(convId.value)
  chatStore.subscribeToConversation(convId.value)
  chatStore.markAsRead(convId.value)
  scrollToBottom()
}

onMounted(() => {
  loadConversation()
  window.addEventListener('click', closeEmojiPicker)
})

// Recharger les données quand on change de conversation via la liste
watch(convId, (newId, oldId) => {
  if (oldId) chatStore.unsubscribeFromConversation(oldId)
  loadConversation()
})

// Marquer comme lu quand de nouveaux messages arrivent
watch(() => chatStore.messages[convId.value]?.length, (newLen, oldLen) => {
  if (newLen > (oldLen || 0)) {
    chatStore.markAsRead(convId.value)
  }
  nextTick(scrollToBottom)
})

// Se désabonner à la fermeture
onUnmounted(() => {
  chatStore.unsubscribeFromConversation(convId.value)
  window.removeEventListener('click', closeEmojiPicker)
})

// Utiliser le typing indicator du store
const isTyping = computed(() => chatStore.isUserTyping(convId.value))

const conversation = computed(() => chatStore.conversations.find(c => c.id === convId.value))

const otherUser = computed(() => conversation.value?.other_user)

const lastSeen = computed(() => {
  const ls = otherUser.value?.last_seen
  if (!ls) return 'récemment'
  return new Date(ls).toLocaleString('fr', { hour: '2-digit', minute: '2-digit' })
})

const isPending  = computed(() => conversation.value?.status === 'pending')
const isBlocked  = computed(() => conversation.value?.status === 'blocked')

const groupedMessages = computed(() => {
  const msgs = chatStore.messages[convId.value] ?? []
  return msgs.reduce((acc, msg) => {
    const date = formatDate(msg.created_at)
    if (!acc[date]) acc[date] = []
    acc[date].push(msg)
    return acc
  }, {})
})

async function sendMessage() {
  if (!messageText.value.trim()) return
  const currentMsg = messageText.value
  messageText.value = '' // Vider tout de suite pour UX
  try {
    await chatStore.sendMessage(convId.value, { message: currentMsg, type: 'text' })
  } catch (e) {
    messageText.value = currentMsg // Remettre en cas d'erreur
    console.error("Erreur d'envoi", e)
  }
}

function getGroupedReactions(reactions) {
  if (!reactions) return []
  const groups = {}
  reactions.forEach(r => {
    if (!groups[r.reaction]) {
      groups[r.reaction] = { reaction: r.reaction, count: 0, usernames: [] }
    }
    groups[r.reaction].count++
    if (r.username) groups[r.reaction].usernames.push(r.username)
    else if (r.user_id === auth.user.id) groups[r.reaction].usernames.push('Vous')
  })
  return Object.values(groups)
}

function toggleEmojiPicker(msgId) {
  activeEmojiMsgId.value = activeEmojiMsgId.value === msgId ? null : msgId
}

function closeEmojiPicker() {
  activeEmojiMsgId.value = null
}

async function reactToMessage(msgId, emoji) {
  const msgs = chatStore.messages[convId.value] ?? []
  const msg  = msgs.find(m => m.id === msgId)
  if (!msg) return

  // Optimiste : ajouter localement avant l'appel API
  if (!msg.reactions) msg.reactions = []
  const existing = msg.reactions.find(r => r.user_id === auth.user.id)
  
  if (existing && existing.reaction === emoji) {
    await chatStore.removeReaction(msgId)
    msg.reactions = msg.reactions.filter(r => r.user_id !== auth.user.id)
  } else {
    await chatStore.addReaction(msgId, emoji)
    if (existing) {
      existing.reaction = emoji
      existing.username = auth.user.username
    } else {
      msg.reactions.push({ 
        user_id: auth.user.id, 
        username: auth.user.username, 
        reaction: emoji 
      })
    }
  }
  activeEmojiMsgId.value = null
}

function onTyping() {
  chatStore.sendTyping(convId.value, true)
  clearTimeout(typingTimer)
  typingTimer = setTimeout(() => chatStore.sendTyping(convId.value, false), 2000)
}

function triggerFileInput() {
  fileInput.value?.click()
}

async function handleFileUpload(e) {
  const file = e.target.files[0]
  if (!file) return
  await chatStore.sendFile(convId.value, file)
}

async function acceptConversation() {
  await chatStore.updateStatus(convId.value, 'accepted')
}

async function blockConversation() {
  await chatStore.updateStatus(convId.value, 'blocked')
}

function scrollToBottom() {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
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
]
function avatarGradient(id = 0) {
  return `background: linear-gradient(${gradients[id % gradients.length]})`
}

function formatTime(dateStr) {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleTimeString('fr', { hour: '2-digit', minute: '2-digit' })
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  const now  = new Date()
  if (date.toDateString() === now.toDateString()) return "Aujourd'hui"
  const yesterday = new Date(now); yesterday.setDate(now.getDate() - 1)
  if (date.toDateString() === yesterday.toDateString()) return 'Hier'
  return date.toLocaleDateString('fr', { day: '2-digit', month: 'long' })
}
</script>

<style scoped>
.gc-chat {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background: #fdf0f8;
  overflow: hidden;
}

/* Header */
.gc-chat-header {
  background: #fff;
  border-bottom: .5px solid #f0d6f5;
  padding: 12px 20px;
  display: flex;
  align-items: center;
  gap: 12px;
  flex-shrink: 0;
}

.gc-back-btn {
  background: none;
  border: none;
  color: #c4b5fd;
  cursor: pointer;
  padding: 4px;
  display: none;
}

.gc-chat-av {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 600;
  color: #fff;
  flex-shrink: 0;
  position: relative;
}

.gc-online-dot {
  position: absolute;
  bottom: 1px;
  right: 1px;
  width: 10px;
  height: 10px;
  background: #22c55e;
  border-radius: 50%;
  border: 2px solid #fff;
}

.gc-chat-info { flex: 1; }
.gc-chat-name { font-size: 15px; font-weight: 600; color: #1a1a2e; }
.gc-chat-status { font-size: 12px; }
.gc-chat-status.online  { color: #22c55e; }
.gc-chat-status.offline { color: #9ca3af; }

.gc-chat-actions { display: flex; gap: 8px; }
.gc-action-btn {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  border: none;
  background: #fdf4ff;
  color: #c4b5fd;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all .15s;
}
.gc-action-btn:hover { background: #f0d6f5; color: #a855f7; }
.gc-action-btn i { font-size: 18px; }

/* Messages */
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

.gc-date-sep {
  text-align: center;
  font-size: 11px;
  color: #9ca3af;
  background: #fce7f3;
  padding: 3px 14px;
  border-radius: 99px;
  margin: 10px auto;
  width: fit-content;
}

.gc-msg-row {
  display: flex;
  align-items: flex-end;
  gap: 8px;
  margin-bottom: 4px;
}
.gc-msg-row.me { flex-direction: row-reverse; }

.gc-msg-av {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  font-weight: 600;
  color: #fff;
  flex-shrink: 0;
}

.gc-bubble-wrap {
  display: flex;
  flex-direction: column;
  gap: 2px;
  max-width: 80%;
  position: relative;
}

.gc-bubble-actions {
  position: absolute;
  top: -28px;
  right: 0;
  display: none;
  background: white;
  border-radius: 20px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  padding: 4px 10px;
  z-index: 10;
  /* Zone de sécurité pour le survol */
  padding-bottom: 20px;
  margin-bottom: -15px;
}

.gc-bubble:hover .gc-bubble-actions,
.gc-bubble-actions.forced-show {
  display: flex;
}

.gc-react-trigger {
  background: #f8f9fa;
  border: 1px solid #eee;
  color: #9ca3af;
  cursor: pointer;
  padding: 4px;
  border-radius: 50%;
  font-size: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}
.gc-react-trigger:hover { 
  color: #f59e0b; 
  background: #fff;
  transform: scale(1.1);
}

.gc-emoji-picker {
  position: absolute;
  bottom: 100%;
  right: 0;
  background: white;
  border-radius: 24px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.15);
  display: flex;
  gap: 10px;
  padding: 10px 14px;
  margin-bottom: 12px;
  z-index: 20;
  border: 1px solid #f0f0f0;
}

.gc-emoji-picker span {
  cursor: pointer;
  font-size: 20px;
  transition: transform .1s;
}
.gc-emoji-picker span:hover { transform: scale(1.3); }

.gc-bubble p.optimistic {
  opacity: 0.7;
}

.gc-message-reactions {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  margin-top: 4px;
}

.gc-reaction-item {
  background: white;
  border: 1px solid #f0f0f0;
  border-radius: 12px;
  padding: 2px 8px;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 4px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  cursor: help;
}

.gc-reaction-item small {
  color: #6b7280;
  font-weight: 600;
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
  box-shadow: 0 1px 4px rgba(168,85,247,.08);
}
.gc-bubble.me {
  background: linear-gradient(135deg, #f472b6, #a855f7);
  color: #fff;
  border-bottom-right-radius: 4px;
}

.gc-bubble-meta {
  font-size: 10px;
  margin-top: 4px;
  display: flex;
  align-items: center;
  gap: 3px;
  justify-content: flex-end;
  opacity: .7;
}
.gc-bubble.them .gc-bubble-meta { justify-content: flex-start; }

.gc-seen { color: #a855f7; opacity: 1 !important; }

/* Médias */
.gc-media-img  { max-width: 200px; border-radius: 10px; display: block; }
.gc-media-audio { width: 200px; }
.gc-media-video { max-width: 220px; border-radius: 10px; }
.gc-file-link {
  display: flex; align-items: center; gap: 6px;
  color: inherit; text-decoration: underline; font-size: 13px;
}

/* Réactions */
.gc-reactions { display: flex; gap: 4px; margin-top: 3px; }
.gc-reaction {
  background: #fff;
  border: .5px solid #f0d6f5;
  border-radius: 99px;
  padding: 1px 6px;
  font-size: 12px;
  cursor: pointer;
}

/* Typing */
.gc-typing {
  display: flex;
  gap: 4px;
  align-items: center;
  padding: 12px 16px;
  background: #fff;
  border-radius: 18px;
  border-bottom-left-radius: 4px;
  box-shadow: 0 1px 4px rgba(168,85,247,.08);
}
.gc-typing span {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #f9a8d4;
  animation: bounce .9s infinite;
}
.gc-typing span:nth-child(2) { animation-delay: .15s; }
.gc-typing span:nth-child(3) { animation-delay: .3s; }
@keyframes bounce {
  0%,60%,100% { transform: translateY(0); }
  30%          { transform: translateY(-5px); }
}

/* Bannière pending */
.gc-pending-banner {
  background: #fff;
  border-top: .5px solid #f0d6f5;
  padding: 16px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-shrink: 0;
}
.gc-pending-banner p { font-size: 14px; color: #374151; }
.gc-pending-actions { display: flex; gap: 10px; }

.gc-btn-accept {
  background: linear-gradient(135deg, #f472b6, #a855f7);
  color: #fff;
  border: none;
  border-radius: 10px;
  padding: 8px 18px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
}
.gc-btn-block {
  background: #fef2f2;
  color: #dc2626;
  border: 1px solid #fecaca;
  border-radius: 10px;
  padding: 8px 18px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
}

/* Barre d'envoi */
.gc-input-bar {
  background: #fff;
  border-top: .5px solid #f0d6f5;
  padding: 10px 16px;
  display: flex;
  align-items: center;
  gap: 10px;
  flex-shrink: 0;
}

.gc-input-icon {
  background: none;
  border: none;
  color: #c4b5fd;
  cursor: pointer;
  display: flex;
  align-items: center;
  padding: 4px;
  transition: color .15s;
}
.gc-input-icon:hover { color: #a855f7; }
.gc-input-icon i { font-size: 20px; }

.gc-input-field {
  flex: 1;
  border: .5px solid #f0d6f5;
  border-radius: 24px;
  padding: 10px 16px;
  font-size: 13px;
  color: #1a1a2e;
  background: #fdf4ff;
  outline: none;
  transition: border-color .15s;
}
.gc-input-field:focus { border-color: #c4b5fd; }
.gc-input-field::placeholder { color: #c4b5fd; }

.gc-send-btn {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #f472b6, #a855f7);
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  flex-shrink: 0;
  transition: opacity .15s, transform .15s;
}
.gc-send-btn:hover:not(:disabled) { opacity: .9; }
.gc-send-btn:active:not(:disabled) { transform: scale(0.95); }
.gc-send-btn:disabled { opacity: .5; cursor: not-allowed; }
.gc-send-btn i { font-size: 17px; color: #fff; }

/* Bloqué */
.gc-blocked-bar {
  background: #fef2f2;
  border-top: .5px solid #fecaca;
  padding: 14px 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-size: 13px;
  color: #dc2626;
  flex-shrink: 0;
}
.gc-blocked-bar i { font-size: 16px; }

/* Responsive mobile */
@media (max-width: 640px) {
  .gc-back-btn { display: flex; }
}
</style>