import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/axios'
import router from '@/router'

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

  // Register - AGORA COM REDIRECIONAMENTO AUTOMÁTICO
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
      
      // 👇 REDIRECIONAMENTO AUTOMÁTICO
      if (newUser.role_id === 2) {
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
    user,
    token,
    isLoading,
    isAuthenticated,
    login,
    register,
    logout
  }
}, {
  persist: {
    key: 'codecraft-user',
    storage: localStorage,
    pick: ['user', 'token']
  }
})