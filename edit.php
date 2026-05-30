<?php
// ============================================================
// edit.php — Edit Data Customer
// ============================================================
session_start();
if (!isset($_SESSION['login'])) { header("Location: login.php"); exit; }
include 'koneksi.php';

$cid = mysqli_real_escape_string($conn, $_GET['id'] ?? '');
if (!$cid) { header("Location: customers.php"); exit; }

// Ambil data customer
$res = mysqli_query($conn, "SELECT * FROM customer_churn WHERE customerid='$cid' LIMIT 1");
if (!$res || mysqli_num_rows($res) === 0) {
    header("Location: customers.php");
    exit;
}
$c = mysqli_fetch_assoc($res);

$success = $error = '';

// ── Proses Update ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $age     = (int)$_POST['age'];
    $gender  = mysqli_real_escape_string($conn, $_POST['gender']);
    $tenure  = (int)$_POST['tenure'];
    $usage   = (int)$_POST['usage_frequency'];
    $calls   = (int)$_POST['support_calls'];
    $delay   = (int)$_POST['payment_delay'];
    $sub     = mysqli_real_escape_string($conn, $_POST['subscription_type']);
    $contract= mysqli_real_escape_string($conn, $_POST['contract_length']);
    $spend   = (float)$_POST['total_spend'];
    $last    = (int)$_POST['last_interaction'];
    $churn   = (int)$_POST['churn'];

    if (!$gender || !$sub || !$contract) {
        $error = 'Harap lengkapi semua field yang wajib diisi.';
    } elseif ($age < 18 || $age > 100) {
        $error = 'Usia harus antara 18 – 100 tahun.';
    } else {
        $sql = "UPDATE customer_churn SET
                  age=$age, gender='$gender', tenure=$tenure,
                  usage_frequency=$usage, support_calls=$calls,
                  payment_delay=$delay, subscription_type='$sub',
                  contract_length='$contract', total_spend=$spend,
                  last_interaction=$last, churn=$churn
                WHERE customerid='$cid'";

        if (mysqli_query($conn, $sql)) {
            // Refresh data
            $res = mysqli_query($conn, "SELECT * FROM customer_churn WHERE customerid='$cid' LIMIT 1");
            $c   = mysqli_fetch_assoc($res);
            $success = "Data customer <strong>$cid</strong> berhasil diperbarui!";
        } else {
            $error = "Gagal update: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Customer — CRM Analytics</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include 'partials/sidebar.php'; ?>

<div class="main">

  <div class="page-header">
    <div>
      <div class="page-title">Edit Customer</div>
      <div class="page-sub">Perbarui data untuk
        <strong style="color:#93c5fd;font-family:'JetBrains Mono',monospace;"><?= htmlspecialchars($cid) ?></strong>
      </div>
    </div>
    <a href="customers.php" class="btn-secondary">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
      Kembali
    </a>
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
      <?= $success ?>
      <a href="customers.php" style="color:#34d399;margin-left:10px;">→ Lihat Data</a>
    </div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert-error">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
      <?= $error ?>
    </div>
  <?php endif; ?>

  <div class="section-card" style="max-width:860px;">
    <div class="section-header">
      <div class="section-title">Form Edit Customer</div>
      <span class="section-badge">ID: <?= htmlspecialchars($cid) ?></span>
    </div>

    <form method="POST">
      <div class="form-grid">

        <div class="form-group">
          <label class="form-label">Customer ID</label>
          <input type="text" class="form-input" value="<?= htmlspecialchars($cid) ?>" disabled style="opacity:.5;">
        </div>

        <div class="form-group">
          <label class="form-label">Usia *</label>
          <input type="number" name="age" class="form-input" min="18" max="100" required value="<?= $c['age'] ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Gender *</label>
          <select name="gender" class="form-input" required>
            <option value="Male"   <?= $c['gender']==='Male'  ?'selected':'' ?>>Male</option>
            <option value="Female" <?= $c['gender']==='Female'?'selected':'' ?>>Female</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Tenure (Bulan)</label>
          <input type="number" name="tenure" class="form-input" min="0" value="<?= $c['tenure'] ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Usage Frequency</label>
          <input type="number" name="usage_frequency" class="form-input" min="0" value="<?= $c['usage_frequency'] ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Support Calls</label>
          <input type="number" name="support_calls" class="form-input" min="0" value="<?= $c['support_calls'] ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Payment Delay (Hari)</label>
          <input type="number" name="payment_delay" class="form-input" min="0" value="<?= $c['payment_delay'] ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Total Spend ($)</label>
          <input type="number" name="total_spend" class="form-input" min="0" step="0.01" value="<?= $c['total_spend'] ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Subscription Type *</label>
          <select name="subscription_type" class="form-input" required>
            <?php foreach (['Basic','Standard','Premium'] as $s): ?>
              <option value="<?= $s ?>" <?= $c['subscription_type']===$s?'selected':'' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Contract Length *</label>
          <select name="contract_length" class="form-input" required>
            <?php foreach (['Monthly','Quarterly','Annual'] as $ct): ?>
              <option value="<?= $ct ?>" <?= $c['contract_length']===$ct?'selected':'' ?>><?= $ct ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Last Interaction (Hari)</label>
          <input type="number" name="last_interaction" class="form-input" min="0" value="<?= $c['last_interaction'] ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Status Churn *</label>
          <select name="churn" class="form-input" required>
            <option value="0" <?= $c['churn']==0?'selected':'' ?>>Aktif (Tidak Churn)</option>
            <option value="1" <?= $c['churn']==1?'selected':'' ?>>Churn</option>
          </select>
        </div>

      </div>

      <div style="margin-top:26px; display:flex; gap:12px;">
        <button type="submit" class="btn-primary">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          Simpan Perubahan
        </button>
        <a href="customers.php" class="btn-secondary">Batal</a>
      </div>
    </form>
  </div>

</div><!-- /main -->
</body>
</html>
