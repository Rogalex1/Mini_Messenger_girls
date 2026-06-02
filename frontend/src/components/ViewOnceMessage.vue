<template>
  <div class="gc-view-once" @click="handleView">

    <!-- Pas encore vu -->
    <template v-if="!viewed">
      <div class="gc-view-once-btn" :class="isMine ? 'mine' : 'them'">
        <i class="ti ti-eye"></i>
        <span>{{ isMine ? 'Vue unique envoyée' : 'Appuyer pour voir' }}</span>
      </div>
    </template>

    <!-- En cours d'affichage -->
    <template v-else-if="content && !expired">
      <div class="gc-view-once-media">
        <img v-if="type === 'image'" :src="content" class="gc-vo-img" />
        <video v-else-if="type === 'video'" :src="content" autoplay class="gc-vo-video"></video>

        <!-- Timer visuel -->
        <div class="gc-vo-timer">
          <div class="gc-vo-timer-bar" :style="{ width: timerPercent + '%' }"></div>
        </div>
        <span class="gc-vo-countdown">{{ countdown }}s</span>
      </div>
    </template>

    <!-- Déjà vu -->
    <template v-else>
      <div class="gc-view-once-btn expired">
        <i class="ti ti-eye-off"></i>
        <span>{{ isMine ? 'Message vu' : 'Déjà vu' }}</span>
      </div>
    </template>

  </div>
</template>

<script setup>
import { ref, computed, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/api/axios'

const props = defineProps({
    messageId:      { type: Number, required: true },
    conversationId: { type: Number, required: true },
    fileUrl:        { type: String, default: null },
    type:           { type: String, default: 'image' },
    senderId:       { type: Number, required: true },
    isDeleted:      { type: Boolean, default: false },
})

const auth     = useAuthStore()
const viewed   = ref(props.isDeleted)
const expired  = ref(false)
const content  = ref(null)
const countdown = ref(10)    // 10 secondes pour voir
const timerPercent = ref(100)
let   timer    = null

const isMine = computed(() => props.senderId === auth.user?.id)

async function handleView() {
    if (isMine.value || viewed.value) return

    // Récupérer et afficher le contenu
    content.value = props.fileUrl
    viewed.value  = true

    // Notifier le backend
    await api.post(`/conversations/${props.conversationId}/messages/${props.messageId}/view`)

    // Démarrer le compte à rebours
    startTimer()
}

function startTimer() {
    let elapsed = 0
    const total = 10000  // 10 secondes
    const step  = 100    // toutes les 100ms

    timer = setInterval(() => {
        elapsed += step
        countdown.value    = Math.ceil((total - elapsed) / 1000)
        timerPercent.value = Math.max(0, 100 - (elapsed / total * 100))

        if (elapsed >= total) {
            clearInterval(timer)
            content.value = null
            expired.value = true
        }
    }, step)
}

onUnmounted(() => {
    if (timer) clearInterval(timer)
})
</script>

<style scoped>
.gc-view-once { cursor: pointer; }

.gc-view-once-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    border-radius: 14px;
    font-size: 13px;
    font-weight: 500;
}
.gc-view-once-btn.them {
    background: #fff;
    color: #a855f7;
    border: 1.5px solid #f0d6f5;
}
.gc-view-once-btn.mine {
    background: rgba(255,255,255,.2);
    color: #fff;
}
.gc-view-once-btn.expired {
    background: #f3f4f6;
    color: #9ca3af;
    cursor: default;
}
.gc-view-once-btn i { font-size: 18px; }

.gc-view-once-media {
    position: relative;
    border-radius: 14px;
    overflow: hidden;
}

.gc-vo-img {
    max-width: 220px;
    max-height: 280px;
    display: block;
    border-radius: 14px;
}
.gc-vo-video {
    max-width: 220px;
    border-radius: 14px;
}

.gc-vo-timer {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 3px;
    background: rgba(255,255,255,.3);
}
.gc-vo-timer-bar {
    height: 100%;
    background: #fff;
    transition: width .1s linear;
}

.gc-vo-countdown {
    position: absolute;
    top: 8px; right: 10px;
    font-size: 12px;
    font-weight: 600;
    color: #fff;
    background: rgba(0,0,0,.4);
    padding: 2px 7px;
    border-radius: 99px;
}
</style>