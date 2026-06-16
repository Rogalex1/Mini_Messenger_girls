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
          <template v-else>{{ otherUser?.username }}</template>
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
          <div
            class="gc-msg-row"
            :class="{ me: msg.sender_id === auth.user?.id }"
            @mouseenter="hoveredMsgId = msg.id"
            @mouseleave="hoveredMsgId = null"
          >
            <!-- Avatar (eux seulement) -->
            <div
              v-if="msg.sender_id !== auth.user?.id"
              class="gc-msg-av"
              :style="avatarGradient(msg.sender_id)"
            >
              {{ initials(otherUser) }}
            </div>

            <div class="gc-bubble-wrap">

              <!-- Actions flottantes (modifier / supprimer / répondre) -->
              <Transition name="gc-fade">
                <div
                  v-if="hoveredMsgId === msg.id && !msg.is_deleted"
                  class="gc-msg-actions"
                  :class="{ me: msg.sender_id === auth.user?.id }"
                >
                  <!-- Modifier — seulement mes messages texte -->
                  <button
                    v-if="msg.sender_id === auth.user?.id && msg.type === 'text'"
                    @click="startEdit(msg)"
                    title="Modifier"
                  >
                    <i class="ti ti-pencil"></i>
                  </button>
                  <!-- Répondre -->
                  <button @click="setReply(msg)" title="Répondre">
                    <i class="ti ti-arrow-back-up"></i>
                  </button>
                  <!-- Réagir -->
                  <button @click.stop="toggleEmojiPicker(msg.id)" title="Réagir">
                    <i class="ti ti-mood-smile"></i>
                  </button>
                  <!-- Supprimer — seulement mes messages -->
                  <button
                    v-if="msg.sender_id === auth.user?.id"
                    @click="confirmDelete(msg)"
                    title="Supprimer"
                    class="gc-action-danger"
                  >
                    <i class="ti ti-trash"></i>
                  </button>
                </div>
              </Transition>

              <!-- Picker emoji -->
              <Transition name="gc-fade">
                <div
                  v-if="activeEmojiMsgId === msg.id"
                  class="gc-emoji-picker"
                  :class="{ me: msg.sender_id === auth.user?.id }"
                  @click.stop
                >
                  <span
                    v-for="e in emojis" :key="e"
                    @click="reactToMessage(msg.id, e)"
                  >{{ e }}</span>
                </div>
              </Transition>

              <!-- Bulle -->
              <div class="gc-bubble" :class="msg.sender_id === auth.user?.id ? 'me' : 'them'">

                <!-- Message supprimé -->
                <p v-if="msg.is_deleted" class="gc-deleted">
                  <i class="ti ti-ban"></i> Message supprimé
                </p>

                <template v-else>

                  <!-- Réponse à -->
                  <div v-if="msg.reply_to" class="gc-reply-preview">
                    <span class="gc-reply-bar"></span>
                    <p>{{ msg.reply_to.message || '📎 Fichier' }}</p>
                  </div>

                  <!-- Édition en cours -->
                  <div v-if="editingMsgId === msg.id" class="gc-edit-area">
                    <textarea
                      v-model="editText"
                      @keyup.enter.exact="saveEdit(msg)"
                      @keyup.escape="cancelEdit"
                      rows="1"
                      ref="editInputRef"
                    ></textarea>
                    <div class="gc-edit-btns">
                      <button @click="cancelEdit" class="gc-edit-cancel">Annuler</button>
                      <button @click="saveEdit(msg)" class="gc-edit-save">Enregistrer</button>
                    </div>
                  </div>

                  <!-- Texte -->
                  <p
                    v-else-if="msg.type === 'text'"
                    :class="{ optimistic: msg.optimistic }"
                  >
                    {{ msg.message }}
                    <span v-if="msg.updated_at && msg.updated_at !== msg.created_at" class="gc-edited">
                      (modifié)
                    </span>
                  </p>

                  <!-- Image -->
                  <div v-else-if="msg.type === 'image'" class="gc-media-wrap">
                    <img
                      :src="msg.file_url"
                      class="gc-media-img"
                      @click="lightboxUrl = msg.file_url"
                    />
                  </div>

                  <!-- Audio -->
                  <div v-else-if="msg.type === 'audio'" class="gc-audio-wrap">
                    <i class="ti ti-microphone gc-audio-icon"></i>
                    <audio :src="msg.file_url" controls class="gc-media-audio"></audio>
                  </div>

                  <!-- Vidéo -->
                  <video
                    v-else-if="msg.type === 'video'"
                    :src="msg.file_url"
                    controls
                    class="gc-media-video"
                  ></video>

                  <!-- Fichier -->
                  
                 <a   v-else-if="msg.type === 'file'"
                    :href="msg.file_url"
                    target="_blank"
                    class="gc-file-bubble"
                  >
                    <div class="gc-file-icon"><i class="ti ti-file-text"></i></div>
                    <div class="gc-file-info">
                      <span class="gc-file-name">{{ msg.message }}</span>
                      <span class="gc-file-dl">Télécharger</span>
                    </div>
                    <i class="ti ti-download"></i>
                  </a>

                </template>

                <!-- Heure + vu -->
                <div class="gc-bubble-meta" v-if="!msg.is_deleted">
                  {{ formatTime(msg.created_at) }}
                  <i
                    v-if="msg.sender_id === auth.user?.id"
                    class="ti"
                    :class="msg.is_seen ? 'ti-checks gc-seen' : 'ti-check'"
                  ></i>
                </div>
              </div>

              <!-- Réactions affichées sous la bulle -->
              <div v-if="msg.reactions?.length" class="gc-message-reactions">
                <span
                  v-for="r in getGroupedReactions(msg.reactions)"
                  :key="r.reaction"
                  class="gc-reaction-item"
                  :title="r.usernames.join(', ')"
                  @click="reactToMessage(msg.id, r.reaction)"
                >
                  {{ r.reaction }}
                  <small v-if="r.count > 1">{{ r.count }}</small>
                </span>
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
      <p><strong>{{ otherUser?.profile?.first_name || otherUser?.username }}</strong> vous a envoyé un message.</p>
      <div class="gc-pending-actions">
        <button class="gc-btn-accept" @click="acceptConversation">Accepter</button>
        <button class="gc-btn-block"  @click="blockConversation">Bloquer</button>
      </div>
    </div>

    <!-- Barre d'envoi -->
    <template v-if="!isPending && !isBlocked">

      <!-- Preview fichier -->
      <Transition name="gc-slide">
        <div v-if="selectedFile" class="gc-file-preview">
          <div class="gc-file-preview-inner">
            <img v-if="previewUrl && isImage" :src="previewUrl" class="gc-preview-img" />
            <div v-else class="gc-preview-file">
              <i :class="fileIcon"></i>
              <div>
                <p class="gc-preview-name">{{ selectedFile.name }}</p>
                <p class="gc-preview-size">{{ fileSize }}</p>
              </div>
            </div>
            <button class="gc-preview-remove" @click="clearFile">
              <i class="ti ti-x"></i>
            </button>
          </div>
          <div v-if="uploadProgress > 0" class="gc-progress-wrap">
            <div class="gc-progress-bar" :style="{ width: uploadProgress + '%' }"></div>
            <span>{{ uploadProgress }}%</span>
          </div>
        </div>
      </Transition>

      <!-- Bannière répondre à -->
      <Transition name="gc-slide">
        <div v-if="replyTo" class="gc-reply-banner">
          <div class="gc-reply-content">
            <i class="ti ti-arrow-back-up"></i>
            <span>{{ replyTo.message || '📎 Fichier' }}</span>
          </div>
          <button @click="replyTo = null"><i class="ti ti-x"></i></button>
        </div>
      </Transition>

      <!-- Barre principale -->
      <div class="gc-input-bar">

        <!-- Pièce jointe -->
