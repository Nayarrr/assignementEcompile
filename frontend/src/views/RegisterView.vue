<template>
  <div class="auth-form">
    <h2>Register</h2>
    <form @submit.prevent="handleSubmit">
      <div class="form-group">
        <label for="name">Name</label>
        <input
          id="name"
          v-model="form.name"
          type="text"
          required
          placeholder="Your name"
        />
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          required
          placeholder="your@email.com"
        />
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          required
          minlength="8"
          placeholder="Min 8 characters"
        />
      </div>
      <div class="form-group">
        <label for="password_confirmation">Confirm Password</label>
        <input
          id="password_confirmation"
          v-model="form.password_confirmation"
          type="password"
          required
          placeholder="Confirm password"
        />
      </div>
      <p v-if="error" class="error">{{ error }}</p>
      <button type="submit" :disabled="loading">
        {{ loading ? 'Registering...' : 'Register' }}
      </button>
    </form>
    <p class="switch-auth">
      Already have an account? <router-link to="/login">Login</router-link>
    </p>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import type { RegisterData } from '@/types'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive<RegisterData>({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const loading = ref(false)
const error = ref('')

async function handleSubmit() {
  error.value = ''
  
  if (form.password !== form.password_confirmation) {
    error.value = 'Passwords do not match'
    return
  }

  loading.value = true
  try {
    await authStore.register(form)
    router.push('/bookings')
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    error.value = err.response?.data?.message || 'Registration failed'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.auth-form {
  max-width: 400px;
  margin: 2rem auto;
  padding: 2rem;
  border: 1px solid #ddd;
  border-radius: 8px;
}

h2 {
  margin-bottom: 1.5rem;
  text-align: center;
}

.form-group {
  margin-bottom: 1rem;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
}

input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 1rem;
}

button {
  width: 100%;
  padding: 0.75rem;
  background-color: #4CAF50;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  cursor: pointer;
}

button:disabled {
  background-color: #ccc;
}

.error {
  color: red;
  margin-bottom: 1rem;
}

.switch-auth {
  text-align: center;
  margin-top: 1rem;
}
</style>
