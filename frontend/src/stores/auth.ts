import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import type { User, LoginCredentials, RegisterData, ApiResponse } from '@/types'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(null)
  const loading = ref(false)

  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const isAdmin = computed(() => user.value?.is_admin === true)

  function init() {
    // Load token and user from localStorage
    const savedToken = localStorage.getItem('token')
    const savedUser = localStorage.getItem('user')
    
    if (savedToken && savedUser) {
      token.value = savedToken
      user.value = JSON.parse(savedUser)
    }
  }

  async function register(data: RegisterData): Promise<void> {
    const response = await api.post<ApiResponse<{ user: User; token: string }>>('/register', data)
    
    token.value = response.data.data.token
    user.value = response.data.data.user
    
    localStorage.setItem('token', token.value)
    localStorage.setItem('user', JSON.stringify(user.value))
  }

  async function login(credentials: LoginCredentials): Promise<void> {
    const response = await api.post<ApiResponse<{ user: User; token: string }>>('/login', credentials)
    
    token.value = response.data.data.token
    user.value = response.data.data.user
    
    localStorage.setItem('token', token.value)
    localStorage.setItem('user', JSON.stringify(user.value))
  }

  async function logout(): Promise<void> {
    try {
      await api.post('/logout')
    } catch {
      // Ignore errors on logout
    }
    
    token.value = null
    user.value = null
    localStorage.removeItem('token')
    localStorage.removeItem('user')
  }

  async function fetchUser(): Promise<void> {
    const response = await api.get<ApiResponse<User>>('/user')
    user.value = response.data.data
    localStorage.setItem('user', JSON.stringify(user.value))
  }

  return {
    user,
    token,
    loading,
    isAuthenticated,
    isAdmin,
    init,
    register,
    login,
    logout,
    fetchUser,
  }
})
