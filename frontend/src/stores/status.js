import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../api/axios'

export const useStatusStore = defineStore('status', () => {
  const statuses = ref([])
  const myStatuses = ref([])
  const loading = ref(false)
  const error = ref(null)

  const fetchStatuses = async () => {
    loading.value = true
    error.value = null
    try {
      const response = await api.get('/statuses')
      statuses.value = response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du chargement des statuts'
    } finally {
      loading.value = false
    }
  }

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

  const createStatus = async (data) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.post('/statuses', data)
      statuses.value.unshift(response.data)
      myStatuses.value.unshift(response.data)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la création du statut'
      throw err
    } finally {
      loading.value = false
    }
  }

  const viewStatus = async (statusId) => {
    try {
      const response = await api.post(`/statuses/${statusId}/view`)
      const index = statuses.value.findIndex(s => s.id === statusId)
      if (index !== -1) {
        if (!statuses.value[index].views) {
          statuses.value[index].views = []
        }
        statuses.value[index].views.push(response.data.view)
      }
    } catch (err) {
      console.error('Erreur lors du marquage comme vu:', err)
    }
  }

  const deleteStatus = async (statusId) => {
    loading.value = true
    try {
      await api.delete(`/statuses/${statusId}`)
      statuses.value = statuses.value.filter(s => s.id !== statusId)
      myStatuses.value = myStatuses.value.filter(s => s.id !== statusId)
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la suppression du statut'
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    statuses,
    myStatuses,
    loading,
    error,
    fetchStatuses,
    fetchMyStatuses,
    createStatus,
    viewStatus,
    deleteStatus
  }
})
