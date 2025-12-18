<template>
  <div class="bookings-page">
    <header class="page-header">
      <h1>{{ authStore.isAdmin ? 'All Bookings' : 'My Bookings' }}</h1>
      <div class="user-info">
        <span>Welcome, {{ authStore.user?.name }}</span>
        <router-link v-if="authStore.isAdmin" to="/admin/services" class="admin-link">Manage Services</router-link>
        <router-link v-if="authStore.isAdmin" to="/admin/bookings" class="admin-link">Admin Bookings</router-link>
        <router-link to="/services" class="admin-link">View Services</router-link>
        <button @click="handleLogout" class="logout-btn">Logout</button>
      </div>
    </header>

    <!-- Booking Form (only for non-admin users) -->
    <section v-if="!authStore.isAdmin" class="booking-form-section">
      <h2>Book a Cleaner</h2>
      <form @submit.prevent="createBooking" class="booking-form">
        <div class="form-row">
          <div class="form-group">
            <label for="service_id">Service</label>
            <select id="service_id" v-model="newBooking.service_id" required>
              <option value="">Select a service</option>
              <option v-for="service in services" :key="service.id" :value="service.id">
                {{ service.title }} - ${{ service.price }}
              </option>
            </select>
          </div>
          <div class="form-group">
            <label for="customer_name">Customer Name</label>
            <input
              id="customer_name"
              v-model="newBooking.customer_name"
              type="text"
              required
              placeholder="Your name"
            />
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="address">Address</label>
            <input
              id="address"
              v-model="newBooking.address"
              type="text"
              required
              placeholder="Cleaning address"
            />
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="date">Date</label>
            <input
              id="date"
              v-model="newBooking.date"
              type="date"
              required
              :min="today"
            />
          </div>
          <div class="form-group">
            <label for="time">Time</label>
            <input
              id="time"
              v-model="newBooking.time"
              type="time"
              required
            />
          </div>
        </div>
        <p v-if="formError" class="error">{{ formError }}</p>
        <button type="submit" :disabled="submitting">
          {{ submitting ? 'Creating...' : 'Create Booking' }}
        </button>
      </form>
    </section>

    <!-- Bookings List -->
    <section class="bookings-list-section">
      <h2>{{ authStore.isAdmin ? 'Manage Bookings' : 'Your Bookings' }}</h2>
      <p v-if="loading">Loading bookings...</p>
      <p v-else-if="bookings.length === 0">No bookings yet.</p>
      <div v-else class="bookings-grid">
        <div v-for="booking in bookings" :key="booking.id" class="booking-card" :class="'card-' + booking.status">
          <div class="booking-info">
            <h3>{{ booking.service?.title || 'Service' }}</h3>
            <p v-if="authStore.isAdmin && booking.user"><strong>User:</strong> {{ booking.user.name }} ({{ booking.user.email }})</p>
            <p><strong>Customer:</strong> {{ booking.customer_name }}</p>
            <p><strong>Address:</strong> {{ booking.address }}</p>
            <p><strong>Date:</strong> {{ formatDate(booking.date) }}</p>
            <p><strong>Time:</strong> {{ booking.time }}</p>
            <p><strong>Status:</strong> <span :class="'status-' + booking.status">{{ booking.status }}</span></p>
            <p v-if="booking.service"><strong>Price:</strong> ${{ booking.service.price }}</p>
          </div>
          <div class="booking-actions">
            <!-- User can cancel their own bookings -->
            <button 
              v-if="!authStore.isAdmin && (booking.status === 'pending' || booking.status === 'confirmed')"
              @click="cancelBooking(booking.id)" 
              class="cancel-user-btn"
              :disabled="cancellingId === booking.id"
            >
              {{ cancellingId === booking.id ? 'Cancelling...' : 'Cancel Booking' }}
            </button>
            
            <!-- User can delete their own bookings -->
            <button 
              v-if="!authStore.isAdmin"
              @click="deleteBooking(booking.id)" 
              class="delete-btn"
              :disabled="deletingId === booking.id"
            >
              {{ deletingId === booking.id ? 'Deleting...' : 'Delete' }}
            </button>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import type { Booking, Service } from '@/types'

const router = useRouter()
const authStore = useAuthStore()

const bookings = ref<Booking[]>([])
const services = ref<Service[]>([])
const loading = ref(false)
const submitting = ref(false)
const formError = ref('')
const cancellingId = ref<number | null>(null)
const deletingId = ref<number | null>(null)

const newBooking = ref({
  service_id: '',
  customer_name: '',
  address: '',
  date: '',
  time: ''
})

const today = computed(() => {
  const now = new Date()
  return now.toISOString().split('T')[0]
})

const fetchBookings = async () => {
  loading.value = true
  try {
    const response = await api.get('/bookings')
    bookings.value = response.data.data || response.data
  } catch (err: any) {
    console.error('Error fetching bookings:', err)
  } finally {
    loading.value = false
  }
}

