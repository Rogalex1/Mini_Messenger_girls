import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/api/axios'
import echo from '@/echo'
import { useAuthStore } from '@/stores/auth'

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
    // Mise à jour optimiste
    const tempId = Date.now()
    const authStore = useAuthStore()
    const tempMsg = {
      id: tempId,
      conversation_id: convId,
      sender_id: authStore.user.id,
      message: data.message,
      type: data.type || 'text',
      is_seen: false,
      created_at: new Date().toISOString(),
      sender: {
        id: authStore.user.id,
        username: authStore.user.username,
        avatar: authStore.user.profile?.profile_photo
      },
      optimistic: true
    }
    
    addMessage(convId, tempMsg)
    updateLastMessage(convId, tempMsg)

    try {
      const res = await api.post(`/conversations/${convId}/messages`, data)
      // Remplacer le message temporaire par le vrai
      const msgs = messages.value[convId] ?? []
      const idx = msgs.findIndex(m => m.id === tempId)
      if (idx !== -1) {
        msgs[idx] = res.data.message
      }
      updateLastMessage(convId, res.data.message)
    } catch (e) {
      // Retirer le message en cas d'erreur
      messages.value[convId] = messages.value[convId].filter(m => m.id !== tempId)
      throw e
    }
  }

  async function sendFile(convId, file) {
    // Mise à jour optimiste pour les fichiers
    const tempId = Date.now()
    const authStore = useAuthStore()
    const isImage = file.type.startsWith('image/')
    
    const tempMsg = {
      id: tempId,
      conversation_id: convId,
      sender_id: authStore.user.id,
      message: file.name,
      type: isImage ? 'image' : 'file',
      file_url: isImage ? URL.createObjectURL(file) : null, // Preview locale pour image
      is_seen: false,
      created_at: new Date().toISOString(),
      sender: {
        id: authStore.user.id,
        username: authStore.user.username,
        avatar: authStore.user.profile?.profile_photo
      },
      optimistic: true
    }

    addMessage(convId, tempMsg)
    updateLastMessage(convId, tempMsg)

    const form = new FormData()
    form.append('file', file)
    
    try {
      const res = await api.post(`/conversations/${convId}/upload`, form, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      
      const msgs = messages.value[convId] ?? []
      const idx = msgs.findIndex(m => m.id === tempId)
      if (idx !== -1) {
        msgs[idx] = res.data.message
      }
      updateLastMessage(convId, res.data.message)
    } catch (e) {
      messages.value[convId] = messages.value[convId].filter(m => m.id !== tempId)
      throw e
    }
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

  async function markAsRead(convId) {
    await api.post(`/conversations/${convId}/read`)
    // Mettre à jour localement les compteurs non lus
    const conv = conversations.value.find(c => c.id === convId)
    if (conv) conv.unread_count = 0
  }

  async function addReaction(messageId, reaction) {
    await api.post(`/messages/${messageId}/reactions`, { reaction })
  }

  async function removeReaction(messageId) {
    await api.delete(`/messages/${messageId}/reactions`)
  }

  // ─── WebSocket : s'abonner à une conversation ─
  function subscribeToConversation(convId) {
    // console.log(`Abonnement au canal privé: conversation.${convId}`)
    echo.private(`conversation.${convId}`)
      // .subscribed(() => {
      //   console.log(`Succès: Abonné au canal conversation.${convId}`)
      // })
      .error((error) => {
        console.error(`Erreur d'abonnement au canal conversation.${convId}:`, error)
      })
      // Nouveau message reçu
      .listen('.message.sent', (data) => {
        const authStore = useAuthStore()
        // console.log('Nouveau message reçu via Echo:', data)
        
        // Si c'est nous qui avons envoyé le message, on ne l'ajoute pas à nouveau
        // car le store l'a déjà ajouté via sendMessage() (mise à jour optimiste)
        if (data.sender_id === authStore.user.id) {
          console.log('Message ignoré  car envoyé par nous-mêmesssss')
          return
        }

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
            msg.reactions.push({ 
              user_id: data.user_id, 
              username: data.username,
              reaction: data.reaction 
            })
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
    markAsRead,
    addReaction,
    removeReaction,
    subscribeToConversation,
    unsubscribeFromConversation,
    subscribeToOnlineUsers,
    isUserTyping,
  }
})