<div class="gc-attach-wrap">
  <button class="gc-input-icon" @click="showAttachMenu = !showAttachMenu">
    <i class="ti ti-paperclip"></i>
  </button>
  <Transition name="gc-fade">
    <div v-if="showAttachMenu" class="gc-attach-menu">
      <label class="gc-attach-item">
        <div class="gc-attach-icon gc-icon-img"><i class="ti ti-photo"></i></div>
        <span>Image / Vidéo</span>
        <input type="file" hidden accept="image/*,video/*" @change="onFileSelect" />
      </label>
      <label class="gc-attach-item">
        <div class="gc-attach-icon gc-icon-audio"><i class="ti ti-music"></i></div>
        <span>Audio</span>
        <input type="file" hidden accept="audio/*" @change="onFileSelect" />
      </label>
      <label class="gc-attach-item">
        <div class="gc-attach-icon gc-icon-file"><i class="ti ti-file"></i></div>
        <span>Fichier</span>
        <input type="file" hidden @change="onFileSelect" />
      </label>
    </div>
  </Transition>
</div>

        <!-- Champ texte -->
        <input
          v-model="messageText"
          class="gc-input-field"
          placeholder="Écrire un message..."
          @keyup.enter.exact="sendMessage"
          @input="onTyping"
          ref="inputField"
        />

        <button class="gc-input-icon"><i class="ti ti-mood-smile"></i></button>

        <!-- Micro -->
        <button
          class="gc-input-icon"
          :class="{ 'is-recording': isRecording }"
          @mousedown="startRecording"
          @mouseup="stopRecording"
          @touchstart.prevent="startRecording"
          @touchend="stopRecording"
        >
          <i class="ti ti-microphone"></i>
        </button>

        <button class="gc-send-btn" :disabled="!canSend" @click="sendMessage">
          <i class="ti ti-send"></i>
        </button>
      </div>

    </template>

    <!-- Bloqué -->
    <div v-if="isBlocked" class="gc-blocked-bar">
      <i class="ti ti-ban"></i>
      Vous avez bloqué cette conversation.
    </div>

    <!-- Lightbox image -->
    <Teleport to="body">
      <div v-if="lightboxUrl" class="gc-lightbox" @click="lightboxUrl = null">
        <button class="gc-lightbox-close"><i class="ti ti-x"></i></button>
        <img :src="lightboxUrl" />
      </div>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useChatStore } from '@/stores/chat'