const fetchServices = async () => {
  try {
    const response = await api.get('/services')
    services.value = response.data.data || response.data
  } catch (err: any) {
    console.error('Error fetching services:', err)
  }
}

const createBooking = async () => {
  formError.value = ''
  submitting.value = true
  
  try {
    await api.post('/bookings', newBooking.value)
    
    // Reset form
    newBooking.value = {
      service_id: '',
      customer_name: '',
      address: '',
      date: '',
      time: ''
    }
    
    // Refresh bookings
    await fetchBookings()
    
    alert('Booking created successfully!')
  } catch (err: any) {
    formError.value = err.response?.data?.message || 'Failed to create booking'
    console.error('Error creating booking:', err)
  } finally {
    submitting.value = false
  }
}

const cancelBooking = async (bookingId: number) => {
  if (!confirm('Are you sure you want to cancel this booking?')) return
  
  cancellingId.value = bookingId
  try {
    await api.post(`/bookings/${bookingId}/cancel`)
    await fetchBookings()
    alert('Booking cancelled successfully!')
  } catch (err: any) {
    alert(err.response?.data?.message || 'Failed to cancel booking')
    console.error('Error cancelling booking:', err)
  } finally {
    cancellingId.value = null
  }
}

const deleteBooking = async (bookingId: number) => {
  if (!confirm('Are you sure you want to delete this booking?')) return
  
  deletingId.value = bookingId
  try {
    await api.delete(`/bookings/${bookingId}`)
    await fetchBookings()
    alert('Booking deleted successfully!')
  } catch (err: any) {
    alert(err.response?.data?.message || 'Failed to delete booking')
    console.error('Error deleting booking:', err)
  } finally {
    deletingId.value = null
  }
}

const formatDate = (dateString: string): string => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  })
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
  fetchBookings()
  if (!authStore.isAdmin) {
    fetchServices()
  }
})
</script>

<style scoped>
.bookings-page {
  max-width: 1400px;
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

.user-info {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.admin-link {
  padding: 0.5rem 1rem;
  background: #3b82f6;
  color: white;
  text-decoration: none;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  transition: background 0.2s;
}

.admin-link:hover {
  background: #2563eb;
}

.logout-btn {
  padding: 0.5rem 1rem;
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 0.375rem;
  cursor: pointer;
  font-size: 0.875rem;
  transition: background 0.2s;
}

.logout-btn:hover {
  background: #dc2626;
}

.booking-form-section {
  background: white;
  padding: 2rem;
  border-radius: 0.5rem;
  margin-bottom: 2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.booking-form-section h2 {
  margin-top: 0;
  color: #1f2937;
}

.booking-form {
  max-width: 800px;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #374151;
}

.form-group input,
.form-group select {
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  font-size: 1rem;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.error {
  color: #ef4444;
  margin: 1rem 0;
}

button[type="submit"] {
  background: #10b981;
  color: white;
  padding: 0.75rem 2rem;
  border: none;
  border-radius: 0.375rem;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  margin-top: 1rem;
  transition: background 0.2s;
}

button[type="submit"]:hover:not(:disabled) {
  background: #059669;
}

button[type="submit"]:disabled {
  background: #9ca3af;
  cursor: not-allowed;
}

.bookings-list-section {
  margin-top: 2rem;
}

.bookings-list-section h2 {
  margin-bottom: 1.5rem;
  color: #1f2937;
}

.bookings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 1.5rem;
}

.booking-card {
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 0.5rem;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s, box-shadow 0.2s;
}

.booking-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.card-pending {
  border-left: 4px solid #f59e0b;
}

.card-confirmed {
  border-left: 4px solid #10b981;
}

.card-cancelled {
  border-left: 4px solid #ef4444;
  opacity: 0.7;
}

.booking-info h3 {
  margin: 0 0 1rem 0;
  color: #1f2937;
  font-size: 1.25rem;
}

.booking-info p {
  margin: 0.5rem 0;
  color: #6b7280;
}

.status-pending {
  color: #f59e0b;
  font-weight: 600;
  text-transform: uppercase;
}

.status-confirmed {
  color: #10b981;
  font-weight: 600;
  text-transform: uppercase;
}

.status-cancelled {
  color: #ef4444;
  font-weight: 600;
  text-transform: uppercase;
}

.booking-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
}

.cancel-user-btn,
.delete-btn {
  flex: 1;
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}

.cancel-user-btn {
  background: #f59e0b;
  color: white;
}

.cancel-user-btn:hover:not(:disabled) {
  background: #d97706;
}

.delete-btn {
  background: #ef4444;
  color: white;
}

.delete-btn:hover:not(:disabled) {
  background: #dc2626;
}

.cancel-user-btn:disabled,
.delete-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
