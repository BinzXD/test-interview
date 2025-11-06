# 🧱 Laravel 11 API - Dockerized Setup

Project ini adalah backend API menggunakan **Laravel 11**, dikonfigurasi agar mudah dijalankan di **Docker** (PHP + MySQL).  

📄 **Dokumentasi API:**  
👉 [Lihat di Postman](https://documenter.getpostman.com/view/49571281/2sB3Wqw1Ph#2061d356-83db-42cd-961c-6b4d40ba87c3)

## ⚙️ Persiapan Sebelum Menjalankan

### 1️⃣ Download dan Install Docker
- [Docker Desktop](https://www.docker.com/products/docker-desktop/)
- [Git](https://git-scm.com/downloads)

### 2️⃣ Clone repository ini
```bash
lakukan git clone pada repo ini
git clone https://github.com/BinzXD/test-interview.git
lalu masuk kedalam folder projectnya
cd projectnya
copy env.example
rename menjadi .env

setelah itu jalankan
docker compose up -d
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan serve --host=0.0.0.0 --port=8000
Buka http://localhost:8000/
