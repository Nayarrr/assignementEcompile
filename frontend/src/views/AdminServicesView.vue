<template>
  <div class="admin-services-page">
    <header class="page-header">
      <h1>Manage Services</h1>
      <div class="header-actions">
        <router-link to="/services" class="back-link">View Services</router-link>
        <router-link to="/admin/bookings" class="back-link">Manage Bookings</router-link>
        <router-link to="/bookings" class="back-link">My Bookings</router-link>
        <button @click="handleLogout" class="logout-btn">Logout</button>
      </div>
    </header>

    <!-- Create Service Form -->
    <section class="form-section">
      <h2>{{ editingService ? 'Edit Service' : 'Create New Service' }}</h2>
      <form @submit.prevent="saveService" class="service-form">
        <div class="form-group">
          <label for="title">Title</label>
          <input id="title" v-model="form.title" type="text" required placeholder="Service title" />
        </div>
        <div class="form-group">
          <label for="description">Description</label>
          <textarea id="description" v-model="form.description" placeholder="Service description"></textarea>
        </div>
        <div class="form-group">
          <label for="price">Price ($)</label>
          <input id="price" v-model="form.price" type="number" step="0.01" min="0" required placeholder="0.00" />
        </div>
        <p v-if="formError" class="error">{{ formError }}</p>
        <div class="form-actions">
          <button type="submit" :disabled="submitting">
            {{ submitting ? 'Saving...' : (editingService ? 'Update Service' : 'Create Service') }}
          </button>
          <button v-if="editingService" type="button" @click="cancelEdit" class="cancel-btn">Cancel</button>
        </div>
      </form>
    </section>

    <!-- Services List -->
    <section class="services-list-section">
      <h2>All Services</h2>
      <p v-if="loading">Loading services...</p>
      <p v-else-if="services.length === 0">No services yet. Create your first one above!</p>
      <div v-else class="services-grid">
        <div v-for="service in services" :key="service.id" class="service-card">
          <div class="service-info">
            <h3>{{ service.title }}</h3>
            <p v-if="service.description">{{ service.description }}</p>
            <p class="price"><strong>Price:</strong> ${{ service.price }}</p>
          </div>
          <div class="service-actions">
            <button @click="startEdit(service)" class="edit-btn">Edit</button>
            <button @click="deleteService(service.id)" class="delete-btn">Delete</button>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import type { Service } from '@/types'

const router = useRouter()
const authStore = useAuthStore()

const services = ref<Service[]>([])
const loading = ref(false)
const submitting = ref(false)
const formError = ref('')
const editingService = ref<Service | null>(null)

const form = reactive({
  title: '',
  description: '',
  price: '',
})

function resetForm() {
  form.title = ''
  form.description = ''
  form.price = ''
  editingService.value = null
}

function startEdit(service: Service) {
  editingService.value = service
  form.title = service.title
  form.description = service.description || ''
  form.price = String(service.price)
}

function cancelEdit() {
  resetForm()
}

async function fetchServices() {
  loading.value = true
  try {
    const response = await api.get<{ data: Service[] }>('/services')
    services.value = response.data.data
  } catch {
    console.error('Failed to fetch services')
  } finally {
    loading.value = false
  }
}

async function saveService() {
  formError.value = ''
  submitting.value = true
  try {
    const payload = {
      title: form.title,
      description: form.description || null,
      price: Number(form.price),
    }

    if (editingService.value) {
      await api.put(`/services/${editingService.value.id}`, payload)
    } else {
      await api.post('/services', payload)
    }

    resetForm()
    await fetchServices()
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    formError.value = err.response?.data?.message || 'Failed to save service'
  } finally {
    submitting.value = false
  }
}

async function deleteService(id: number) {
  if (!confirm('Are you sure you want to delete this service?')) return
  try {
    await api.delete(`/services/${id}`)
    await fetchServices()
  } catch {
    console.error('Failed to delete service')
  }
}

async function handleLogout() {
  await authStore.logout()
  router.push('/login')
}

onMounted(() => {
  fetchServices()
})
</script>

<style scoped>
.admin-services-page {
  max-width: 900px;
  margin: 0 auto;
  padding: 1rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #ddd;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.back-link {
  color: #2196F3;
  text-decoration: none;
}

.logout-btn {
  padding: 0.5rem 1rem;
  background-color: #f44336;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.form-section,
.services-list-section {
  margin-bottom: 2rem;
}

.service-form {
  padding: 1.5rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  background: #f9f9f9;
}

.form-group {
  margin-bottom: 1rem;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
}

input, textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 1rem;
}

textarea {
  min-height: 100px;
  resize: vertical;
}

.form-actions {
  display: flex;
  gap: 1rem;
}

button[type="submit"] {
  padding: 0.75rem 1.5rem;
  background-color: #4CAF50;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  cursor: pointer;
}

.cancel-btn {
  padding: 0.75rem 1.5rem;
  background-color: #757575;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

button:disabled {
  background-color: #ccc;
}

.error {
  color: red;
  margin-bottom: 1rem;
}

.services-grid {
  display: grid;
  gap: 1rem;
}

.service-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  background: white;
}

.service-info h3 {
  margin: 0 0 0.5rem 0;
}

.service-info p {
  margin: 0.25rem 0;
  color: #666;
}

.service-info .price {
  color: #4CAF50;
  font-weight: 500;
}

.service-actions {
  display: flex;
  gap: 0.5rem;
}

.edit-btn {
  padding: 0.5rem 1rem;
  background-color: #2196F3;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.delete-btn {
  padding: 0.5rem 1rem;
  background-color: #f44336;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
</style>
