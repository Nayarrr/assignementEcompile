<template>
  <div class="admin-bookings-page">
    <header class="page-header">
      <h1>Manage All Bookings</h1>
      <div class="header-actions">
        <router-link to="/admin/services" class="link-btn">Manage Services</router-link>
        <button @click="handleLogout" class="logout-btn">Logout</button>
      </div>
    </header>

    <!-- Filters -->
    <section class="filters-section">
      <div class="filter-group">
        <label for="statusFilter">Filter by Status:</label>
        <select id="statusFilter" v-model="statusFilter">
          <option value="">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="confirmed">Confirmed</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
    </section>

    <!-- Bookings List -->
    <section class="bookings-list-section">
      <p v-if="loading" class="loading">Loading bookings...</p>
      <p v-else-if="error" class="error">{{ error }}</p>
      <p v-else-if="filteredBookings.length === 0" class="empty">
        {{ statusFilter ? `No ${statusFilter} bookings found.` : 'No bookings yet.' }}
      </p>
      
      <div v-else class="bookings-grid">
        <div 
          v-for="booking in filteredBookings" 
          :key="booking.id" 
          class="booking-card" 
          :class="`status-${booking.status}`"
        >
          <div class="booking-header">
            <h3>{{ booking.service?.title || 'Service' }}</h3>
            <span class="status-badge" :class="`badge-${booking.status}`">
              {{ booking.status_label || booking.status }}
            </span>
          </div>

          <div class="booking-details">
            <div class="detail-row">
              <span class="label">Customer:</span>
              <span>{{ booking.customer_name }}</span>
            </div>
            <div class="detail-row">
              <span class="label">User:</span>
              <span>{{ booking.user?.name }} ({{ booking.user?.email }})</span>
            </div>
            <div class="detail-row">
              <span class="label">Address:</span>
              <span>{{ booking.address }}</span>
            </div>
            <div class="detail-row">
              <span class="label">Date & Time:</span>
              <span>{{ formatDate(booking.date) }} at {{ booking.time }}</span>
            </div>
            <div class="detail-row">
              <span class="label">Price:</span>
              <span class="price">${{ booking.service?.price || '0.00' }}</span>
            </div>
          </div>

          <div class="booking-actions">
            <button 
              v-if="booking.status === 'pending'"
              @click="booking.id && updateStatus(booking.id, 'confirmed')"
              :disabled="updatingId === booking.id"
              class="btn confirm-btn"
            >
              {{ updatingId === booking.id ? 'Updating...' : 'Confirm' }}
            </button>
            
            <button 
              v-if="booking.status === 'pending' || booking.status === 'confirmed'"
              @click="booking.id && updateStatus(booking.id, 'cancelled')"
              :disabled="updatingId === booking.id"
              class="btn cancel-btn"
            >
              {{ updatingId === booking.id ? 'Updating...' : 'Cancel' }}
            </button>

            <button 
              @click="booking.id && deleteBooking(booking.id)"
              :disabled="deletingId === booking.id"
              class="btn delete-btn"
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
import type { Booking } from '@/types'

const router = useRouter()
const authStore = useAuthStore()

const bookings = ref<Booking[]>([])
const loading = ref(false)
const error = ref('')
const statusFilter = ref('')
const updatingId = ref<number | null>(null)
const deletingId = ref<number | null>(null)

const filteredBookings = computed(() => {
  if (!statusFilter.value) return bookings.value
  return bookings.value.filter(b => b.status === statusFilter.value)
})

const fetchBookings = async () => {
  loading.value = true
  error.value = ''
  try {
    const response = await api.get('/bookings')
    bookings.value = response.data.data || response.data
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to load bookings'
    console.error('Error fetching bookings:', err)
  } finally {
    loading.value = false
  }
}

const updateStatus = async (bookingId: number, status: string) => {
  if (!confirm(`Are you sure you want to ${status} this booking?`)) return

  updatingId.value = bookingId
  try {
    const response = await api.patch(`/bookings/${bookingId}/status`, { status })
    
    // Update local booking
    const index = bookings.value.findIndex(b => b.id === bookingId)
    if (index !== -1) {
      bookings.value[index] = response.data.data
    }
    
    alert(`Booking ${status} successfully!`)
  } catch (err: any) {
    const message = err.response?.data?.message || `Failed to ${status} booking`
    alert(message)
    console.error('Error updating status:', err)
  } finally {
    updatingId.value = null
  }
}

const deleteBooking = async (bookingId: number) => {
  if (!confirm('Are you sure you want to delete this booking? This action cannot be undone.')) return

  deletingId.value = bookingId
  try {
    await api.delete(`/bookings/${bookingId}`)
    bookings.value = bookings.value.filter(b => b.id !== bookingId)
    alert('Booking deleted successfully!')
  } catch (err: any) {
    const message = err.response?.data?.message || 'Failed to delete booking'
    alert(message)
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
})
</script>

<style scoped>
.admin-bookings-page {
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

.header-actions {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.link-btn {
  padding: 0.5rem 1rem;
  background: #3b82f6;
  color: white;
  text-decoration: none;
  border-radius: 0.375rem;
  font-weight: 500;
  transition: background 0.2s;
}

.link-btn:hover {
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

.filters-section {
  background: white;
  padding: 1.5rem;
  border-radius: 0.5rem;
  margin-bottom: 2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.filter-group label {
  font-weight: 500;
  color: #374151;
}

.filter-group select {
  padding: 0.5rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  font-size: 1rem;
  min-width: 200px;
}

.bookings-list-section {
  margin-top: 2rem;
}

.loading,
.error,
.empty {
  text-align: center;
  padding: 3rem;
  font-size: 1.125rem;
}

.error {
  color: #ef4444;
}

.bookings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
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

.booking-card.status-pending {
  border-left: 4px solid #f59e0b;
}

.booking-card.status-confirmed {
  border-left: 4px solid #10b981;
}

.booking-card.status-cancelled {
  border-left: 4px solid #ef4444;
}

.booking-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid #e5e7eb;
}

.booking-header h3 {
  margin: 0;
  font-size: 1.25rem;
  color: #1f2937;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 600;
  text-transform: uppercase;
}

.badge-pending {
  background: #fef3c7;
  color: #92400e;
}

.badge-confirmed {
  background: #d1fae5;
  color: #065f46;
}

.badge-cancelled {
  background: #fee2e2;
  color: #991b1b;
}

.booking-details {
  margin-bottom: 1rem;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid #f3f4f6;
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-row .label {
  font-weight: 500;
  color: #6b7280;
}

.detail-row .price {
  font-weight: bold;
  color: #3b82f6;
  font-size: 1.125rem;
}

.booking-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
}

.btn {
  flex: 1;
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 0.375rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.confirm-btn {
  background: #10b981;
  color: white;
}

.confirm-btn:hover:not(:disabled) {
  background: #059669;
}

.cancel-btn {
  background: #f59e0b;
  color: white;
}

.cancel-btn:hover:not(:disabled) {
  background: #d97706;
}

.delete-btn {
  background: #ef4444;
  color: white;
}

.delete-btn:hover:not(:disabled) {
  background: #dc2626;
}
</style>