import { useAuthStore } from '@/stores/auth'
import api from '@/api/axios'

const route     = useRoute()
const router    = useRouter()
const chatStore = useChatStore()
const auth      = useAuthStore()

// ── Refs ────────────────────────────────────────────────
const messageText       = ref('')
const messagesContainer = ref(null)
const fileInput         = ref(null)
const inputField        = ref(null)
const editInputRef      = ref(null)

// Emoji
const activeEmojiMsgId  = ref(null)
const emojis = ['❤️', '😂', '😮', '😢', '😡', '👍', '🙌']

// Actions messages
const hoveredMsgId  = ref(null)
const editingMsgId  = ref(null)
const editText      = ref('')
const lightboxUrl   = ref(null)

// Upload
const selectedFile   = ref(null)
const previewUrl     = ref(null)
const showAttachMenu = ref(false)
const uploadProgress = ref(0)

// Reply
const replyTo = ref(null)

// Audio record
const isRecording = ref(false)
let   mediaRecorder = null
let   audioChunks   = []

// Typing
let typingTimer = null

// ── Computed ────────────────────────────────────────────
const convId = computed(() => Number(route.params.id))

const conversation = computed(() =>
  chatStore.conversations.find(c => c.id === convId.value)
)

const otherUser  = computed(() => conversation.value?.other_user)
const isPending  = computed(() => conversation.value?.status === 'pending')
const isBlocked  = computed(() => conversation.value?.status === 'blocked')
const isTyping   = computed(() => chatStore.isUserTyping(convId.value))

const lastSeen = computed(() => {
  const ls = otherUser.value?.last_seen
  if (!ls) return 'récemment'
  return new Date(ls).toLocaleString('fr', { hour: '2-digit', minute: '2-digit' })
})

const canSend = computed(() =>
  messageText.value.trim().length > 0 || selectedFile.value !== null
)

const isImage = computed(() => selectedFile.value?.type.startsWith('image/'))

const fileIcon = computed(() => {
  const t = selectedFile.value?.type ?? ''
  if (t.startsWith('video/')) return 'ti ti-video'
  if (t.startsWith('audio/')) return 'ti ti-music'
  return 'ti ti-file-text'
})

const fileSize = computed(() => {
  const s = selectedFile.value?.size ?? 0
  if (s < 1024)        return s + ' o'
  if (s < 1024 * 1024) return (s / 1024).toFixed(1) + ' Ko'
  return (s / 1024 / 1024).toFixed(1) + ' Mo'
})

const groupedMessages = computed(() => {
  const msgs = chatStore.messages[convId.value] ?? []
  return msgs.reduce((acc, msg) => {
    const date = formatDate(msg.created_at)
    if (!acc[date]) acc[date] = []
    acc[date].push(msg)
    return acc
  }, {})
})

// ── Lifecycle ───────────────────────────────────────────
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

watch(convId, (newId, oldId) => {
  if (oldId) chatStore.unsubscribeFromConversation(oldId)
  loadConversation()
})

watch(() => chatStore.messages[convId.value]?.length, (newLen, oldLen) => {
  if (newLen > (oldLen || 0)) chatStore.markAsRead(convId.value)
  nextTick(scrollToBottom)
})

onUnmounted(() => {
  chatStore.unsubscribeFromConversation(convId.value)
  window.removeEventListener('click', closeEmojiPicker)
})

