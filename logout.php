<?php
// ============================================
// FILE: logout.php
// Deskripsi: Hapus semua session dan redirect
// ke halaman login
// ============================================

session_start();
session_destroy(); // Hapus semua data session
header('Location: login.php?pesan=logout');
exit;
?>
