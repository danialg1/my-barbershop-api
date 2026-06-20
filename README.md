# ⚙️ My Barbershop - Backend API

RESTful API yang melayani seluruh alur data aplikasi My Barbershop. Dibangun menggunakan **PHP Native** dan database **MySQL**.

## ✨ Fitur Utama
* **CRUD Layanan & Pengguna:** Manajemen data dari panel Admin.
* **Midtrans Webhook:** Menangkap dan memproses status pembayaran (Settlement, Expire, Cancel) secara otomatis.
* **Sistem Notifikasi Email:** Mengirim kode OTP dan notifikasi reservasi menggunakan PHPMailer.
* **Manajemen Jadwal Barber:** Endpoint khusus untuk mengatur pesanan masuk.

## 🚀 Teknologi & Keamanan
* Bahasa: PHP Native
* Database: MySQL
* Keamanan: Password Hashing (Bcrypt) & Environment Variable (config.php) disembunyikan via .gitignore.
