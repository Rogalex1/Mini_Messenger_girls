<template>
  <div class="gc-msg-row" :class="{ me: isMine }" @mouseenter="showActions = true" @mouseleave="showActions = false">

    <!-- Avatar -->
    <div v-if="!isMine" class="gc-msg-av" :style="avatarGradient(msg.sender_id)">
      {{ senderInitials }}
    </div>

    <div class="gc-bubble-wrap">

      <!-- Actions (modifier / supprimer) -->
      <Transition name="fade">
        <div v-if="showActions && !msg.is_deleted" class="gc-msg-actions" :class="{ me: isMine }">
          <button v-if="isMine && msg.type === 'text'" @click="startEdit" title="Modifier">
            <i class="ti ti-pencil"></i>
          </button>
          <button @click="confirmDelete" title="Supprimer">
            <i class="ti ti-trash"></i>
          </button>
          <button @click="$emit('reply', msg)" title="Répondre">
            <i class="ti ti-arrow-back-up"></i>
          </button>
        </div>
      </Transition>

      <!-- Bulle -->
      <div class="gc-bubble" :class="isMine ? 'me' : 'them'">

        <!-- Message supprimé -->
        <p v-if="msg.is_deleted" class="gc-deleted">
          <i class="ti ti-ban"></i> Message supprimé
        </p>

        <!-- Vue unique -->
        <ViewOnceMessage
          v-else-if="msg.is_single_view"
          :message-id="msg.id"
          :conversation-id="msg.conversation_id"
          :file-url="msg.file_url"
          :type="msg.type"
          :sender-id="msg.sender_id"
          :is-deleted="msg.is_deleted"
        />

        <!-- Texte normal -->
        <template v-else>

          <!-- Réponse à -->
          <div v-if="msg.reply_to" class="gc-reply-preview">
            <span class="gc-reply-bar"></span>
            <p>{{ msg.reply_to.message || '📎 Fichier' }}</p>
          </div>

          <!-- Édition en cours -->
          <div v-if="isEditing" class="gc-edit-area">
            <textarea
              v-model="editText"
              @keyup.enter.exact="saveEdit"
              @keyup.escape="cancelEdit"
              rows="1"
              ref="editInput"
            ></textarea>
            <div class="gc-edit-actions">
              <button @click="cancelEdit" class="gc-edit-cancel">Annuler</button>
              <button @click="saveEdit"   class="gc-edit-save">Enregistrer</button>
            </div>
          </div>

          <!-- Texte -->
          <p v-else-if="msg.type === 'text'">
            {{ msg.message }}
            <span v-if="msg.updated_at !== msg.created_at" class="gc-edited">(modifié)</span>
          </p>

          <!-- Image -->
          <div v-else-if="msg.type === 'image'" class="gc-media-wrap">
            <img
              :src="msg.file_url"
              class="gc-media-img"
              @click="openLightbox(msg.file_url)"
            />
          </div>

          <!-- Vidéo -->
          <video
            v-else-if="msg.type === 'video'"
            :src="msg.file_url"
            controls
            class="gc-media-video"
          ></video>

          <!-- Audio -->
          <div v-else-if="msg.type === 'audio'" class="gc-audio-wrap">
            <i class="ti ti-microphone gc-audio-icon"></i>
            <audio :src="msg.file_url" controls class="gc-media-audio"></audio>
          </div>

          <!-- Fichier -->
          
           <a v-else-if="msg.type === 'file'"
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

        <!-- Meta : heure + vu -->
        <div class="gc-bubble-meta" v-if="!msg.is_deleted">
          {{ formatTime(msg.created_at) }}
          <i
            v-if="isMine"
            class="ti"
            :class="msg.is_seen ? 'ti-checks gc-seen' : 'ti-check'"
          ></i>
        </div>
      </div>

      <!-- Réactions -->
      <MessageReaction
        v-if="!msg.is_deleted"
        :message-id="msg.id"
        :reactions="msg.reactions ?? []"
      />

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
import { ref, computed, nextTick } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useChatStore } from '@/stores/chat'
import MessageReaction from './MessageReaction.vue'
import ViewOnceMessage from './ViewOnceMessage.vue'

const props = defineProps({
    msg: { type: Object, required: true },
})
const emit = defineEmits(['reply'])

const auth      = useAuthStore()
const chatStore = useChatStore()

const showActions = ref(false)
const isEditing   = ref(false)
const editText    = ref('')
const editInput   = ref(null)
const lightboxUrl = ref(null)

const isMine = computed(() => props.msg.sender_id === auth.user?.id)

const senderInitials = computed(() => {
    const s = props.msg.sender
    if (!s) return '?'
    const f = s.profile?.first_name?.[0] ?? ''
    const l = s.profile?.last_name?.[0]  ?? ''
    return (f + l).toUpperCase() || s.username?.[0]?.toUpperCase() || '?'
})

const gradients = [
    '135deg, #f472b6, #a855f7',
    '135deg, #f9a8d4, #e879f9',
    '135deg, #c084fc, #818cf8',
    '135deg, #fda4af, #fb7185',
]
function avatarGradient(id = 0) {
    return `background: linear-gradient(${gradients[id % gradients.length]})`
}

function formatTime(dateStr) {
    if (!dateStr) return ''
    return new Date(dateStr).toLocaleTimeString('fr', { hour: '2-digit', minute: '2-digit' })
}

