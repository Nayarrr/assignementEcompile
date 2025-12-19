# Cleaner Booking System

Simple booking system with Laravel API + Vue 3 frontend.

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+

### 1. Clone Repository
```bash
git clone https://github.com/Nayarrr/assignementEcompile
cd assignementEcompile
```

### 2. Database Setup

**Create MySQL database and user:**

```bash
mysql -u root -p
```

```sql
CREATE DATABASE cleaner_booking;
CREATE USER 'cleaner_user'@'localhost' IDENTIFIED BY 'password123';
GRANT ALL PRIVILEGES ON cleaner_booking.* TO 'cleaner_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Backend Setup

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

**Edit `.env` and verify database configuration:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cleaner_booking
DB_USERNAME=cleaner_user
DB_PASSWORD=password123
```

**Run migrations:**
```bash
php artisan migrate:fresh --seed
```

**Start backend server:**
```bash
npm run serve
```
Backend will run on http://localhost:4000

**Start the entire app:**

```bash
./start.sh
```

### 4. Frontend Setup

**In a new terminal:**
```bash
cd frontend
npm install
npm run dev
```
Frontend will run on http://localhost:4200

---

**Admin Account:**
- Email: `admin@example.com`
- Password: `password123`

**Regular User:**
- Email: `user@example.com`
- Password: `password123`

---

## 🔗 API Endpoints

### Authentication
- `POST /register` - Register new user
- `POST /login` - Login (returns Bearer token)
- `POST /logout` - Logout (requires auth)
- `GET /user` - Get current user (requires auth)

### Services (Public)
- `GET /services` - List all services
- `GET /services/{id}` - Show single service

### Services (Admin Only)
- `POST /services` - Create service
- `PUT /services/{id}` - Update service
- `DELETE /services/{id}` - Delete service

### Bookings (Authenticated Users)
- `GET /bookings` - List bookings (user: own bookings, admin: all bookings)
- `POST /bookings` - Create new booking
- `GET /bookings/{id}` - Show booking details
- `PATCH /bookings/{id}/cancel` - Cancel booking
- `DELETE /bookings/{id}` - Delete booking

### Bookings (Admin Only)
- `PATCH /bookings/{id}/status` - Update booking status (pending/confirmed/cancelled)

---

## 🛠️ Tech Stack

**Backend:**
- Laravel 11
- MySQL 8.0
- Laravel Sanctum (API Authentication)

**Frontend:**
- Vue 3 (Composition API)
- TypeScript
- Pinia (State Management)
- Vite (Build Tool)

---