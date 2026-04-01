# Lapor-Sekolah (v3.0 Production)

Lapor-Sekolah adalah portal pelaporan kerusakan fasilitas sekolah berbasis Web (Responsive Mobile-First) yang dikembangkan dengan PHP Native dan Bootstrap 5 (Dark Mode). Aplikasi ini dirancang untuk mempermudah civitas akademika (siswa/guru) dalam melaporkan kerusakan sarana prasarana sekolah secara mandiri, aman, dan transparan.

## ✨ Fitur Utama
- **Autentikasi Aman:** Menggunakan `password_hash()` dan *session guard pattern* untuk mencegah *ghost sessions*.
- **Role-Based Access Control (RBAC):** Sistem otorisasi memisahkan hak akses antara Admin dan User biasa.
- **Upload Gambar Aman:** Validasi tipe MIME dan ekstensi file yang ketat.
- **UI/UX Modern:** Tema Dark Mode dengan efek Glassmorphism, Micro-animations, dan tata letak dinamis menggunakan `clamp()` untuk responsivitas lintas platform (Desktop & Mobile).
- **Integrasi SweetAlert2:** Dialog yang ramah pengguna untuk notifikasi dan konfirmasi aksi kritis.

---

## 📂 Struktur Direktori

```text
/
├── config/                 # File inti konfigurasi (Production Ready)
│   ├── koneksi.php         # Koneksi ke database MySQL + set timezone
│   └── auth_check.php      # Guard pattern session & ghost session patch
├── uploads/                # Direktori penyimpanan foto bukti pelaporan (Read/Write)
├── assets/                 # (Opsional) Tempat penyimpanan icon/aset statis jika diperlukan
├── favicon.ico             # Ikon website (ditampilkan di tab browser)
├── index.php               # Landing Page (Dark Mode)
├── dashboard.php           # Tabel utama Laporan (User melihat miliknya, Admin melihat semua)
├── tambah.php              # Form User: Melaporkan kerusakan + Upload Foto
├── detail_laporan.php      # Form User (Read + Edit jika status 'Menunggu') serta Catatan Admin
├── edit.php                # Form Admin: Review data pelapor (Read-Only) & Ubah Status/Catatan
├── hapus.php               # Skrip aksi menghapus laporan beserta foto fisiknya
├── kelola_user.php         # Panel Admin: Daftar seluruh pengguna
├── hapus_user.php          # Skrip aksi menghapus pengguna (Laporan jadi Anonim berkat SET NULL)
├── login.php               # Halaman Login
├── register.php            # Halaman Registrasi (Otomatis role 'user')
├── logout.php              # Skrip destroy session dan redirect
└── README.md               # Dokumentasi proyek (File ini)
```

---

## 👥 Manajemen Role (Hak Akses)

Sistem memiliki dua tingkat hak akses (*Roles*):

### 1. User Biasa
Setiap akun yang mendaftar secara default menjadi **User**.
- **Dashboard:** Hanya dapat melihat daftar laporan yang ia buat sendiri.
- **Action:** Bisa menambah laporan baru.
- **Update:** Bisa membatalkan/mengubah detail dan foto laporannya **HANYA** ketika status laporan masih `"Menunggu"`. Begitu laporan `"Diproses"` atau `"Selesai"`, akses edit dikunci (*Read-Only*).
- **Security:** Tidak diizinkan mengakses panel Admin maupun data akun orang lain. Jika nekat mengetik URL Admin, sistem me-*redirect* kembali.

### 2. Admin
Hanya akun yang di-set `'admin'` di kolom `role` database yang memiliki hak ini.
- **Dashboard:** Melihat seluruh laporan dari semua pengguna (beserta data diri pelapor).
- **Action:** Berhak mengubah Status *Progress* Pelaporan (`Menunggu`, `Diproses`, `Selesai`) dan menambahkan **Catatan Admin** yang bisa dibaca ke pelapor. Tidak berhak mengubah isi pengaduan asli pelapor (*Read-Only form*).
- **Kelola Pengguna:** Admin memiliki akses ke halaman Kelola User untuk melihat semua pengguna terdaftar dan menghapus akun pengguna (namun laporan tetap tersimpan, label pengguna di laporan berubah jadi *Anonim*).
- **Data Privasi:** Bisa menghapus laporan secara permanen beserta file foto server (`unlink`).

---

## 🚀 Panduan Instalasi ke Server (Deployment)

Ikuti langkah-langkah di bawah untuk men-*deploy* aplikasi pada Web Server (Apache/Nginx) dan MySQL.

### Langkah 1: Persiapan Server
1. Download repository project Lapor-Sekolah.
2. Tempatkan seluruh direktori project ke dalam web direktori lokal server (misal di cPanel ke `public_html/` atau XAMPP ke `htdocs/lapor-sekolah`).
3. Pastikan direktori `uploads/` memiliki *Permission / CHMOD* hak akses **755** (atau `chmod -R 755 uploads`) agar web server bisa menulis/menyimpan gambar!

### Langkah 2: Setup Database
1. Buka antarmuka manajemen database seperti phpMyAdmin atau DBeaver.
2. Buat database baru bernama **`lapor_sekolah`**.
3. *Import* file SQL (`database.sql`, `update_database.sql`, dll) satu per satu secara berurutan ATAU kamu bisa langsung *import* struktur tabel final jika sudah kamu kompilasi menjadi satu file `export` .sql.
4. *Penting:* Pastikan tabel `laporan_kerusakan` memiliki *Foreign Key Constraint `ON DELETE SET NULL`* pada relasi `user_id` yang mengarah ke tabel `users`.

### Langkah 3: Konfigurasi Endpoint Koneksi
1. Buka folder `config/`.
2. Edit file `koneksi.php`.
3. Sesuaikan kredensial koneksi sesuai hosting server/lokal Anda:
```php
$host = "localhost";
$user = "u1234567_dbuser";  // Ganti dengan username database mu
$pass = "passwordku_kuat";  // Ganti dengan password database mu
$db   = "lapor_sekolah";    // Nama database
```

### Langkah 4: Membuat Akun Admin Pertama
Dikarenakan pendaftaran melalui `register.php` otomatis menjadi *User biasa*, kamu harus mengubah status role melalui database:
1. Akses web `http://domain-kamu/register.php` dan buatlah akun dengan mengisi nama lengkap, email, password.
2. Masuk ke phpMyAdmin / *SQL Console*, buka tabel `users`.
3. Cari akun yang barusan kamu daftarkan.
4. Ubah valuenya di kolom `role` (dari `"user"` diubah menjadi `"admin"`).
5. Silahkan *Login* menggunakan akun yang sudah di-elevasi tersebut, lalu kamu otomatis diarahkan ke Panel Admin.

### Langkah 5: Produksi (Going Live)
Sistem sekarang sudah bisa online dan dapat diakses publik. Nikmati fitur *Mobile Responsiveness* Lapor-Sekolah secara langsung melalui smartphone murid atau staff Anda!

---
*[Alfareza.site](https://alfareza.site)*
