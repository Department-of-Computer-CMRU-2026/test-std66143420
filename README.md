# 📚 Student Management System

ระบบจัดการข้อมูลนักเรียน สร้างด้วย **Laravel 12** + **Livewire 4** + **Flux UI** ใช้ฐานข้อมูล **PostgreSQL** และ Deploy บน Docker ผ่าน **GitHub Actions CI/CD**

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.4, Laravel 12, Laravel Fortify |
| Frontend | Livewire 4, Flux UI, Tailwind CSS 4, Vite 7 |
| Database | PostgreSQL 16 |
| Server | Nginx (Alpine), PHP-FPM |
| Container | Docker, Docker Compose |
| CI/CD | GitHub Actions |

---

## ✨ Features

- 🔐 ระบบสมัครสมาชิก / ล็อกอิน / ออกจากระบบ (Laravel Fortify)
- 👩‍🎓 จัดการข้อมูลนักเรียน (CRUD) — ชื่อ, รหัส, อีเมล, เบอร์โทร, สาขาวิชา, สถานะ
- 📁 Directory — ดูรายชื่อนักเรียนแบบ Directory
- ⚡ Real-time UI ด้วย Livewire (ไม่ต้อง Reload หน้า)
- 🐳 พร้อม Deploy ด้วย Docker Compose

---

## 🚀 Getting Started (Local Development)

### Prerequisites
- PHP >= 8.2
- Composer
- Node.js & npm
- Docker & Docker Compose (ถ้าต้องการรันผ่าน Container)

### 1. Clone โปรเจกต์
```bash
git clone https://github.com/<your-username>/<repo-name>.git
cd <repo-name>
```

### 2. ติดตั้ง Dependencies และ Setup อัตโนมัติ
```bash
composer run setup
```
คำสั่งนี้จะ:
- ติดตั้ง PHP dependencies (`composer install`)
- สร้างไฟล์ `.env`
- Generate `APP_KEY`
- รัน Migrations
- ติดตั้ง npm dependencies
- Build frontend assets

### 3. รัน Development Server
```bash
composer run dev
```
จากนั้นเปิด [http://localhost:8000](http://localhost:8000)

---

## 🐳 Deploy ด้วย Docker

### 1. ตั้งค่า `.env`
```bash
cp .env.example .env
```
แก้ไขค่าให้ตรงกับ Student ID ของคุณ:
```env
STUDENT_ID=std66xxxxxx
STUDENT_NAME=std66xxxxxx
STUDENT_PORT=6001          # พอร์ต Web ที่ต้องการเปิด
FORWARD_DB_PORT=6002       # พอร์ต Database ที่ต้องการเปิด
DB_DATABASE=db_name
DB_USERNAME=db_66xxxxxx
DB_PASSWORD=your_password
```

### 2. Start Containers
```bash
docker compose -p $STUDENT_NAME up -d --build
```

### 3. Post-Deploy (ครั้งแรก)
```bash
docker exec <STUDENT_NAME>-app php artisan key:generate
docker exec <STUDENT_NAME>-app php artisan migrate --force
docker exec <STUDENT_NAME>-app npm install
docker exec <STUDENT_NAME>-app npm run build
```

เปิดเบราว์เซอร์ที่ `http://localhost:<STUDENT_PORT>`

---

## ⚙️ GitHub Actions (CI/CD)

โปรเจกต์มีการ Deploy อัตโนมัติทุกครั้งที่ Push ขึ้น branch `main` หรือ `master`

**Pipeline จะทำงานดังนี้:**
1. ⚙️ ตั้งค่า Environment จาก `.env.example`
2. 📦 ติดตั้ง PHP dependencies (`composer install`)
3. 🏗️ Build frontend assets (`npm install && npm run build`)
4. 🐳 Deploy ด้วย `docker compose up -d --build`
5. 🔧 Post-deploy: แก้สิทธิ์ไฟล์, generate key, migrate database

---

## 📁 Project Structure

```
├── app/
│   ├── Actions/         # Business Logic (Fortify Actions)
│   ├── Livewire/        # Livewire Components
│   ├── Models/          # Eloquent Models (User, Student)
│   └── Providers/       # Service Providers
├── database/
│   └── migrations/      # Database Migrations
├── resources/views/     # Blade & Livewire Views
├── routes/
│   ├── web.php          # Web Routes (Dashboard, Students, Directory)
│   └── settings.php     # Settings Routes
├── docker/              # Nginx Config
├── docker-compose.yml   # Docker Compose Configuration
├── Dockerfile           # PHP-FPM Application Image
└── .github/workflows/
    └── deploy.yml       # GitHub Actions CI/CD Pipeline
```

---

## 📋 Useful Commands

```bash
# รัน tests
composer run test

# Code linting
composer run lint

# Laravel Artisan
php artisan migrate:fresh --seed   # Reset และ Seed ฐานข้อมูล
php artisan cache:clear            # ล้าง Cache
php artisan config:clear           # ล้าง Config Cache
```

---

## 👨‍💻 ผู้พัฒนา (Developer)

**Zismail (Zismaildev)** *Computer Science, Chiang Mai Rajabhat University*

- **GitHub:** [@Zismaildev](https://github.com/Zismaildev)
- **Skill Tree:** Full-stack Developer & Cybersecurity Enthusiast
- **University:** Chiang Mai Rajabhat University (CMRU)
