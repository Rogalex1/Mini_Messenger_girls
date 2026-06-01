import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../api/axios'

export const usePostStore = defineStore('post', () => {
  const posts = ref([])
  const myPosts = ref([])
  const loading = ref(false)
  const error = ref(null)
  const currentPage = ref(1)
  const hasMore = ref(true)

  const fetchPosts = async (reset = false) => {
    if (reset) {
      posts.value = []
      currentPage.value = 1
      hasMore.value = true
    }
    if (!hasMore.value) return

    loading.value = true
    error.value = null
    try {
      const response = await api.get(`/posts?page=${currentPage.value}`)
      if (reset) {
        posts.value = response.data.data
      } else {
        posts.value = [...posts.value, ...response.data.data]
      }
      hasMore.value = response.data.current_page < response.data.last_page
      currentPage.value++
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du chargement des publications'
    } finally {
      loading.value = false
    }
  }

  const fetchMyPosts = async () => {
    loading.value = true
    error.value = null
    try {
      const response = await api.get('/posts/my')
      myPosts.value = response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors du chargement de vos publications'
    } finally {
      loading.value = false
    }
  }

  const createPost = async (data) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.post('/posts', data)
      posts.value.unshift(response.data)
      myPosts.value.unshift(response.data)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la création de la publication'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updatePost = async (postId, data) => {
    loading.value = true
    error.value = null
    try {
      const formData = new FormData()
      if (data.content) formData.append('content', data.content)
      if (data.media) formData.append('media', data.media)
      formData.append('_method', 'PUT')

      const response = await api.post(`/posts/${postId}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      const index = posts.value.findIndex(p => p.id === postId)
      if (index !== -1) posts.value[index] = response.data
      const myIndex = myPosts.value.findIndex(p => p.id === postId)
      if (myIndex !== -1) myPosts.value[myIndex] = response.data
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la modification de la publication'
      throw err
    } finally {
      loading.value = false
    }
  }

  const deletePost = async (postId) => {
    loading.value = true
    try {
      await api.delete(`/posts/${postId}`)
      posts.value = posts.value.filter(p => p.id !== postId)
      myPosts.value = myPosts.value.filter(p => p.id !== postId)
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la suppression de la publication'
      throw err
    } finally {
      loading.value = false
    }
  }

  const toggleLike = async (postId) => {
    try {
      const response = await api.post(`/posts/${postId}/like`)
      const index = posts.value.findIndex(p => p.id === postId)
      if (index !== -1) {
        posts.value[index].likes_count += response.data.liked ? 1 : -1
      }
      const myIndex = myPosts.value.findIndex(p => p.id === postId)
      if (myIndex !== -1) {
        myPosts.value[myIndex].likes_count += response.data.liked ? 1 : -1
      }
      return response.data
    } catch (err) {
      console.error('Erreur lors du like:', err)
      throw err
    }
  }

  const addComment = async (postId, comment) => {
    try {
      const response = await api.post(`/posts/${postId}/comment`, { comment })
      const index = posts.value.findIndex(p => p.id === postId)
      if (index !== -1) {
        posts.value[index].comments.push(response.data)
        posts.value[index].comments_count++
      }
      const myIndex = myPosts.value.findIndex(p => p.id === postId)
      if (myIndex !== -1) {
        myPosts.value[myIndex].comments.push(response.data)
        myPosts.value[myIndex].comments_count++
      }
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de l\'ajout du commentaire'
      throw err
    }
  }

  const deleteComment = async (postId, commentId) => {
    try {
      await api.delete(`/posts/${postId}/comments/${commentId}`)
      const index = posts.value.findIndex(p => p.id === postId)
      if (index !== -1) {
        posts.value[index].comments = posts.value[index].comments.filter(c => c.id !== commentId)
        posts.value[index].comments_count--
      }
      const myIndex = myPosts.value.findIndex(p => p.id === postId)
      if (myIndex !== -1) {
        myPosts.value[myIndex].comments = myPosts.value[myIndex].comments.filter(c => c.id !== commentId)
        myPosts.value[myIndex].comments_count--
      }
    } catch (err) {
      console.error('Erreur lors de la suppression du commentaire:', err)
      throw err
    }
  }

  return {
    posts,
    myPosts,
    loading,
    error,
    hasMore,
    fetchPosts,
    fetchMyPosts,
    createPost,
    updatePost,
    deletePost,
    toggleLike,
    addComment,
    deleteComment
  }
})

