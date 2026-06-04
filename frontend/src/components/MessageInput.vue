<template>
  <div class="gc-input-section">

    <!-- Preview fichier sélectionné -->
    <Transition name="slide">
      <div v-if="selectedFile" class="gc-file-preview">
        <div class="gc-file-preview-inner">

          <!-- Image preview -->
          <img
            v-if="previewUrl && isImage"
            :src="previewUrl"
            class="gc-preview-img"
          />

          <!-- Autres fichiers -->
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

        <!-- Barre de progression -->
        <div v-if="chatStore.uploadProgress > 0" class="gc-progress-wrap">
          <div class="gc-progress-bar" :style="{ width: chatStore.uploadProgress + '%' }"></div>
          <span>{{ chatStore.uploadProgress }}%</span>
        </div>
      </div>
    </Transition>

    <!-- Répondre à un message -->
    <Transition name="slide">
      <div v-if="replyTo" class="gc-reply-bar">
        <div class="gc-reply-content">
          <i class="ti ti-arrow-back-up"></i>
          <span>{{ replyTo.message || '📎 Fichier' }}</span>
        </div>
        <button @click="$emit('cancel-reply')">
          <i class="ti ti-x"></i>
        </button>
      </div>
    </Transition>

    <!-- Barre principale -->
    <div class="gc-input-bar">

      <!-- Bouton pièce jointe -->
      <div class="gc-attach-wrap">
        <button class="gc-input-icon" @click="showAttachMenu = !showAttachMenu">
          <i class="ti ti-paperclip"></i>
        </button>

        <!-- Menu pièces jointes -->
        <Transition name="fade">
          <div v-if="showAttachMenu" class="gc-attach-menu">
            <label class="gc-attach-item">
              <div class="gc-attach-icon gc-icon-img">
                <i class="ti ti-photo"></i>
              </div>
              <span>Image / Vidéo</span>
              <input type="file" hidden accept="image/*,video/*" @change="onFileSelect" />
            </label>
            <label class="gc-attach-item">
              <div class="gc-attach-icon gc-icon-audio">
                <i class="ti ti-music"></i>
              </div>
              <span>Audio</span>
              <input type="file" hidden accept="audio/*" @change="onFileSelect" />
            </label>
            <label class="gc-attach-item">
              <div class="gc-attach-icon gc-icon-file">
                <i class="ti ti-file"></i>
              </div>
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
        @keyup.enter.exact="handleSend"
        @input="onTyping"
        ref="inputField"
      />

      <!-- Emoji -->
      <button class="gc-input-icon">
        <i class="ti ti-mood-smile"></i>
      </button>

      <!-- Audio record -->
      <button
        class="gc-input-icon"
        :class="{ recording: isRecording }"
        @mousedown="startRecording"
        @mouseup="stopRecording"
        @touchstart.prevent="startRecording"
        @touchend="stopRecording"
      >
        <i class="ti ti-microphone"></i>
      </button>

      <!-- Envoi -->
      <button
        class="gc-send-btn"
        :disabled="!canSend"
        @click="handleSend"
      >
        <i class="ti ti-send"></i>
      </button>
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useChatStore } from '@/stores/chat'
import api from '@/api/axios'

const props = defineProps({
    conversationId: { type: Number, required: true },
    replyTo:        { type: Object, default: null },
})
const emit = defineEmits(['cancel-reply'])

const chatStore      = useChatStore()
const messageText    = ref('')
const selectedFile   = ref(null)
const previewUrl     = ref(null)
const showAttachMenu = ref(false)
const isRecording    = ref(false)
const inputField     = ref(null)
let   typingTimer    = null
let   mediaRecorder  = null
let   audioChunks    = []

const canSend = computed(() =>
    messageText.value.trim().length > 0 || selectedFile.value !== null
)

const isImage = computed(() =>
    selectedFile.value?.type.startsWith('image/')
)

const fileIcon = computed(() => {
    const type = selectedFile.value?.type ?? ''
    if (type.startsWith('video/')) return 'ti ti-video'
    if (type.startsWith('audio/')) return 'ti ti-music'
    return 'ti ti-file-text'
})

const fileSize = computed(() => {
    const size = selectedFile.value?.size ?? 0
    if (size < 1024)          return size + ' o'
    if (size < 1024 * 1024)   return (size / 1024).toFixed(1) + ' Ko'
    return (size / 1024 / 1024).toFixed(1) + ' Mo'
})

// ── Sélection fichier ───────────────────────────────────
function onFileSelect(e) {
    const file = e.target.files[0]
    if (!file) return
    selectedFile.value   = file
    showAttachMenu.value = false

    if (file.type.startsWith('image/')) {
        const reader = new FileReader()
        reader.onload = (ev) => { previewUrl.value = ev.target.result }
        reader.readAsDataURL(file)
    } else {
        previewUrl.value = null
    }
}

