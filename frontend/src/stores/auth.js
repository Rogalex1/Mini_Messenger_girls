import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '@/api/auth'
import router from '@/router'

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('token'))
  if (token.value === 'null' || token.value === 'undefined') token.value = null
  
  const user  = ref(null)
  const loading = ref(false)

  const isLoggedIn = computed(() => !!token.value)
  const isAdmin    = computed(() => user.value?.role === 'admin')

  async function register(data) {
    loading.value = true
    try {
      const res = await authApi.register(data)
      setSession(res.data)
      return { success: true }
    } catch (e) {
      return { success: false, errors: e.response?.data }
    } finally {
      loading.value = false
    }
  }

  async function login(data) {
    loading.value = true
    try {
      const res = await authApi.login(data)
      setSession(res.data)
      return { success: true }
    } catch (e) {
      return {
        success: false,
        message: e.response?.data?.message,
        errors: e.response?.data
      }
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await authApi.logout()
    } catch (e) {
      console.error('Logout error', e)
    } finally {
      clearSession()
      // Utiliser window.location pour vider complètement l'état de l'application (Pinia, Echo, etc.)
      window.location.href = '/login'
    }
  }

  async function fetchMe() {
    if (!token.value) return
    try {
      const res = await authApi.me()
      user.value = res.data.user
    } catch {
      clearSession()
    }
  }

  function setSession(data) {
    token.value = data.token
    user.value  = data.user
    localStorage.setItem('token', data.token)
  }

  function clearSession() {
    token.value = null
    user.value  = null
    localStorage.removeItem('token')
  }

  return { user, token, loading, isLoggedIn, isAdmin, register, login, logout, fetchMe }
})