// ── Modifier ────────────────────────────────────────────
function startEdit() {
    isEditing.value = true
    editText.value  = props.msg.message
    nextTick(() => editInput.value?.focus())
}

function cancelEdit() {
    isEditing.value = false
    editText.value  = ''
}

async function saveEdit() {
    if (!editText.value.trim()) return
    await chatStore.editMessage(
        props.msg.conversation_id,
        props.msg.id,
        editText.value.trim()
    )
    isEditing.value = false
}

// ── Supprimer ───────────────────────────────────────────
async function confirmDelete() {
    if (!confirm('Supprimer ce message ?')) return
    await chatStore.deleteMessage(props.msg.conversation_id, props.msg.id)
}

// ── Lightbox ────────────────────────────────────────────
function openLightbox(url) {
    lightboxUrl.value = url
}
</script>

<style scoped>
.gc-msg-row {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    margin-bottom: 6px;
    position: relative;
}
.gc-msg-row.me { flex-direction: row-reverse; }

.gc-msg-av {
    width: 30px; height: 30px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 10px; font-weight: 600; color: #fff; flex-shrink: 0;
}

.gc-bubble-wrap {
    display: flex; flex-direction: column;
    max-width: 65%; position: relative;
}

/* ── Actions ──────────────────────────────────────────── */
.gc-msg-actions {
    position: absolute;
    top: -36px;
    left: 0;
    display: flex;
    gap: 4px;
    background: #fff;
    border: .5px solid #f0d6f5;
    border-radius: 10px;
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
.gc-msg-actions button i { font-size: 15px; }

/* ── Bulle ────────────────────────────────────────────── */
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

.gc-deleted {
    display: flex; align-items: center; gap: 6px;
    font-style: italic; opacity: .6; font-size: 12px;
}

.gc-edited { font-size: 10px; opacity: .6; margin-left: 4px; }

/* ── Réponse ──────────────────────────────────────────── */
.gc-reply-preview {
    display: flex; gap: 8px; align-items: flex-start;
    background: rgba(0,0,0,.06);
    border-radius: 8px; padding: 6px 8px; margin-bottom: 6px;
}
.gc-reply-bar {
    width: 3px; border-radius: 99px; background: #a855f7;
    flex-shrink: 0; align-self: stretch;
}
.gc-reply-preview p { font-size: 12px; opacity: .8; }

/* ── Édition ──────────────────────────────────────────── */
.gc-edit-area { display: flex; flex-direction: column; gap: 6px; }
.gc-edit-area textarea {
    background: rgba(255,255,255,.2);
    border: 1px solid rgba(255,255,255,.4);
    border-radius: 8px; padding: 6px 10px;
    font-size: 13px; color: inherit;
    font-family: inherit; resize: none; outline: none; width: 100%;
}
.gc-edit-actions { display: flex; gap: 6px; justify-content: flex-end; }
.gc-edit-cancel, .gc-edit-save {
    font-size: 12px; font-weight: 500; padding: 4px 10px;
    border-radius: 8px; border: none; cursor: pointer;
}
.gc-edit-cancel { background: rgba(255,255,255,.2); color: inherit; }
.gc-edit-save   { background: #fff; color: #a855f7; }

/* ── Médias ───────────────────────────────────────────── */
.gc-media-wrap { border-radius: 12px; overflow: hidden; }
.gc-media-img {
    max-width: 220px; max-height: 280px;
    display: block; cursor: zoom-in;
    border-radius: 12px; transition: opacity .15s;
}
.gc-media-img:hover { opacity: .9; }
.gc-media-video { max-width: 220px; border-radius: 12px; }
.gc-audio-wrap { display: flex; align-items: center; gap: 8px; }
.gc-audio-icon { font-size: 18px; opacity: .8; }
.gc-media-audio { width: 180px; height: 32px; }

/* Fichier */
.gc-file-bubble {
    display: flex; align-items: center; gap: 10px;
    text-decoration: none; color: inherit;
    background: rgba(255,255,255,.15);
    border-radius: 10px; padding: 8px 10px;
    border: .5px solid rgba(255,255,255,.2);
    transition: background .15s;
}
.gc-file-bubble:hover { background: rgba(255,255,255,.25); }
.gc-file-icon {
    width: 36px; height: 36px; border-radius: 10px;
    background: rgba(255,255,255,.2);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.gc-file-icon i { font-size: 18px; }
.gc-file-info { flex: 1; min-width: 0; }
.gc-file-name {
    font-size: 12px; font-weight: 500;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    display: block;
}
.gc-file-dl { font-size: 11px; opacity: .7; }

/* Meta */
.gc-bubble-meta {
    font-size: 10px; margin-top: 4px;
    display: flex; align-items: center;
    gap: 3px; justify-content: flex-end; opacity: .7;
}
.gc-bubble.them .gc-bubble-meta { justify-content: flex-start; }
.gc-seen { color: #fff; opacity: 1 !important; }

/* ── Lightbox ─────────────────────────────────────────── */
.gc-lightbox {
    position: fixed; inset: 0; z-index: 1000;
    background: rgba(0,0,0,.85);
    display: flex; align-items: center; justify-content: center;
    cursor: zoom-out;
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

/* ── Transitions ──────────────────────────────────────── */
.fade-enter-active, .fade-leave-active { transition: opacity .15s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>