import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/axios'
import router from '@/router'
import { useWatchlistStore } from './watchlistStore'
import { useProgressStore } from './progressStore'

export const useUserStore = defineStore('user', () => {
  // Estado
  const user = ref<any>(null)
  const token = ref<string | null>(null)
  const isLoading = ref(false)

  // Carregar do localStorage ao iniciar
  const loadFromStorage = () => {
    const storedUser = localStorage.getItem('user')
    const storedToken = localStorage.getItem('auth_token')
    
    if (storedUser && storedToken) {
      user.value = JSON.parse(storedUser)
      token.value = storedToken
    }
  }

  // ================================================
  // AVATAR METHODS
  // ================================================
  
  /**
   * Upload de novo avatar
   */
  const updateAvatar = async (formData: FormData) => {
    isLoading.value = true
    try {
      const response = await api.post('/user/avatar', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      
      if (user.value) {
        user.value.avatar = response.data.avatar_url
        // Atualizar localStorage
        localStorage.setItem('user', JSON.stringify(user.value))
      }
      
      return { success: true, avatar_url: response.data.avatar_url }
    } catch (error: any) {
      console.error('Erro ao fazer upload:', error)
      return { 
        success: false, 
        error: error.response?.data?.message || 'Erro ao atualizar avatar' 
      }
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Remover avatar
   */
  const removeAvatar = async () => {
    isLoading.value = true
    try {
      await api.delete('/user/avatar')
      
      if (user.value) {
        delete user.value.avatar
        // Atualizar localStorage
        localStorage.setItem('user', JSON.stringify(user.value))
      }
      
      return { success: true }
    } catch (error: any) {
      console.error('Erro ao remover avatar:', error)
      return { 
        success: false, 
        error: error.response?.data?.message || 'Erro ao remover avatar' 
      }
    } finally {
      isLoading.value = false
    }
  }

  // ================================================
  // PREFERENCES METHODS
  // ================================================
  
  /**
   * Buscar preferências do utilizador
   */
  const fetchPreferences = async () => {
    try {
      const response = await api.get('/user/preferences')
      return response.data
    } catch (error: any) {
      console.error('Erro ao buscar preferências:', error)
      return null
    }
  }

  /**
   * Atualizar preferências do utilizador
   */
  const updatePreferences = async (data: { theme: string }) => {
    try {
      const response = await api.put('/user/preferences', data)
      return { success: true, data: response.data }
    } catch (error: any) {
      console.error('Erro ao atualizar preferências:', error)
      return { 
        success: false, 
        error: error.response?.data?.message || 'Erro ao atualizar preferências' 
      }
    }
  }

  // ================================================
  // AUTH METHODS
  // ================================================

  // Login
  const login = async (email: string, password: string, rememberMe: boolean) => {
    isLoading.value = true
    
    try {
      const response = await api.post('/login', { email, password })
      const { user: userData, token: authToken } = response.data

      user.value = userData
      token.value = authToken

      localStorage.setItem('auth_token', authToken)
      localStorage.setItem('user', JSON.stringify(userData))
      
      if (rememberMe) {
        localStorage.setItem('rememberMe', 'true')
      }

      if (userData.role_id === 2) {
        const watchlistStore = useWatchlistStore()
        const progressStore = useProgressStore()
        await Promise.all([
          watchlistStore.fetchWatchlist(),
          progressStore.fetchProgressCourses()
        ])
        router.push('/user')
      } else if (userData.role_id === 1) {
        router.push('/admin')
      }

      return { success: true }
    } catch (error: any) {
      const message = error.response?.data?.error || 'Erro no login'
      return { success: false, error: message }
    } finally {
      isLoading.value = false
    }
  }

  // Register
  const register = async (userData: {
    name: string
    email: string
    password: string
    password_confirmation: string
  }) => {
    isLoading.value = true
    
    try {
      const response = await api.post('/register', userData)
      const { user: newUser, token: authToken } = response.data

      user.value = newUser
      token.value = authToken

      localStorage.setItem('auth_token', authToken)
      localStorage.setItem('user', JSON.stringify(newUser))

      if (newUser.role_id === 2) {
        const watchlistStore = useWatchlistStore()
        const progressStore = useProgressStore()
        await Promise.all([
          watchlistStore.fetchWatchlist(),
          progressStore.fetchProgressCourses()
        ])
        router.push('/user')
      } else if (newUser.role_id === 1) {
        router.push('/admin')
      }
      
      return { success: true }
    } catch (error: any) {
      const message = error.response?.data?.message || 
                      error.response?.data?.errors || 
                      'Erro ao criar conta'
      return { success: false, error: message }
    } finally {
      isLoading.value = false
    }
  }

  // Logout
  const logout = async () => {
    isLoading.value = true
    
    try {
      await api.post('/logout').catch(() => {})
    } finally {
      user.value = null
      token.value = null
      
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
      localStorage.removeItem('rememberMe')
      
      router.push('/login')
      isLoading.value = false
    }
  }

  // Getter
  const isAuthenticated = (): boolean => {
    return !!token.value
  }

  // Inicializar
  loadFromStorage()

  return {
    // Estado
    user,
    token,
    isLoading,
    
    // Getters
    isAuthenticated,
    
    // Auth Methods
    login,
    register,
    logout,
    
    // Avatar Methods
    updateAvatar,
    removeAvatar,
    
    // Preferences Methods
    fetchPreferences,
    updatePreferences,
  }
}, {
  persist: {
    key: 'codecraft-user',
    storage: localStorage,
    pick: ['user', 'token']
  }
})