<template>
  <div class="gc-reactions-bar">

    <!-- Réactions existantes -->
    <button
      v-for="(group, emoji) in groupedReactions"
      :key="emoji"
      class="gc-reaction-pill"
      :class="{ mine: group.includes(myUserId) }"
      @click="toggleReaction(emoji)"
    >
      {{ emoji }} {{ group.length }}
    </button>

    <!-- Bouton ajouter -->
    <button class="gc-reaction-add" @click="showPicker = !showPicker">
      <i class="ti ti-mood-smile"></i>
    </button>

    <!-- Picker simple -->
    <div v-if="showPicker" class="gc-emoji-picker">
      <button
        v-for="emoji in quickEmojis"
        :key="emoji"
        @click="pickEmoji(emoji)"
      >{{ emoji }}</button>
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/api/axios'

const props = defineProps({
  messageId: { type: Number, required: true },
  reactions: { type: Array, default: () => [] },
})

const auth       = useAuthStore()
const showPicker = ref(false)
const myUserId   = computed(() => auth.user?.id)

const quickEmojis = ['❤️', '😂', '😮', '😢', '😡', '👍', '👎', '🔥', '🎉', '✅']

// Grouper les réactions par emoji
const groupedReactions = computed(() => {
    return props.reactions.reduce((acc, r) => {
        if (!acc[r.reaction]) acc[r.reaction] = []
        acc[r.reaction].push(r.user_id)
        return acc
    }, {})
})

async function toggleReaction(emoji) {
    const alreadyReacted = groupedReactions.value[emoji]?.includes(myUserId.value)
    if (alreadyReacted) {
        await api.delete(`/messages/${props.messageId}/reactions`)
    } else {
        await api.post(`/messages/${props.messageId}/reactions`, { reaction: emoji })
    }
    showPicker.value = false
}

async function pickEmoji(emoji) {
    await api.post(`/messages/${props.messageId}/reactions`, { reaction: emoji })
    showPicker.value = false
}
</script>

<style scoped>
.gc-reactions-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin-top: 4px;
    position: relative;
}

.gc-reaction-pill {
    background: #fff;
    border: 1.5px solid #f0d6f5;
    border-radius: 99px;
    padding: 2px 8px;
    font-size: 13px;
    cursor: pointer;
    transition: all .15s;
    display: flex;
    align-items: center;
    gap: 3px;
}
.gc-reaction-pill:hover { border-color: #a855f7; }
.gc-reaction-pill.mine  {
    background: #fdf4ff;
    border-color: #a855f7;
}

.gc-reaction-add {
    background: #fff;
    border: 1.5px solid #f0d6f5;
    border-radius: 99px;
    width: 26px;
    height: 26px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #c4b5fd;
    transition: all .15s;
}
.gc-reaction-add:hover { border-color: #a855f7; color: #a855f7; }
.gc-reaction-add i { font-size: 14px; }

.gc-emoji-picker {
    position: absolute;
    bottom: 32px;
    left: 0;
    background: #fff;
    border: .5px solid #f0d6f5;
    border-radius: 14px;
    padding: 8px;
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    width: 200px;
    box-shadow: 0 4px 20px rgba(168,85,247,.12);
    z-index: 10;
}

.gc-emoji-picker button {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    border-radius: 8px;
    padding: 4px;
    transition: background .15s;
}
.gc-emoji-picker button:hover { background: #fdf4ff; }
</style>