// ── Envoi message ───────────────────────────────────────
async function sendMessage() {
  if (!canSend.value) return

  // Envoi fichier
  if (selectedFile.value) {
    await uploadFile(selectedFile.value)
    clearFile()
    return
  }

  // Envoi texte
  const text = messageText.value.trim()
  messageText.value = ''
  try {
    await chatStore.sendMessage(convId.value, {
      message:     text,
      type:        'text',
      reply_to_id: replyTo.value?.id ?? null,
    })
    replyTo.value = null
    inputField.value?.focus()
  } catch (e) {
    messageText.value = text
    console.error('Erreur envoi', e)
  }
}

// ── Upload fichier ──────────────────────────────────────
// ── Upload fichier (Votre fonction existante) ───────────────────────────
async function uploadFile(file) {
  const form = new FormData()
  form.append('file', file) // Laravel recevra le fichier via $request->file('file')
  uploadProgress.value = 0
  try {
    const res = await api.post(`/conversations/${convId.value}/upload`, form, {

      onUploadProgress: (e) => {
        uploadProgress.value = Math.round((e.loaded / e.total) * 100)
      },
    })
    chatStore.uploadFile(convId.value, res.data.message)
  } catch (err) {
    console.error("Erreur lors de l'envoi du fichier :", err)
  } finally {
    uploadProgress.value = 0
  }
}

// ── Sélection du fichier (Modifiée pour lancer l'upload) ────────────────
async function onFileSelect(e) {
  console.log("Fichier sélectionné via le menu", e);
  
  const file = e.target.files[0]
  if (!file) return
  
  selectedFile.value   = file
  showAttachMenu.value = false // Ferme le menu proprement
  
  // Gestion de l'aperçu local temporaire (si vous affichez un loader à l'écran)
  if (file.type.startsWith('image/')) {
    const reader = new FileReader()
    reader.onload = (ev) => { previewUrl.value = ev.target.result }
    reader.readAsDataURL(file)
  } else {
    previewUrl.value = null
  }

  // ACTION CRUCIALE : On lance l'envoi immédiat au serveur Laravel !
  await uploadFile(file)
  
  // Optionnel : Réinitialiser l'input HTML pour permettre de sélectionner 
  // le même fichier deux fois d'affilée si nécessaire
  e.target.value = ''
}

// Vous pouvez nettoyer votre code en supprimant la fonction handleFileUpload(e) 
// car elle n'est plus nécessaire avec cette structure.

// function onFileSelect(e) {
//   console.log(e);
  
//   const file = e.target.files[0]
//   if (!file) return
//   selectedFile.value   = file
//   showAttachMenu.value = false
//   if (file.type.startsWith('image/')) {
//     const reader = new FileReader()
//     reader.onload = (ev) => { previewUrl.value = ev.target.result }
//     reader.readAsDataURL(file)
//   } else {
//     previewUrl.value = null
//   }
// }

// Garde la compatibilité avec la ref fileInput existante
function handleFileUpload(e) {
  console.log(e);
  
  console.log(onFileSelect(e))
   onFileSelect(e) 
  }

function clearFile() {
  selectedFile.value = null
  previewUrl.value   = null
}

// ── Modifier un message ─────────────────────────────────
function startEdit(msg) {
  editingMsgId.value = msg.id
  editText.value     = msg.message
  nextTick(() => editInputRef.value?.focus())
}

function cancelEdit() {
  editingMsgId.value = null
  editText.value     = ''
}

async function saveEdit(msg) {
  if (!editText.value.trim()) return
  try {
    await api.put(`/conversations/${convId.value}/messages/${msg.id}`, {
      message: editText.value.trim(),
    })
    // Mise à jour locale
    const msgs = chatStore.messages[convId.value] ?? []
    const found = msgs.find(m => m.id === msg.id)
    if (found) {
      found.message    = editText.value.trim()
      found.updated_at = new Date().toISOString()
    }
  } catch (e) {
    console.error('Erreur modification', e)
  } finally {
    cancelEdit()
  }
}

// ── Supprimer un message ────────────────────────────────
async function confirmDelete(msg) {
  // if (!confirm('Supprimer ce message ?')) return
  try {
    await api.delete(`/conversations/${convId.value}/messages/${msg.id}`)
    // Mise à jour locale
    const msgs = chatStore.messages[convId.value] ?? []
    const found = msgs.find(m => m.id === msg.id)
    if (found) {
      found.is_deleted = true
      found.message    = null
      found.file_url   = null
    }
  } catch (e) {
    console.error('Erreur suppression', e)
  }
}

// ── Réactions ───────────────────────────────────────────
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
        user_id:  auth.user.id,
        username: auth.user.username,
        reaction: emoji,
      })
    }
  }
  activeEmojiMsgId.value = null
}

