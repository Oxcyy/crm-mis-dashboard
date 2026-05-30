<?php
// ============================================================
// login.php — Halaman Login CRM Analytics
// ============================================================
session_start();

if (isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if (isset($_POST['login'])) {
    include 'koneksi.php';

    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = $_POST['password'];

    // Cek di tabel users (password SHA2-256)
    $hash = hash('sha256', $password);
    $sql  = "SELECT * FROM users WHERE username = '$username' AND password = '$hash' LIMIT 1";
    $res  = mysqli_query($conn, $sql);

    if ($res && mysqli_num_rows($res) > 0) {
        $user = mysqli_fetch_assoc($res);
        $_SESSION['login']    = true;
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];
        header("Location: index.php");
        exit;
    } else {
        // Fallback: hardcoded admin (untuk kemudahan demo)
        if ($username === 'admin' && $password === 'admin123') {
            $_SESSION['login']    = true;
            $_SESSION['username'] = 'admin';
            $_SESSION['role']     = 'admin';
            header("Location: index.php");
            exit;
        }
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — CRM Analytics MIS</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root {
  --bg: #06080f; --surface: #0b0f1c; --card: #0f1525;
  --accent: #3b82f6; --accent2: #06b6d4;
  --danger: #ef4444; --text: #e2e8f0; --text2: #94a3b8; --muted: #475569;
  --border2: rgba(255,255,255,0.05);
}
* { margin:0; padding:0; box-sizing:border-box; }
body {
  font-family:'Space Grotesk',sans-serif;
  background:var(--bg); color:var(--text);
  min-height:100vh; display:flex;
  align-items:center; justify-content:center;
}
body::before {
  content:''; position:fixed; inset:0;
  background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");
  pointer-events:none; z-index:0; opacity:0.4;
}
.login-wrap {
  position:relative; z-index:1;
  background:var(--card);
  border:1px solid var(--border2);
  border-radius:20px;
  padding:44px 40px;
  width:100%; max-width:380px;
  box-shadow:0 25px 60px rgba(0,0,0,0.5);
  animation:fadeUp 0.4s ease;
}
@keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
.login-logo {
  width:48px; height:48px;
  background:linear-gradient(135deg,var(--accent),var(--accent2));
  border-radius:14px; display:flex; align-items:center; justify-content:center;
  margin-bottom:20px;
}
.login-logo svg { width:24px; height:24px; fill:white; }
.login-title { font-size:22px; font-weight:700; letter-spacing:-0.5px; margin-bottom:4px; }
.login-sub { font-size:13px; color:var(--muted); margin-bottom:28px; }
.form-label { display:block; font-size:11px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:0.8px; margin-bottom:8px; }
.form-input {
  width:100%; background:rgba(255,255,255,0.03);
  border:1px solid var(--border2); border-radius:10px;
  padding:12px 14px; color:var(--text); font-size:14px;
  font-family:'Space Grotesk',sans-serif; outline:none;
  transition:all 0.2s; margin-bottom:16px;
}
.form-input:focus { border-color:rgba(59,130,246,0.4); background:rgba(59,130,246,0.04); box-shadow:0 0 0 3px rgba(59,130,246,0.1); }
.form-input::placeholder { color:var(--muted); }
.btn-login {
  width:100%; padding:13px; background:var(--accent);
  color:white; border:none; border-radius:10px;
  font-size:14px; font-weight:600; font-family:'Space Grotesk',sans-serif;
  cursor:pointer; transition:all 0.2s; margin-top:4px;
}
.btn-login:hover { background:#2563eb; box-shadow:0 6px 20px rgba(59,130,246,0.4); }
.alert-error {
  background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.25);
  color:#f87171; border-radius:10px; padding:12px 14px;
  font-size:13px; margin-bottom:18px;
}
.hint { font-size:11px; color:var(--muted); text-align:center; margin-top:18px; }
.hint span { color:var(--text2); font-family:'JetBrains Mono',monospace; }
</style>
</head>
<body>
<div class="login-wrap">
  <div class="login-logo">
    <svg viewBox="0 0 24 24"><path d="M3 3h7v7H3V3zm11 0h7v7h-7V3zm0 11h7v7h-7v-7zM3 14h7v7H3v-7z"/></svg>
  </div>
  <div class="login-title">CRM Analytics</div>
  <div class="login-sub">MIS Dashboard — Universitas Mulawarman</div>

  <?php if ($error): ?>
    <div class="alert-error">⚠ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <label class="form-label">Username</label>
    <input type="text" name="username" class="form-input" placeholder="Masukkan username" autocomplete="username" required>

    <label class="form-label">Password</label>
    <input type="password" name="password" class="form-input" placeholder="Masukkan password" autocomplete="current-password" required>

    <button type="submit" name="login" class="btn-login">Masuk ke Dashboard</button>
  </form>

  <div class="hint">Default login: <span>admin</span> / <span>admin123</span></div>
</div>
</body>
</html>
