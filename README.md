# Cleaner Booking System

Simple booking system with Laravel API + Vue 3 frontend.

## Quick Start

### 1. Backend (Laravel)
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed

# Once the accounts are created you can simply :
npm run serve
```

### 2. Frontend (Vue 3)
```bash
cd frontend
npm install
npm run dev
```
### 3. Or start both servers at once
```bash
chmod +x start.sh
./start.sh 
```

**Access:** http://localhost:4200

---

## Test Credentials

### Admin
- **Email:** admin@example.com
- **Password:** password123

### Normal User
- **Email:** User@example.com
- **Password:** password123

---

## API Endpoints

**Base URL:** http://localhost:4000/api

### Auth
- `POST /register` - Register user
- `POST /login` - Login (get token)
- `POST /logout` - Logout
- `GET /user` - Current user

### Services
- `GET /services` - List all (public)
- `GET /services/{id}` - Show single service (public)
- `POST /services` - Create (admin)
- `PUT /services/{id}` - Update (admin)
- `DELETE /services/{id}` - Delete (admin)

### Bookings
- `GET /bookings` - List (user: own, admin: all)
- `POST /bookings` - Create booking
- `GET /bookings/{id}` - Show booking
- `PATCH /bookings/{id}/status` - Update status (admin)
- `PATCH /bookings/{id}/cancel` - Cancel (user)
- `DELETE /bookings/{id}` - Delete

---

## Tech Stack

**Backend:** Laravel 11, MySQL, Sanctum  
**Frontend:** Vue 3, TypeScript, Pinia, Vite
