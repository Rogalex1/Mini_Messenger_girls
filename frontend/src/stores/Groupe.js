import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/api/axios'

export const useGroupeStore = defineStore('groupes', () => {
  const groupes = ref([])
  const loading = ref(false)
  const error = ref(null)
  const currentMessages= ref([])

  // Affichage des groupes
  const fetchGroupes = async () => {
    loading.value = true
    error.value = null

    try {
      const response = await api.get('/groups')
      groupes.value = response.data.data
      return true
    } catch (err) {
      error.value = "Erreur lors de la récupération des groupes"
      console.error(err)
      return false
    } finally {
      loading.value = false
    }
  }

 const fetchGroupMessage =async (groupId) =>{
    loading.value=true
    error.value=null

    try {
      const res=await api.get(`/groups/${groupId}/messages`)
      currentMessages.value=res.data.messages
      return true
    } catch (error) {
      return (false)
    }finally{
      loading.value=false
    }
  }

  // Affichage d'un groupe spécifique
  const fetchGroup = async (id) => {
    loading.value = true
    try {
      const response = await api.get(`/groups/${id}`)
      return response.data.data
    } catch (err) {
      error.value = "Erreur lors de la récupération du groupe"
      console.error(err)
      return null
    } finally {
      loading.value = false
    }
  }

  // Création d'un groupe
  const addGroup = async (groupData) => {
    loading.value = true
    try {
      const response = await api.post('/groups', groupData)
      groupes.value.unshift(response.data.data)
      return response.data.data
    } catch (err) {
      error.value = "Impossible d'ajouter le groupe"
      console.error(err)
      throw err
    } finally {
      loading.value = false
    }
  }
  // Création d'un groupe
  const sendGroupMessages = async (groupId , content) => {
    loading.value = true
    try {
      const response = await api.post(`/groups/${groupId}/messages`, { message: content, type: 'text' })
      currentMessages.value.push(response.data.message)
      return response.data.message
    } catch (err) {
      error.value = "Impossible d'envoyer le message"
      console.error(err)
      throw err
    } finally {
      loading.value = false
    }
  }
  
  // Mise à jour d'un groupe
  const updateGroup = async (id, groupData) => {
    loading.value = true
    try {
      const response = await api.put(`/groups/${id}`, groupData)
      const index = groupes.value.findIndex(g => g.id === id)
      if (index !== -1) {
        groupes.value[index] = response.data.data
      }
      return response.data.data
    } catch (err) {
      error.value = "Impossible de mettre à jour le groupe"
      console.error(err)
      throw err
    } finally {
      loading.value = false
    }
  }

  // Suppression d'un groupe
  const destroyGroup = async (id) => {
    try {
      await api.delete(`/groups/${id}`)
      groupes.value = groupes.value.filter(g => g.id !== id)
      return true
    } catch (err) {
      error.value = "Impossible de supprimer le groupe"
      console.error(err)
      return false
    }
  }

  // Ajouter des membres
  const addMembers = async (groupId, userIds) => {
    try {
      const response = await api.post(`/groups/${groupId}/members`, { user_ids: userIds })
      const index = groupes.value.findIndex(g => g.id === groupId)
      if (index !== -1) {
        groupes.value[index] = response.data.data
      }
      return response.data.data
    } catch (err) {
      console.error(err)
      throw err
    }
  }

  // Supprimer un membre
  const removeMember = async (groupId, userId) => {
    try {
      const response = await api.delete(`/groups/${groupId}/members/${userId}`)
      const index = groupes.value.findIndex(g => g.id === groupId)
      if (index !== -1) {
        groupes.value[index] = response.data.data
      }
      return response.data.data
    } catch (err) {
      console.error(err)
      throw err
    }
  }

  // Promouvoir en admin
  const promoteToAdmin = async (groupId, userId) => {
    try {
      const response = await api.post(`/groups/${groupId}/members/${userId}/promote`)
      const index = groupes.value.findIndex(g => g.id === groupId)
      if (index !== -1) {
        groupes.value[index] = response.data.data
      }
      return response.data.data
    } catch (err) {
      console.error(err)
      throw err
    }
  }

  // Rétrograder d'admin
  const demoteFromAdmin = async (groupId, userId) => {
    try {
      const response = await api.post(`/groups/${groupId}/members/${userId}/demote`)
      const index = groupes.value.findIndex(g => g.id === groupId)
      if (index !== -1) {
        groupes.value[index] = response.data.data
      }
      return response.data.data
    } catch (err) {
      console.error(err)
      throw err
    }
  }

  // Quitter le groupe
  const leaveGroup = async (groupId) => {
    try {
      await api.post(`/groups/${groupId}/leave`)
      groupes.value = groupes.value.filter(g => g.id !== groupId)
      return true
    } catch (err) {
      console.error(err)
      throw err
    }
  }

  const refreshGroupes = async () => {
    await fetchGroupes()
  }

  return {
    groupes,
    loading,
    error,
    fetchGroupes,
    fetchGroup,
    refreshGroupes,
    addGroup,
    updateGroup,
    destroyGroup,
    addMembers,
    removeMember,
    promoteToAdmin,
    demoteFromAdmin,
    leaveGroup,

    currentMessages,
    fetchGroupMessage,
    sendGroupMessages,
  }
})


