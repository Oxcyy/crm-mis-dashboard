<?php
// ============================================================
// tambah.php — Tambah Customer Baru ke Database
// ============================================================
session_start();
if (!isset($_SESSION['login'])) { header("Location: login.php"); exit; }
include 'koneksi.php';

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id      = mysqli_real_escape_string($conn, trim($_POST['customerid']));
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

    // Validasi
    if (!$id || !$gender || !$sub || !$contract) {
        $error = 'Harap lengkapi semua field yang wajib diisi.';
    } elseif ($age < 18 || $age > 100) {
        $error = 'Usia harus antara 18 – 100 tahun.';
    } else {
        $sql = "INSERT INTO customer_churn
                  (customerid, age, gender, tenure, usage_frequency, support_calls,
                   payment_delay, subscription_type, contract_length, total_spend, last_interaction, churn)
                VALUES
                  ('$id', $age, '$gender', $tenure, $usage, $calls,
                   $delay, '$sub', '$contract', $spend, $last, $churn)";

        if (mysqli_query($conn, $sql)) {
            $success = "Customer <strong>$id</strong> berhasil ditambahkan ke database!";
        } elseif (mysqli_errno($conn) === 1062) {
            $error = "Customer ID <strong>$id</strong> sudah ada. Gunakan ID yang berbeda.";
        } else {
            $error = "Gagal menyimpan: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Customer — CRM Analytics</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include 'partials/sidebar.php'; ?>

<div class="main">

  <div class="page-header">
    <div>
      <div class="page-title">Tambah Customer</div>
      <div class="page-sub">Input data customer baru ke database</div>
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
      <div class="section-title">Form Data Customer</div>
      <span class="section-badge">* Wajib diisi</span>
    </div>

    <form method="POST">
      <div class="form-grid">

        <div class="form-group">
          <label class="form-label">Customer ID *</label>
          <input type="text" name="customerid" class="form-input" placeholder="Contoh: CUST5001" required
                 value="<?= htmlspecialchars($_POST['customerid'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Usia *</label>
          <input type="number" name="age" class="form-input" placeholder="18 – 100" min="18" max="100" required
                 value="<?= htmlspecialchars($_POST['age'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Gender *</label>
          <select name="gender" class="form-input" required>
            <option value="">Pilih Gender</option>
            <option value="Male"   <?= ($_POST['gender']??'')==='Male'  ?'selected':'' ?>>Male</option>
            <option value="Female" <?= ($_POST['gender']??'')==='Female'?'selected':'' ?>>Female</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Tenure (Bulan)</label>
          <input type="number" name="tenure" class="form-input" placeholder="Lama berlangganan" min="0"
                 value="<?= htmlspecialchars($_POST['tenure'] ?? '0') ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Usage Frequency</label>
          <input type="number" name="usage_frequency" class="form-input" placeholder="Frekuensi penggunaan" min="0"
                 value="<?= htmlspecialchars($_POST['usage_frequency'] ?? '0') ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Support Calls</label>
          <input type="number" name="support_calls" class="form-input" placeholder="Jumlah panggilan support" min="0"
                 value="<?= htmlspecialchars($_POST['support_calls'] ?? '0') ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Payment Delay (Hari)</label>
          <input type="number" name="payment_delay" class="form-input" placeholder="Keterlambatan bayar" min="0"
                 value="<?= htmlspecialchars($_POST['payment_delay'] ?? '0') ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Total Spend ($)</label>
          <input type="number" name="total_spend" class="form-input" placeholder="Total pengeluaran" min="0" step="0.01"
                 value="<?= htmlspecialchars($_POST['total_spend'] ?? '0') ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Subscription Type *</label>
          <select name="subscription_type" class="form-input" required>
            <option value="">Pilih Tipe</option>
            <?php foreach (['Basic','Standard','Premium'] as $s): ?>
              <option value="<?= $s ?>" <?= ($_POST['subscription_type']??'')===$s?'selected':'' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Contract Length *</label>
          <select name="contract_length" class="form-input" required>
            <option value="">Pilih Kontrak</option>
            <?php foreach (['Monthly','Quarterly','Annual'] as $c): ?>
              <option value="<?= $c ?>" <?= ($_POST['contract_length']??'')===$c?'selected':'' ?>><?= $c ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Last Interaction (Hari)</label>
          <input type="number" name="last_interaction" class="form-input" placeholder="Hari sejak interaksi terakhir" min="0"
                 value="<?= htmlspecialchars($_POST['last_interaction'] ?? '0') ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Status Churn *</label>
          <select name="churn" class="form-input" required>
            <option value="">Pilih Status</option>
            <option value="0" <?= ($_POST['churn']??'')==='0'?'selected':'' ?>>Aktif (Tidak Churn)</option>
            <option value="1" <?= ($_POST['churn']??'')==='1'?'selected':'' ?>>Churn</option>
          </select>
        </div>

      </div><!-- /form-grid -->

      <div style="margin-top:26px; display:flex; gap:12px;">
        <button type="submit" class="btn-primary">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          Simpan Customer
        </button>
        <a href="customers.php" class="btn-secondary">Batal</a>
      </div>
    </form>
  </div>

</div><!-- /main -->
</body>
</html>
