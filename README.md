# REST API with JWT Authentication

This is a secure **RESTful API** built with **JWT (JSON Web Token)** authentication.  
It provides user registration, login/logout, analytics, and product/order management with data seeding.
 And Below link is API Documentaion https://documenter.getpostman.com/view/29612754/2sB3WpR1J8
---

## 🧱 Requirements

Make sure you have the following installed before setup:

- **PHP 8.1 or higher**
- **Composer 2+**
- **MySQL / MariaDB**
- **Node.js & npm** *(optional for frontend assets)*
- **Git**

---

## 📥 1. Clone the Repository

```bash
[git clone https://github.com/your-username/laravel10-jwt-api.git
cd laravel10-jwt-api](https://github.com/Binod-123/orders_analytical_api.git)
```

---

## ⚙️ 2. Install Dependencies

Install all PHP dependencies:

```bash
composer install
```

(Optional: if you have frontend assets)
```bash
npm install
npm run dev
```

---

## ⚙️ 3. Configure Environment

Create a copy of the example environment file:

```bash
cp .env.example .env
```

Now edit `.env` with your local database credentials:

## 🔐 4. Generate Application Key

```bash
php artisan key:generate
```

---

## 🔑 5. Generate JWT Secret Key

JWT authentication uses a secret key for token signing. Generate it with:

```bash
php artisan jwt:secret
```

This command will add a new `JWT_SECRET` value inside your `.env` file.

---

## 🧩 6. Run Database Migrations

Run all migrations to create database tables:

```bash
php artisan migrate
```

---

## 🌱 7. Seed the Database

### Normal seed:
```bash
php artisan db:seed
```

### Fresh migration and seed:
```bash
php artisan migrate:fresh --seed
```

This will drop all existing tables, re-run all migrations, and populate the database with demo data (users, products, orders, etc.).

---

## ▶️ 8. Start the Development Server

Run the Laravel development server:

```bash
php artisan serve
```

The API will be available at:
👉 [http://localhost:8000](http://localhost:8000)

---




