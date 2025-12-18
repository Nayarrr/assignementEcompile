<template>
  <div class="services-page">
    <header class="page-header">
      <h1>Available Cleaning Services</h1>
      <div class="header-actions">
        <router-link v-if="!authStore.isAuthenticated" to="/login" class="btn-link">Login</router-link>
        <router-link v-if="!authStore.isAuthenticated" to="/register" class="btn-link">Register</router-link>
        <router-link v-if="authStore.isAuthenticated" to="/bookings" class="btn-link">My Bookings</router-link>
        <button v-if="authStore.isAuthenticated" @click="handleLogout" class="logout-btn">Logout</button>
      </div>
    </header>

    <section class="services-section">
      <p v-if="loading" class="loading">Loading services...</p>
      <p v-else-if="error" class="error">{{ error }}</p>
      <p v-else-if="services.length === 0" class="empty">No services available at the moment.</p>
      
      <div v-else class="services-grid">
        <div v-for="service in services" :key="service.id" class="service-card">
          <h3>{{ service.title }}</h3>
          <p class="description">{{ service.description }}</p>
          <p class="price">${{ service.price }}</p>
          <router-link 
            v-if="authStore.isAuthenticated" 
            to="/bookings" 
            class="book-btn"
          >
            Book Now
          </router-link>
          <router-link 
            v-else 
            to="/login" 
            class="book-btn"
          >
            Login to Book
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import type { Service } from '@/types'

const router = useRouter()
const authStore = useAuthStore()

const services = ref<Service[]>([])
const loading = ref(false)
const error = ref('')

const fetchServices = async () => {
  loading.value = true
  error.value = ''
  try {
    const response = await api.get('/services')
    services.value = response.data.data || response.data
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to load services'
    console.error('Error fetching services:', err)
  } finally {
    loading.value = false
  }
}

const handleLogout = async () => {
  try {
    await api.post('/logout')
  } catch (err) {
    console.error('Logout error:', err)
  }
  authStore.logout()
  router.push('/login')
}

onMounted(() => {
  fetchServices()
})
</script>

<style scoped>
.services-page {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #e5e7eb;
}

.page-header h1 {
  font-size: 2rem;
  color: #1f2937;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.btn-link {
  padding: 0.5rem 1rem;
  background: #3b82f6;
  color: white;
  text-decoration: none;
  border-radius: 0.375rem;
  font-weight: 500;
  transition: background 0.2s;
}

.btn-link:hover {
  background: #2563eb;
}

.logout-btn {
  padding: 0.5rem 1rem;
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 0.375rem;
  cursor: pointer;
  font-weight: 500;
  transition: background 0.2s;
}

.logout-btn:hover {
  background: #dc2626;
}

.services-section {
  margin-top: 2rem;
}

.loading,
.error,
.empty {
  text-align: center;
  padding: 2rem;
  font-size: 1.125rem;
}

.error {
  color: #ef4444;
}

.services-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
  margin-top: 1.5rem;
}

.service-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s, box-shadow 0.2s;
}

.service-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.service-card h3 {
  font-size: 1.5rem;
  color: #1f2937;
  margin: 0 0 1rem 0;
}

.description {
  color: #6b7280;
  margin-bottom: 1rem;
  line-height: 1.5;
}

.price {
  font-size: 1.875rem;
  font-weight: bold;
  color: #3b82f6;
  margin: 1rem 0;
}

.book-btn {
  display: block;
  width: 100%;
  padding: 0.75rem;
  background: #10b981;
  color: white;
  text-align: center;
  text-decoration: none;
  border-radius: 0.375rem;
  font-weight: 600;
  transition: background 0.2s;
  margin-top: 1rem;
}

.book-btn:hover {
  background: #059669;
}
</style>
