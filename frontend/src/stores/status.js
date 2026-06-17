import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../api/axios'

export const useStatusStore = defineStore('status', () => {
  const usersWithStatuses = ref([]) // Liste des utilisateurs avec leurs statuts
  const myStatuses = ref([]) // Mes propres statuts
  const loading = ref(false)
  const error = ref(null)
  const selectedUser = ref(null) // Utilisateur sélectionné pour voir ses statuts

  // Récupérer tous les utilisateurs avec statuts
  const fetchStatuses = async () => {
    loading.value = true
    error.value = null
    try {
      const response = await api.get('/statuses')
      usersWithStatuses.value = response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du chargement des statuts'
    } finally {
      loading.value = false
    }
  }

  // Récupérer mes statuts
  const fetchMyStatuses = async () => {
    loading.value = true
    error.value = null
    try {
      const response = await api.get('/statuses/my')
      myStatuses.value = response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du chargement de vos statuts'
    } finally {
      loading.value = false
    }
  }

  // Récupérer les statuts d'un utilisateur spécifique (et marquer comme vus)
  const fetchUserStatuses = async (userId) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.get(`/statuses/user/${userId}`)
      selectedUser.value = response.data
      // Mettre à jour la liste pour marquer les statuts comme vus
      const userIndex = usersWithStatuses.value.findIndex(u => u.id === userId)
      if (userIndex !== -1) {
        usersWithStatuses.value[userIndex].has_unviewed = false
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du chargement des statuts'
    } finally {
      loading.value = false
    }
  }

  // Créer un statut
  const createStatus = async (data) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.post('/statuses', data)
      myStatuses.value.unshift(response.data)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la création du statut'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Marquer un statut comme vu
  const viewStatus = async (statusId) => {
    try {
      const response = await api.post(`/statuses/${statusId}/view`)
    } catch (err) {
      console.error('Erreur lors du marquage comme vu:', err)
    }
  }

  // Supprimer un statut
  const deleteStatus = async (statusId) => {
    loading.value = true
    try {
      await api.delete(`/statuses/${statusId}`)
      myStatuses.value = myStatuses.value.filter(s => s.id !== statusId)
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la suppression du statut'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Réinitialiser l'utilisateur sélectionné
  const clearSelectedUser = () => {
    selectedUser.value = null
  }

  return {
    usersWithStatuses,
    myStatuses,
    loading,
    error,
    selectedUser,
    fetchStatuses,
    fetchMyStatuses,
    fetchUserStatuses,
    createStatus,
    viewStatus,
    deleteStatus,
    clearSelectedUser
  }
})
