import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/api/axios'
import echo from '@/echo'

export const useChatStore = defineStore('chat', () => {
  const conversations = ref([])
  const messages      = ref({})   // { convId: [...] }
  const typingUsers   = ref({})   // { convId: userId }
  const onlineUsers   = ref([])

  // ─── Conversations ───────────────────────────
  async function fetchConversations() {
    const res = await api.get('/conversations')
    conversations.value = res.data.conversations
  }

  async function sendMessage(convId, data) {
    const res = await api.post(`/conversations/${convId}/messages`, data)
    addMessage(convId, res.data.message)
    updateLastMessage(convId, res.data.message)
  }

  async function sendFile(convId, file) {
    const form = new FormData()
    form.append('file', file)
    const res = await api.post(`/conversations/${convId}/upload`, form, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    addMessage(convId, res.data.message)
    updateLastMessage(convId, res.data.message)
  }

  async function fetchMessages(convId) {
    const res = await api.get(`/conversations/${convId}/messages`)
    messages.value[convId] = res.data.messages
  }

  async function sendTyping(convId, isTyping) {
    await api.post(`/conversations/${convId}/typing`, { is_typing: isTyping })
  }

  async function updateStatus(convId, status) {
    await api.patch(`/conversations/${convId}/status`, { status })
    const conv = conversations.value.find(c => c.id === convId)
    if (conv) conv.status = status
  }

  // ─── WebSocket : s'abonner à une conversation ─
  function subscribeToConversation(convId) {
    echo.private(`conversation.${convId}`)

      // Nouveau message reçu
      .listen('.message.sent', (data) => {
        addMessage(convId, data)
        updateLastMessage(convId, data)
      })

      // Indicateur "est en train d'écrire"
      .listen('.user.typing', (data) => {
        if (data.is_typing) {
          typingUsers.value[convId] = data.user_id
        } else {
          delete typingUsers.value[convId]
        }
      })


      .listen('.message.read', (data) => {
    // Mettre à jour is_seen des messages concernés
    const msgs = messages.value[data.conversation_id] ?? []
    msgs.forEach(msg => {
        if (data.message_ids.includes(msg.id)) {
            msg.is_seen  = true
            msg.seen_at  = data.read_at
        }
    })
    })

.listen('.message.reacted', (data) => {
    const msgs = messages.value[data.conversation_id] ?? []
    const msg  = msgs.find(m => m.id === data.message_id)
    if (!msg) return

    if (!msg.reactions) msg.reactions = []

    if (data.action === 'added') {
        const existing = msg.reactions.find(r => r.user_id === data.user_id)
        if (existing) {
            existing.reaction = data.reaction
        } else {
            msg.reactions.push({ user_id: data.user_id, reaction: data.reaction })
        }
    } else {
        msg.reactions = msg.reactions.filter(r => r.user_id !== data.user_id)
    }
})
    


  }

  function unsubscribeFromConversation(convId) {
    echo.leave(`conversation.${convId}`)
  }

  // ─── WebSocket : présence globale ─────────────
  function subscribeToOnlineUsers() {
    echo.join('online-users')

      .here((users) => {
        // Liste initiale des connectés
        onlineUsers.value = users
      })

      .joining((user) => {
        // Quelqu'un vient de se connecter
        if (!onlineUsers.value.find(u => u.id === user.id)) {
          onlineUsers.value.push(user)
        }
        updateUserOnlineStatus(user.id, true)
      })

      .leaving((user) => {
        // Quelqu'un vient de se déconnecter
        onlineUsers.value = onlineUsers.value.filter(u => u.id !== user.id)
        updateUserOnlineStatus(user.id, false)
      })

      .listen('.user.status', (data) => {
        updateUserOnlineStatus(data.user_id, data.is_online)
      })
  }

  // ─── Helpers internes ─────────────────────────
  function addMessage(convId, message) {
    if (!messages.value[convId]) messages.value[convId] = []
    const exists = messages.value[convId].find(m => m.id === message.id)
    if (!exists) messages.value[convId].push(message)
  }

  function updateLastMessage(convId, message) {
    const conv = conversations.value.find(c => c.id === convId)
    if (conv) {
      conv.last_message = message
      conv.updated_at   = message.created_at
      // Remonter la conversation en tête de liste
      conversations.value = [
        conv,
        ...conversations.value.filter(c => c.id !== convId),
      ]
    }
  }

  function updateUserOnlineStatus(userId, isOnline) {
    conversations.value.forEach(conv => {
      if (conv.other_user?.id === userId) {
        conv.other_user.is_online = isOnline
      }
    })
  }

  function isUserTyping(convId) {
    return !!typingUsers.value[convId]
  }

  return {
    conversations,
    messages,
    typingUsers,
    onlineUsers,
    fetchConversations,
    fetchMessages,
    sendMessage,
    sendFile,
    sendTyping,
    updateStatus,
    subscribeToConversation,
    unsubscribeFromConversation,
    subscribeToOnlineUsers,
    isUserTyping,
  }
})