function clearFile() {
    selectedFile.value = null
    previewUrl.value   = null
}

// ── Envoi ───────────────────────────────────────────────
async function handleSend() {
    if (!canSend.value) return

    // Envoi d'un fichier
    if (selectedFile.value) {
        await chatStore.uploadFile(props.conversationId, selectedFile.value)
        clearFile()
        return
    }

    // Envoi d'un message texte
    await chatStore.sendMessage(props.conversationId, {
        message:     messageText.value.trim(),
        type:        'text',
        reply_to_id: props.replyTo?.id ?? null,
    })

    messageText.value = ''
    emit('cancel-reply')
    inputField.value?.focus()
}

// ── Typing indicator ────────────────────────────────────
function onTyping() {
    chatStore.sendTyping(props.conversationId, true)
    clearTimeout(typingTimer)
    typingTimer = setTimeout(() =>
        chatStore.sendTyping(props.conversationId, false), 2000
    )
}

// ── Enregistrement audio ────────────────────────────────
async function startRecording() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true })
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
        await chatStore.uploadFile(props.conversationId, file)
    }

    mediaRecorder.stop()
    mediaRecorder.stream.getTracks().forEach(t => t.stop())
}
</script>

<style scoped>
.gc-input-section {
    background: #fff;
    border-top: .5px solid #f0d6f5;
    flex-shrink: 0;
}

/* ── Preview fichier ──────────────────────────────────── */
.gc-file-preview {
    padding: 10px 16px;
    border-bottom: .5px solid #f0d6f5;
}

.gc-file-preview-inner {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #fdf4ff;
    border-radius: 12px;
    padding: 10px 12px;
    position: relative;
}

.gc-preview-img {
    width: 60px; height: 60px;
    border-radius: 8px; object-fit: cover; flex-shrink: 0;
}

.gc-preview-file {
    display: flex; align-items: center; gap: 10px;
}
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

/* Barre de progression */
.gc-progress-wrap {
    display: flex; align-items: center; gap: 8px; margin-top: 8px;
}
.gc-progress-bar {
    flex: 1; height: 4px;
    background: linear-gradient(90deg, #f472b6, #a855f7);
    border-radius: 99px; transition: width .2s;
}
.gc-progress-wrap span { font-size: 11px; color: #a855f7; font-weight: 500; }

/* ── Répondre ─────────────────────────────────────────── */
.gc-reply-bar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 8px 16px;
    background: #fdf4ff;
    border-bottom: .5px solid #f0d6f5;
}
.gc-reply-content {
    display: flex; align-items: center; gap: 8px;
    font-size: 13px; color: #a855f7;
}
.gc-reply-content i { font-size: 16px; }
.gc-reply-bar button {
    background: none; border: none; cursor: pointer;
    color: #9ca3af; font-size: 16px; padding: 4px;
}
.gc-reply-bar button:hover { color: #dc2626; }

/* ── Barre principale ─────────────────────────────────── */
.gc-input-bar {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 16px;
}

.gc-attach-wrap { position: relative; }

/* Menu pièces jointes */
.gc-attach-menu {
    position: absolute;
    bottom: 44px; left: 0;
    background: #fff;
    border: .5px solid #f0d6f5;
    border-radius: 16px;
    padding: 8px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    box-shadow: 0 8px 24px rgba(168,85,247,.12);
    z-index: 10;
    min-width: 160px;
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
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.gc-attach-icon i { font-size: 17px; color: #fff; }
.gc-icon-img   { background: linear-gradient(135deg, #f472b6, #a855f7); }
.gc-icon-audio { background: linear-gradient(135deg, #34d399, #059669); }
.gc-icon-file  { background: linear-gradient(135deg, #60a5fa, #3b82f6); }

/* Input */
.gc-input-icon {
    background: none; border: none; color: #c4b5fd;
    cursor: pointer; display: flex; align-items: center;
    padding: 4px; transition: color .15s; flex-shrink: 0;
}
.gc-input-icon:hover { color: #a855f7; }
.gc-input-icon i { font-size: 20px; }
.gc-input-icon.recording i { color: #dc2626; animation: pulse 1s infinite; }

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
.gc-send-btn:disabled { opacity: .4; cursor: not-allowed; }
.gc-send-btn i { font-size: 17px; color: #fff; }

/* Transitions */
.slide-enter-active, .slide-leave-active { transition: all .2s ease; }
.slide-enter-from, .slide-leave-to { opacity: 0; transform: translateY(10px); }
.fade-enter-active, .fade-leave-active { transition: opacity .15s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

@keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: .4; } }
</style>