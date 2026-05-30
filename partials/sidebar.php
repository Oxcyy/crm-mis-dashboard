<?php
// Tentukan halaman aktif
$current = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="brand-logo">
      <svg viewBox="0 0 24 24"><path d="M3 3h7v7H3V3zm11 0h7v7h-7V3zm0 11h7v7h-7v-7zM3 14h7v7H3v-7z"/></svg>
    </div>
    <div>
      <div class="brand-name">CRM Analytics</div>
      <div class="brand-sub">MIS Dashboard</div>
    </div>
  </div>

  <div class="nav-label">Menu Utama</div>

  <a href="index.php" class="nav-link <?= $current==='index.php' ? 'active' : '' ?>">
    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
    Dashboard
  </a>
  <a href="customers.php" class="nav-link <?= $current==='customers.php' ? 'active' : '' ?>">
    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    Data Customer
  </a>
  <a href="analytics.php" class="nav-link <?= $current==='analytics.php' ? 'active' : '' ?>">
    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
    Churn Analysis
  </a>
  <a href="tambah.php" class="nav-link <?= $current==='tambah.php' ? 'active' : '' ?>">
    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
    Tambah Customer
  </a>

  <div class="sidebar-footer">
    <div class="user-row">
      <div class="user-avatar"><?= strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)) ?></div>
      <div>
        <div class="user-name"><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></div>
        <div class="user-role"><?= htmlspecialchars($_SESSION['role'] ?? 'Administrator') ?></div>
      </div>
      <a href="logout.php" class="logout-btn" title="Logout">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      </a>
    </div>
  </div>
</aside>