function getGroupedReactions(reactions) {
  if (!reactions) return []
  const groups = {}
  reactions.forEach(r => {
    if (!groups[r.reaction]) {
      groups[r.reaction] = { reaction: r.reaction, count: 0, usernames: [] }
    }
    groups[r.reaction].count++
    groups[r.reaction].usernames.push(
      r.user_id === auth.user?.id ? 'Vous' : (r.username ?? '...')
    )
  })
  return Object.values(groups)
}

// ── Reply ───────────────────────────────────────────────
function setReply(msg) {
  replyTo.value = msg
  inputField.value?.focus()
}

// ── Typing ──────────────────────────────────────────────
function onTyping() {
  chatStore.sendTyping(convId.value, true)
  clearTimeout(typingTimer)
  typingTimer = setTimeout(() =>
    chatStore.sendTyping(convId.value, false), 200
  )
}

// ── Audio record ────────────────────────────────────────
async function startRecording() {
  try {
    const stream  = await navigator.mediaDevices.getUserMedia({ audio: true })
    mediaRecorder = new MediaRecorder(stream)
    audioChunks   = []
    isRecording.value = true
    mediaRecorder.ondataavailable = (e) => audioChunks.push(e.data)
    mediaRecorder.start()
  } catch {
    alert('Microphone non accessible.')
  }
}

async function stopRecording() {
  if (!mediaRecorder) return
  isRecording.value = false
  mediaRecorder.onstop = async () => {
    const blob = new Blob(audioChunks, { type: 'audio/webm' })
    const file = new File([blob], `audio-${Date.now()}.webm`, { type: 'audio/webm' })
    await uploadFile(file)
  }
  mediaRecorder.stop()
  mediaRecorder.stream.getTracks().forEach(t => t.stop())
}

// ── Accepter / Bloquer ──────────────────────────────────
async function acceptConversation() {
  await chatStore.updateStatus(convId.value, 'accepted')
  await chatStore.fetchMessages(convId.value)
  await chatStore.fetchConversations()
}
async function blockConversation() {
  await chatStore.updateStatus(convId.value, 'blocked')
}

