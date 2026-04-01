# 📘 Panduan Deploy: Lapor-Sekolah

## Struktur File Project

```
Php App/
├── koneksi.php       ← Konfigurasi database
├── index.php         ← Halaman utama (Read)
├── tambah.php        ← Form + proses tambah (Create)
├── edit.php          ← Form + proses edit (Update)
├── hapus.php         ← Proses hapus (Delete)
├── database.sql      ← Query buat database & tabel
└── uploads/
    └── .htaccess     ← Keamanan folder upload
```

---

## Langkah 1: Import Database

Buka **phpMyAdmin** (http://localhost/phpmyadmin) atau gunakan terminal:

```bash
# Via terminal MySQL
mysql -u root -p < database.sql
```

---

## Langkah 2: Konfigurasi koneksi.php

Buka `koneksi.php` dan sesuaikan:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');         // username MySQL kamu
define('DB_PASS', 'passwordkamu'); // password MySQL kamu
define('DB_NAME', 'lapor_sekolah');
```

---

## Langkah 3: Deploy ke CentOS / Apache

```bash
# Salin seluruh folder project ke web root
cp -r /path/ke/project/* /var/www/html/lapor-sekolah/

# Buat dan atur izin folder uploads
mkdir -p /var/www/html/lapor-sekolah/uploads
chown apache:apache /var/www/html/lapor-sekolah/uploads
chmod 755 /var/www/html/lapor-sekolah/uploads

# Pastikan SELinux tidak memblokir upload (jika aktif)
setsebool -P httpd_unified 1
# atau
restorecon -Rv /var/www/html/lapor-sekolah/uploads
```

---

## Langkah 4: Akses Aplikasi

Buka browser dan akses:

```
http://IP-SERVER-KAMU/lapor-sekolah/
```

---

## Ringkasan Fitur Keamanan

| Fitur | Implementasi |
|---|---|
| SQL Injection | `mysqli_real_escape_string()` |
| XSS (Cross-Site Scripting) | `htmlspecialchars()` |
| Validasi File Upload | Cek ekstensi + MIME type asli via `finfo` |
| Batas Ukuran File | Maks 5 MB |
| Eksekusi PHP di folder uploads | Diblokir via `.htaccess` |
| Directory Listing | Dinonaktifkan (`Options -Indexes`) |

---

## Troubleshooting

| Masalah | Solusi |
|---|---|
| Foto tidak tampil | Pastikan folder `uploads/` bisa dibaca oleh Apache (`chmod 755`) |
| Gagal upload foto | Cek izin folder (`chown apache:apache uploads/`) |
| Koneksi database error | Periksa username/password di `koneksi.php` |
| Error 500 | Cek log Apache: `tail -f /var/log/httpd/error_log` |
| SELinux memblokir | Jalankan `setsebool -P httpd_unified 1` |
