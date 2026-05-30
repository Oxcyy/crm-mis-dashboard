<?php
// ============================================================
// customers.php — Data Customer dari Database
// ============================================================
session_start();
if (!isset($_SESSION['login'])) { header("Location: login.php"); exit; }
include 'koneksi.php';

// ── Hapus customer ──────────────────────────────────────────
$msg = '';
if (isset($_GET['hapus'])) {
    $hid = mysqli_real_escape_string($conn, $_GET['hapus']);
    $del = mysqli_query($conn, "DELETE FROM customer_churn WHERE customerid='$hid'");
    $msg = $del
        ? '<div class="alert alert-success">✓ Customer <strong>' . htmlspecialchars($hid) . '</strong> berhasil dihapus.</div>'
        : '<div class="alert alert-error">✗ Gagal menghapus customer.</div>';
}

// ── Filter & Search ─────────────────────────────────────────
$search   = mysqli_real_escape_string($conn, trim($_GET['q']   ?? ''));
$filterSub   = mysqli_real_escape_string($conn, $_GET['sub']   ?? '');
$filterChurn = isset($_GET['churn']) && $_GET['churn'] !== '' ? (int)$_GET['churn'] : '';

$where = "WHERE 1=1";
if ($search)            $where .= " AND (customerid LIKE '%$search%' OR gender LIKE '%$search%')";
if ($filterSub !== '')  $where .= " AND subscription_type = '$filterSub'";
if ($filterChurn !== '') $where .= " AND churn = $filterChurn";

// ── Pagination ───────────────────────────────────────────────
$perPage = 15;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$totalRes = mysqli_query($conn, "SELECT COUNT(*) as c FROM customer_churn $where");
$total    = mysqli_fetch_assoc($totalRes)['c'];
$pages    = ceil($total / $perPage);

$rows = mysqli_query($conn,
    "SELECT * FROM customer_churn $where ORDER BY id DESC LIMIT $perPage OFFSET $offset"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Customer — CRM Analytics</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include 'partials/sidebar.php'; ?>

<div class="main">

  <div class="page-header">
    <div>
      <div class="page-title">Data Customer</div>
      <div class="page-sub">Total <strong style="color:var(--text);"><?= number_format($total) ?></strong> customer ditemukan</div>
    </div>
    <a href="tambah.php" class="btn-primary">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Tambah Customer
    </a>
  </div>

  <?= $msg ?>

  <!-- Filter Bar -->
  <div class="section-card" style="padding:14px 18px; margin-bottom:14px;">
    <form method="GET" class="filter-bar">
      <div class="search-input">
        <svg class="search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" name="q" placeholder="Cari customer ID / gender..." value="<?= htmlspecialchars($search) ?>">
      </div>
      <select name="sub" class="form-select" style="width:150px;">
        <option value="">Semua Tipe</option>
        <?php foreach (['Basic','Standard','Premium'] as $s): ?>
          <option value="<?= $s ?>" <?= $filterSub===$s ? 'selected':'' ?>><?= $s ?></option>
        <?php endforeach; ?>
      </select>
      <select name="churn" class="form-select" style="width:140px;">
        <option value="">Semua Status</option>
        <option value="0" <?= $filterChurn===0 ? 'selected':'' ?>>Aktif</option>
        <option value="1" <?= $filterChurn===1 ? 'selected':'' ?>>Churn</option>
      </select>
      <button type="submit" class="btn-primary" style="padding:8px 16px;font-size:12px;">Filter</button>
      <a href="customers.php" class="btn-secondary" style="padding:8px 14px;font-size:12px;">Reset</a>
    </form>
  </div>

  <!-- Tabel -->
  <div class="section-card">
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th><th>Customer ID</th><th>Gender</th><th>Usia</th>
            <th>Tenure</th><th>Subscription</th><th>Contract</th>
            <th>Total Spend</th><th>Support Calls</th><th>Payment Delay</th>
            <th>Status</th><th>Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php if (mysqli_num_rows($rows) === 0): ?>
          <tr><td colspan="12" style="text-align:center;color:var(--muted);padding:32px;">Tidak ada data customer.</td></tr>
        <?php else: $no = $offset + 1; while ($r = mysqli_fetch_assoc($rows)): ?>
          <?php
            $subClass  = strtolower($r['subscription_type']);
            $callColor = $r['support_calls'] >= 7 ? '#f87171' : ($r['support_calls'] >= 4 ? '#fbbf24' : '#34d399');
            $delayColor= $r['payment_delay'] >= 20 ? '#f87171' : ($r['payment_delay'] >= 10 ? '#fbbf24' : '#34d399');
          ?>
          <tr>
            <td style="color:var(--muted);font-size:11px;"><?= $no++ ?></td>
            <td><strong style="color:#93c5fd;font-family:'JetBrains Mono',monospace;font-size:12px;"><?= htmlspecialchars($r['customerid']) ?></strong></td>
            <td><?= htmlspecialchars($r['gender']) ?></td>
            <td><?= $r['age'] ?></td>
            <td><?= $r['tenure'] ?> bln</td>
            <td><span class="badge badge-<?= $subClass ?>"><?= htmlspecialchars($r['subscription_type']) ?></span></td>
            <td style="font-size:12px;"><?= htmlspecialchars($r['contract_length']) ?></td>
            <td><strong>$<?= number_format($r['total_spend'], 2) ?></strong></td>
            <td><span style="color:<?= $callColor ?>;font-weight:600;"><?= $r['support_calls'] ?>x</span></td>
            <td><span style="color:<?= $delayColor ?>;font-weight:600;"><?= $r['payment_delay'] ?> hr</span></td>
            <td><?= $r['churn'] ? '<span class="badge badge-churn">Churn</span>' : '<span class="badge badge-active">Aktif</span>' ?></td>
            <td>
              <a href="edit.php?id=<?= urlencode($r['customerid']) ?>" class="action-btn btn-edit">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit
              </a>
              <a href="customers.php?hapus=<?= urlencode($r['customerid']) ?>&<?= http_build_query(array_filter(['q'=>$search,'sub'=>$filterSub,'churn'=>$filterChurn,'page'=>$page])) ?>"
                 class="action-btn btn-delete"
                 onclick="return confirm('Hapus customer <?= htmlspecialchars($r['customerid']) ?>?')">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
              </a>
            </td>
          </tr>
        <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($pages > 1): ?>
    <div class="pagination">
      <?php
        $qs = array_filter(['q'=>$search,'sub'=>$filterSub,'churn'=>$filterChurn!=='' ? $filterChurn : null]);
        $start = max(1, $page - 2);
        $end   = min($pages, $page + 2);
        if ($page > 1):
      ?>
        <a href="?<?= http_build_query(array_merge($qs,['page'=>$page-1])) ?>" class="page-btn">‹</a>
      <?php endif; ?>
      <?php for ($i = $start; $i <= $end; $i++): ?>
        <a href="?<?= http_build_query(array_merge($qs,['page'=>$i])) ?>"
           class="page-btn <?= $i===$page ? 'active':'' ?>"><?= $i ?></a>
      <?php endfor; ?>
      <?php if ($page < $pages): ?>
        <a href="?<?= http_build_query(array_merge($qs,['page'=>$page+1])) ?>" class="page-btn">›</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>

</div><!-- /main -->
</body>
</html>
