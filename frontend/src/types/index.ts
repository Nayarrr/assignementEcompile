export interface User {
  id: number
  name: string
  email: string
  is_admin: boolean
  created_at?: string
  updated_at?: string
}

export interface Service {
  id: number
  title: string
  description: string | null
  price: number
  created_at?: string
  updated_at?: string
}

export type BookingStatus = 'pending' | 'confirmed' | 'cancelled'

export interface Booking {
  id?: number
  user_id?: number
  service_id: number
  customer_name: string
  address: string
  date: string
  time: string
  status?: BookingStatus
  status_label?: string
  can_cancel?: boolean
  can_confirm?: boolean
  allowed_transitions?: BookingStatus[]
  service?: Service
  user?: User
  created_at?: string
  updated_at?: string
}

export interface LoginCredentials {
  email: string
  password: string
}

export interface RegisterData {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export interface ApiResponse<T> {
  message?: string
  data: T
}
