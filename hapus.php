<?php
// hapus.php — redirect ke customers.php dengan param hapus
session_start();
if (!isset($_SESSION['login'])) { header("Location: login.php"); exit; }
$id = $_GET['id'] ?? '';
header("Location: customers.php?hapus=" . urlencode($id));
exit;