// ── Scroll ──────────────────────────────────────────────
function scrollToBottom() {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

// ── Helpers ─────────────────────────────────────────────
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
  const yesterday = new Date(now)
  yesterday.setDate(now.getDate() - 1)
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
.gc-back-btn {
  background: none; border: none; color: #c4b5fd;
  cursor: pointer; padding: 4px; display: none;
}
.gc-chat-av {
  width: 40px; height: 40px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 13px; font-weight: 600; color: #fff;
  flex-shrink: 0; position: relative;
}
.gc-online-dot {
  position: absolute; bottom: 1px; right: 1px;
  width: 10px; height: 10px; background: #22c55e;
  border-radius: 50%; border: 2px solid #fff;
}
.gc-chat-info { flex: 1; }
.gc-chat-name { font-size: 15px; font-weight: 600; color: #1a1a2e; }
.gc-chat-status { font-size: 12px; }
.gc-chat-status.online  { color: #22c55e; }
.gc-chat-status.offline { color: #9ca3af; }
.gc-chat-actions { display: flex; gap: 8px; }
.gc-action-btn {
  width: 36px; height: 36px; border-radius: 10px;
  border: none; background: #fdf4ff; color: #c4b5fd;
  cursor: pointer; display: flex; align-items: center;
  justify-content: center; transition: all .15s;
}
.gc-action-btn:hover { background: #f0d6f5; color: #a855f7; }
.gc-action-btn i { font-size: 18px; }

/* ── Messages ─────────────────────────────────────────── */
.gc-messages {
  flex: 1; overflow-y: auto;
  padding: 16px 20px;
  display: flex; flex-direction: column; gap: 4px;
}
.gc-messages::-webkit-scrollbar { width: 3px; }
.gc-messages::-webkit-scrollbar-thumb { background: #f0d6f5; border-radius: 99px; }

.gc-date-sep {
  text-align: center; font-size: 11px; color: #9ca3af;
  background: #fce7f3; padding: 3px 14px; border-radius: 99px;
  margin: 10px auto; width: fit-content;
}

.gc-msg-row {
  display: flex; align-items: flex-end;
  gap: 8px; margin-bottom: 4px; position: relative;
}
.gc-msg-row.me { flex-direction: row-reverse; }

.gc-msg-av {
  width: 30px; height: 30px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 10px; font-weight: 600; color: #fff; flex-shrink: 0;
}

.gc-bubble-wrap {
  display: flex; flex-direction: column;
  gap: 2px; max-width: 65%; position: relative;
}

/* ── Actions flottantes ───────────────────────────────── */
.gc-msg-actions {
  position: absolute;
  top: -38px;
  left: 0;
  display: flex;
  gap: 4px;
  background: #fff;
  border: .5px solid #f0d6f5;
  border-radius: 12px;
  padding: 4px 6px;
  box-shadow: 0 4px 16px rgba(168,85,247,.12);
  z-index: 10;
}
.gc-msg-actions.me { left: auto; right: 0; }

.gc-msg-actions button {
  width: 28px; height: 28px; border-radius: 8px;
  border: none; background: none; cursor: pointer;
  color: #9ca3af; display: flex; align-items: center;
  justify-content: center; transition: all .15s;
}
.gc-msg-actions button:hover { background: #fdf4ff; color: #a855f7; }
.gc-msg-actions button.gc-action-danger:hover { background: #fef2f2; color: #dc2626; }
.gc-msg-actions button i { font-size: 15px; }

/* ── Emoji picker ─────────────────────────────────────── */
.gc-emoji-picker {
  position: absolute;
  bottom: calc(100% + 8px);
  left: 0;
  background: #fff;
  border: .5px solid #f0d6f5;
  border-radius: 24px;
  padding: 10px 14px;
  display: flex;
  gap: 10px;
  box-shadow: 0 4px 20px rgba(168,85,247,.12);
  z-index: 20;
}
.gc-emoji-picker.me { left: auto; right: 0; }
.gc-emoji-picker span {
  cursor: pointer; font-size: 20px;
  transition: transform .1s;
}
.gc-emoji-picker span:hover { transform: scale(1.3); }

/* ── Bulle ────────────────────────────────────────────── */
.gc-bubble {
  padding: 10px 14px; border-radius: 18px;
  font-size: 13px; line-height: 1.5; word-break: break-word;
}
.gc-bubble.them {
  background: #fff; color: #1a1a2e;
  border-bottom-left-radius: 4px;
  box-shadow: 0 1px 4px rgba(168,85,247,.08);
}
.gc-bubble.me {
  background: linear-gradient(135deg, #f472b6, #a855f7);
  color: #fff; border-bottom-right-radius: 4px;
}

.gc-deleted {
  display: flex; align-items: center; gap: 6px;
  font-style: italic; opacity: .6; font-size: 12px;
}
.gc-edited { font-size: 10px; opacity: .6; margin-left: 4px; }
.optimistic { opacity: .65; }

/* Répondre à */
.gc-reply-preview {
  display: flex; gap: 8px; align-items: flex-start;
  background: rgba(0,0,0,.06); border-radius: 8px;
  padding: 6px 8px; margin-bottom: 6px;
}
.gc-reply-bar {
  width: 3px; border-radius: 99px;
  background: rgba(255,255,255,.6); flex-shrink: 0; align-self: stretch;
}
.gc-bubble.them .gc-reply-bar { background: #a855f7; }
.gc-reply-preview p { font-size: 12px; opacity: .8; }

/* Édition */
.gc-edit-area { display: flex; flex-direction: column; gap: 6px; }
.gc-edit-area textarea {
  background: rgba(255,255,255,.2); border: 1px solid rgba(255,255,255,.4);
  border-radius: 8px; padding: 6px 10px; font-size: 13px;
  color: inherit; font-family: inherit; resize: none; outline: none; width: 100%;
}
.gc-edit-btns { display: flex; gap: 6px; justify-content: flex-end; }
.gc-edit-cancel, .gc-edit-save {
  font-size: 12px; font-weight: 500; padding: 4px 10px;
  border-radius: 8px; border: none; cursor: pointer;
}
.gc-edit-cancel { background: rgba(255,255,255,.2); color: inherit; }
.gc-edit-save   { background: #fff; color: #a855f7; }

/* Médias */
.gc-media-wrap { border-radius: 12px; overflow: hidden; }
.gc-media-img {
  max-width: 220px; max-height: 280px; display: block;
  cursor: zoom-in; border-radius: 12px; transition: opacity .15s;
}
.gc-media-img:hover { opacity: .9; }
.gc-media-video { max-width: 220px; border-radius: 12px; }
.gc-audio-wrap  { display: flex; align-items: center; gap: 8px; }
.gc-audio-icon  { font-size: 18px; opacity: .8; }
.gc-media-audio { width: 180px; height: 32px; }

.gc-file-bubble {
  display: flex; align-items: center; gap: 10px;
  text-decoration: none; color: inherit;
  background: rgba(255,255,255,.15); border-radius: 10px;
  padding: 8px 10px; border: .5px solid rgba(255,255,255,.2); transition: background .15s;
}
.gc-file-bubble:hover { background: rgba(255,255,255,.25); }
.gc-file-icon {
  width: 36px; height: 36px; border-radius: 10px;
  background: rgba(255,255,255,.2);
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.gc-file-icon i { font-size: 18px; }
.gc-file-info   { flex: 1; min-width: 0; }
.gc-file-name {
  font-size: 12px; font-weight: 500;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;
}
.gc-file-dl { font-size: 11px; opacity: .7; }

.gc-bubble-meta {
  font-size: 10px; margin-top: 4px;
  display: flex; align-items: center;
  gap: 3px; justify-content: flex-end; opacity: .7;
}
.gc-bubble.them .gc-bubble-meta { justify-content: flex-start; }
.gc-seen { color: #fff; opacity: 1 !important; }

/* Réactions */
.gc-message-reactions {
  display: flex; flex-wrap: wrap; gap: 4px; margin-top: 4px;
}
.gc-reaction-item {
  background: #fff; border: 1px solid #f0d6f5; border-radius: 12px;
  padding: 2px 8px; font-size: 14px;
  display: flex; align-items: center; gap: 4px;
  box-shadow: 0 1px 3px rgba(0,0,0,.05); cursor: pointer;
  transition: border-color .15s;
}
.gc-reaction-item:hover { border-color: #a855f7; }
.gc-reaction-item small { color: #6b7280; font-weight: 600; font-size: 11px; }

/* Typing */
.gc-typing {
  display: flex; gap: 4px; align-items: center;
  padding: 12px 16px; background: #fff;
  border-radius: 18px; border-bottom-left-radius: 4px;
  box-shadow: 0 1px 4px rgba(168,85,247,.08);
}
.gc-typing span {
  width: 7px; height: 7px; border-radius: 50%;
  background: #f9a8d4; animation: bounce .9s infinite;
}
.gc-typing span:nth-child(2) { animation-delay: .15s; }
.gc-typing span:nth-child(3) { animation-delay: .3s; }
@keyframes bounce {
  0%,60%,100% { transform: translateY(0); }
  30%          { transform: translateY(-5px); }
}

/* Pending */
.gc-pending-banner {
  background: #fff; border-top: .5px solid #f0d6f5;
  padding: 16px 20px; display: flex;
  align-items: center; justify-content: space-between; flex-shrink: 0;
}
.gc-pending-banner p { font-size: 14px; color: #374151; }
.gc-pending-actions  { display: flex; gap: 10px; }
.gc-btn-accept {
  background: linear-gradient(135deg, #f472b6, #a855f7);
  color: #fff; border: none; border-radius: 10px;
  padding: 8px 18px; font-size: 13px; font-weight: 600; cursor: pointer;
}
.gc-btn-block {
  background: #fef2f2; color: #dc2626;
  border: 1px solid #fecaca; border-radius: 10px;
  padding: 8px 18px; font-size: 13px; font-weight: 600; cursor: pointer;
}

/* ── Preview fichier ──────────────────────────────────── */
.gc-file-preview {
  padding: 10px 16px; border-bottom: .5px solid #f0d6f5; background: #fff;
}
.gc-file-preview-inner {
  display: flex; align-items: center; gap: 12px;
  background: #fdf4ff; border-radius: 12px;
  padding: 10px 12px; position: relative;
}
.gc-preview-img { width: 60px; height: 60px; border-radius: 8px; object-fit: cover; flex-shrink: 0; }
.gc-preview-file { display: flex; align-items: center; gap: 10px; }
.gc-preview-file i { font-size: 28px; color: #c4b5fd; }
.gc-preview-name { font-size: 13px; font-weight: 500; color: #1a1a2e; }
.gc-preview-size { font-size: 11px; color: #9ca3af; }
.gc-preview-remove {
  position: absolute; top: 6px; right: 6px;
  width: 22px; height: 22px; border-radius: 50%;
  background: #fff; border: .5px solid #f0d6f5;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; color: #9ca3af; transition: all .15s;
}
.gc-preview-remove:hover { color: #dc2626; border-color: #dc2626; }
.gc-preview-remove i { font-size: 12px; }
.gc-progress-wrap { display: flex; align-items: center; gap: 8px; margin-top: 8px; }
.gc-progress-bar {
  flex: 1; height: 4px;
  background: linear-gradient(90deg, #f472b6, #a855f7);
  border-radius: 99px; transition: width .2s;
}
.gc-progress-wrap span { font-size: 11px; color: #a855f7; font-weight: 500; }

/* ── Reply banner ─────────────────────────────────────── */
.gc-reply-banner {
  display: flex; align-items: center; justify-content: space-between;
  padding: 8px 16px; background: #fdf4ff; border-bottom: .5px solid #f0d6f5;
}
.gc-reply-content { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #a855f7; }
.gc-reply-content i { font-size: 16px; }
.gc-reply-banner button {
  background: none; border: none; cursor: pointer; color: #9ca3af; font-size: 16px; padding: 4px;
}
.gc-reply-banner button:hover { color: #dc2626; }

/* ── Input bar ────────────────────────────────────────── */
.gc-input-bar {
  background: #fff; border-top: .5px solid #f0d6f5;
  padding: 10px 16px; display: flex;
  align-items: center; gap: 10px; flex-shrink: 0;
}
.gc-attach-wrap { position: relative; }
.gc-attach-menu {
  position: absolute; bottom: 44px; left: 0;
  background: #fff; border: .5px solid #f0d6f5;
  border-radius: 16px; padding: 8px;
  display: flex; flex-direction: column; gap: 4px;
  box-shadow: 0 8px 24px rgba(168,85,247,.12);
  z-index: 10; min-width: 160px;
}
.gc-attach-item {
  display: flex; align-items: center; gap: 10px;
  padding: 8px 10px; border-radius: 10px;
  cursor: pointer; transition: background .15s;
  font-size: 13px; color: #374151;
}
.gc-attach-item:hover { background: #fdf4ff; }
.gc-attach-icon {
  width: 34px; height: 34px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.gc-attach-icon i { font-size: 17px; color: #fff; }
.gc-icon-img   { background: linear-gradient(135deg, #f472b6, #a855f7); }
.gc-icon-audio { background: linear-gradient(135deg, #34d399, #059669); }
.gc-icon-file  { background: linear-gradient(135deg, #60a5fa, #3b82f6); }

.gc-input-icon {
  background: none; border: none; color: #c4b5fd;
  cursor: pointer; display: flex; align-items: center;
  padding: 4px; transition: color .15s; flex-shrink: 0;
}
.gc-input-icon:hover { color: #a855f7; }
.gc-input-icon i { font-size: 20px; }
.gc-input-icon.is-recording i { color: #dc2626; animation: gc-pulse 1s infinite; }

.gc-input-field {
  flex: 1; border: .5px solid #f0d6f5; border-radius: 24px;
  padding: 10px 16px; font-size: 13px; color: #1a1a2e;
  background: #fdf4ff; outline: none; transition: border-color .15s;
}
.gc-input-field:focus { border-color: #c4b5fd; }
.gc-input-field::placeholder { color: #c4b5fd; }

.gc-send-btn {
  width: 40px; height: 40px; border-radius: 50%;
  background: linear-gradient(135deg, #f472b6, #a855f7);
  border: none; display: flex; align-items: center;
  justify-content: center; cursor: pointer; flex-shrink: 0;
  transition: opacity .15s, transform .15s;
}
.gc-send-btn:hover:not(:disabled)  { opacity: .9; }
.gc-send-btn:active:not(:disabled) { transform: scale(0.95); }
.gc-send-btn:disabled { opacity: .5; cursor: not-allowed; }
.gc-send-btn i { font-size: 17px; color: #fff; }

/* Bloqué */
.gc-blocked-bar {
  background: #fef2f2; border-top: .5px solid #fecaca;
  padding: 14px 20px; display: flex; align-items: center;
  justify-content: center; gap: 8px; font-size: 13px;
  color: #dc2626; flex-shrink: 0;
}
.gc-blocked-bar i { font-size: 16px; }

/* Lightbox */
.gc-lightbox {
  position: fixed; inset: 0; z-index: 1000;
  background: rgba(0,0,0,.85);
  display: flex; align-items: center; justify-content: center; cursor: zoom-out;
}
.gc-lightbox img {
  max-width: 90vw; max-height: 90vh;
  border-radius: 12px; object-fit: contain;
}
.gc-lightbox-close {
  position: absolute; top: 20px; right: 20px;
  width: 40px; height: 40px; border-radius: 50%;
  background: rgba(255,255,255,.15); border: none;
  color: #fff; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
}
.gc-lightbox-close i { font-size: 20px; }

/* Transitions */
.gc-slide-enter-active, .gc-slide-leave-active { transition: all .2s ease; }
.gc-slide-enter-from,   .gc-slide-leave-to     { opacity: 0; transform: translateY(8px); }
.gc-fade-enter-active,  .gc-fade-leave-active  { transition: opacity .15s; }
.gc-fade-enter-from,    .gc-fade-leave-to      { opacity: 0; }

@keyframes gc-pulse { 0%,100% { opacity: 1; } 50% { opacity: .3; } }

@media (max-width: 640px) {
  .gc-back-btn { display: flex; }
}
</style>