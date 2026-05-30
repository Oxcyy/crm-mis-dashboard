<?php
// ============================================================
// koneksi.php — Konfigurasi koneksi database
// CRM Analytics MIS | customer_churn DB
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'customer_churn');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die(json_encode([
        'error' => true,
        'message' => 'Koneksi database gagal: ' . mysqli_connect_error()
    ]));
}

mysqli_set_charset($conn, 'utf8mb4');
