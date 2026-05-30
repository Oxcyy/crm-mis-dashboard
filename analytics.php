<?php
// ============================================================
// analytics.php — Halaman Analisis Churn Customer
// ============================================================
session_start();
if (!isset($_SESSION['login'])) { header("Location: login.php"); exit; }
include 'koneksi.php';

// ── Statistik Utama ─────────────────────────────────────────
$total  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM customer_churn"))['c'];
$churnN = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM customer_churn WHERE churn=1"))['c'];
$aktif  = $total - $churnN;
$rate   = $total > 0 ? round($churnN / $total * 100, 1) : 0;

// KPI Churn segment
$avgAge   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT ROUND(AVG(age),1) as v FROM customer_churn WHERE churn=1"))['v'];
$avgCalls = mysqli_fetch_assoc(mysqli_query($conn, "SELECT ROUND(AVG(support_calls),1) as v FROM customer_churn WHERE churn=1"))['v'];
$avgDelay = mysqli_fetch_assoc(mysqli_query($conn, "SELECT ROUND(AVG(payment_delay),1) as v FROM customer_churn WHERE churn=1"))['v'];
$avgTenure= mysqli_fetch_assoc(mysqli_query($conn, "SELECT ROUND(AVG(tenure),1) as v FROM customer_churn WHERE churn=1"))['v'];
$avgSpendChurn = mysqli_fetch_assoc(mysqli_query($conn, "SELECT ROUND(AVG(total_spend),2) as v FROM customer_churn WHERE churn=1"))['v'];
$avgSpendAktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT ROUND(AVG(total_spend),2) as v FROM customer_churn WHERE churn=0"))['v'];

// ── Chart: Churn per Subscription ───────────────────────────
$subRes = mysqli_query($conn, "
    SELECT subscription_type,
           COUNT(*) as total,
           SUM(churn) as churned
    FROM customer_churn
    GROUP BY subscription_type
    ORDER BY FIELD(subscription_type,'Basic','Standard','Premium')
");
$subLabels = $subTotal = $subChurn = [];
while ($r = mysqli_fetch_assoc($subRes)) {
    $subLabels[] = $r['subscription_type'];
    $subTotal[]  = (int)$r['total'];
    $subChurn[]  = (int)$r['churned'];
}

// ── Chart: Churn per Gender ──────────────────────────────────
$genRes = mysqli_query($conn, "
    SELECT gender, COUNT(*) as total, SUM(churn) as churned
    FROM customer_churn GROUP BY gender
");
$genLabels = $genChurn = $genAktif = [];
while ($r = mysqli_fetch_assoc($genRes)) {
    $genLabels[] = $r['gender'];
    $genChurn[]  = (int)$r['churned'];
    $genAktif[]  = (int)$r['total'] - (int)$r['churned'];
}

// ── Chart: Churn per Age Group ───────────────────────────────
$ageGroups = [['< 25',0,25],['25–34',25,35],['35–44',35,45],['45–54',45,55],['55+',55,999]];
$ageLabels = $ageChurn = $ageAktif = [];
foreach ($ageGroups as [$label, $lo, $hi]) {
    $cond = $hi === 999 ? "age >= $lo" : "age >= $lo AND age < $hi";
    $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t, SUM(churn) as ch FROM customer_churn WHERE $cond"));
    $ageLabels[] = $label;
    $ageChurn[]  = (int)$r['ch'];
    $ageAktif[]  = (int)$r['t'] - (int)$r['ch'];
}

// ── Chart: Churn per Contract ────────────────────────────────
$conRes = mysqli_query($conn, "
    SELECT contract_length, COUNT(*) as total, SUM(churn) as churned
    FROM customer_churn GROUP BY contract_length
    ORDER BY FIELD(contract_length,'Monthly','Quarterly','Annual')
");
$conLabels = $conChurn = [];
while ($r = mysqli_fetch_assoc($conRes)) {
    $conLabels[] = $r['contract_length'];
    $conChurn[]  = (int)$r['churned'];
}

// ── Detail per Subscription ──────────────────────────────────
$detailRes = mysqli_query($conn, "
    SELECT subscription_type,
           COUNT(*) as total,
           SUM(churn) as churned,
           COUNT(*) - SUM(churn) as aktif,
           ROUND(SUM(churn)/COUNT(*)*100,1) as rate
    FROM customer_churn
    GROUP BY subscription_type
    ORDER BY rate DESC
");

// ── High Risk Customers ──────────────────────────────────────
$highRisk = mysqli_query($conn, "
    SELECT * FROM customer_churn
    WHERE churn=1 AND payment_delay > 20 AND support_calls > 6
    ORDER BY payment_delay DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Churn Analysis — CRM Analytics</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include 'partials/sidebar.php'; ?>

<div class="main">

  <div class="page-header">
    <div>
      <div class="page-title">Churn Analysis</div>
      <div class="page-sub">Analisis mendalam pola customer churn — data real dari database</div>
    </div>
    <div class="churn-rate-badge">
      <div class="churn-rate-label">Churn Rate</div>
      <div class="churn-rate-value"><?= $rate ?>%</div>
    </div>
  </div>

  <!-- Insight Box -->
  <div class="insight-box">
    <div class="insight-title">Temuan Analisis</div>
    <ul class="insight-list">
      <li>Rata-rata customer yang churn memiliki <strong><?= $avgDelay ?> hari</strong> payment delay dan <strong><?= $avgCalls ?>x</strong> support call.</li>
      <li>Customer churn rata-rata menghabiskan <strong>$<?= number_format($avgSpendChurn,2) ?></strong> — <?= $avgSpendChurn < $avgSpendAktif ? 'lebih rendah' : 'lebih tinggi' ?> dari customer aktif ($<?= number_format($avgSpendAktif,2) ?>).</li>
      <li>Rata-rata masa berlangganan customer churn: <strong><?= $avgTenure ?> bulan</strong> — perlu program loyalitas lebih agresif di tahun pertama.</li>
    </ul>
  </div>

  <!-- KPI -->
  <div class="kpi-grid">
    <div class="kpi-card blue">
      <div class="kpi-indicator"></div>
      <div class="kpi-label">Rata-rata Usia (Churn)</div>
      <div class="kpi-value"><?= $avgAge ?></div>
      <div class="kpi-sub">tahun</div>
    </div>
    <div class="kpi-card red">
      <div class="kpi-indicator"></div>
      <div class="kpi-label">Avg Support Calls</div>
      <div class="kpi-value"><?= $avgCalls ?></div>
      <div class="kpi-sub danger">kali per customer</div>
    </div>
    <div class="kpi-card" style="--col:#f59e0b;">
      <div class="kpi-indicator" style="background:var(--warn);box-shadow:0 0 8px var(--warn);"></div>
      <div class="kpi-label">Avg Payment Delay</div>
      <div class="kpi-value"><?= $avgDelay ?></div>
      <div class="kpi-sub" style="color:#fbbf24;">hari keterlambatan</div>
    </div>
    <div class="kpi-card green">
      <div class="kpi-indicator"></div>
      <div class="kpi-label">Avg Tenure (Churn)</div>
      <div class="kpi-value"><?= $avgTenure ?></div>
      <div class="kpi-sub">bulan berlangganan</div>
    </div>
  </div>

  <!-- Charts Row 1 -->
  <div class="charts-grid">
    <div class="section-card">
      <div class="section-header">
        <div class="section-title">Churn per Subscription Type</div>
        <span class="section-badge">Grouped Bar</span>
      </div>
      <div class="chart-wrap"><canvas id="subChart"></canvas></div>
    </div>
    <div class="section-card">
      <div class="section-header">
        <div class="section-title">Distribusi per Gender</div>
        <span class="section-badge">Stacked Bar</span>
      </div>
      <div class="chart-wrap"><canvas id="genderChart"></canvas></div>
    </div>
  </div>

  <!-- Charts Row 2 -->
  <div class="charts-grid">
    <div class="section-card">
      <div class="section-header">
        <div class="section-title">Churn per Kelompok Usia</div>
        <span class="section-badge">Bar</span>
      </div>
      <div class="chart-wrap"><canvas id="ageChart"></canvas></div>
    </div>
    <div class="section-card">
      <div class="section-header">
        <div class="section-title">Churn per Jenis Kontrak</div>
        <span class="section-badge">Donut</span>
      </div>
      <div class="chart-wrap"><canvas id="contractChart"></canvas></div>
    </div>
  </div>

  <!-- Tabel Detail per Subscription -->
  <div class="section-card">
    <div class="section-header">
      <div class="section-title">Detail Churn per Segmen Subscription</div>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>Subscription</th><th>Total Customer</th><th>Total Churn</th><th>Total Aktif</th><th>Churn Rate</th><th>Risiko</th></tr>
        </thead>
        <tbody>
          <?php while ($r = mysqli_fetch_assoc($detailRes)):
            $subClass = strtolower($r['subscription_type']);
            $rate2 = $r['rate'];
            $riskClass = $rate2 >= 30 ? 'risk-high' : ($rate2 >= 25 ? 'risk-med' : 'risk-low');
            $riskLabel = $rate2 >= 30 ? 'Tinggi' : ($rate2 >= 25 ? 'Sedang' : 'Rendah');
          ?>
          <tr>
            <td><span class="badge badge-<?= $subClass ?>"><?= htmlspecialchars($r['subscription_type']) ?></span></td>
            <td><?= number_format($r['total']) ?></td>
            <td><span class="badge badge-churn"><?= number_format($r['churned']) ?></span></td>
            <td><span class="badge badge-active"><?= number_format($r['aktif']) ?></span></td>
            <td>
              <div style="display:flex;align-items:center;gap:10px;">
                <div style="flex:1;height:5px;background:rgba(255,255,255,0.06);border-radius:3px;">
                  <div style="width:<?= $rate2 ?>%;height:100%;background:<?= $rate2>=30?'#ef4444':($rate2>=25?'#f59e0b':'#10b981') ?>;border-radius:3px;"></div>
                </div>
                <strong class="<?= $riskClass ?>"><?= $rate2 ?>%</strong>
              </div>
            </td>
            <td><span class="<?= $riskClass ?>"><?= $riskLabel ?></span></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- High Risk Customers -->
  <div class="section-card">
    <div class="section-header">
      <div class="section-title">Customer Berisiko Tinggi (Churn + Delay > 20 hr + Calls > 6x)</div>
      <span class="section-badge"><?= mysqli_num_rows($highRisk) ?> customer</span>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>Customer ID</th><th>Gender</th><th>Usia</th><th>Subscription</th><th>Total Spend</th><th>Support Calls</th><th>Payment Delay</th><th>Tenure</th></tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($highRisk) === 0): ?>
            <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:24px;">Tidak ada customer berisiko tinggi saat ini. 🎉</td></tr>
          <?php else: while ($r = mysqli_fetch_assoc($highRisk)): ?>
          <tr>
            <td><strong style="color:#f87171;font-family:'JetBrains Mono',monospace;font-size:12px;"><?= htmlspecialchars($r['customerid']) ?></strong></td>
            <td><?= htmlspecialchars($r['gender']) ?></td>
            <td><?= $r['age'] ?> th</td>
            <td><span class="badge badge-<?= strtolower($r['subscription_type']) ?>"><?= htmlspecialchars($r['subscription_type']) ?></span></td>
            <td><strong>$<?= number_format($r['total_spend'],2) ?></strong></td>
            <td><span class="risk-high"><?= $r['support_calls'] ?>x</span></td>
            <td><span class="risk-high"><?= $r['payment_delay'] ?> hr</span></td>
            <td><?= $r['tenure'] ?> bln</td>
          </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div><!-- /main -->

<script>
// ── Data dari PHP ──────────────────────────────────────────
const subLabels  = <?= json_encode($subLabels) ?>;
const subTotal   = <?= json_encode($subTotal) ?>;
const subChurn   = <?= json_encode($subChurn) ?>;
const genLabels  = <?= json_encode($genLabels) ?>;
const genChurn   = <?= json_encode($genChurn) ?>;
const genAktif   = <?= json_encode($genAktif) ?>;
const ageLabels  = <?= json_encode($ageLabels) ?>;
const ageChurn   = <?= json_encode($ageChurn) ?>;
const ageAktif   = <?= json_encode($ageAktif) ?>;
const conLabels  = <?= json_encode($conLabels) ?>;
const conChurn   = <?= json_encode($conChurn) ?>;

// ── Chart Defaults ──────────────────────────────────────────
const CD = {
  responsive: true, maintainAspectRatio: false,
  plugins: { legend: { labels: { color:'#94a3b8', font:{size:11,family:'Space Grotesk'}, padding:14 }}},
  scales: {
    x: { ticks:{color:'#64748b',font:{size:10}}, grid:{color:'rgba(255,255,255,0.03)'}, border:{color:'rgba(255,255,255,0.05)'} },
    y: { ticks:{color:'#64748b',font:{size:10}}, grid:{color:'rgba(255,255,255,0.03)'}, border:{color:'rgba(255,255,255,0.05)'} }
  }
};

// Subscription
new Chart(document.getElementById('subChart'), {
  type: 'bar',
  data: {
    labels: subLabels,
    datasets: [
      { label:'Total', data:subTotal, backgroundColor:'rgba(100,116,139,0.4)', borderColor:'#64748b', borderWidth:1.5, borderRadius:5 },
      { label:'Churn', data:subChurn, backgroundColor:'rgba(239,68,68,0.45)', borderColor:'#ef4444', borderWidth:1.5, borderRadius:5 }
    ]
  },
  options: JSON.parse(JSON.stringify(CD))
});

// Gender
new Chart(document.getElementById('genderChart'), {
  type: 'bar',
  data: {
    labels: genLabels,
    datasets: [
      { label:'Aktif', data:genAktif, backgroundColor:'rgba(16,185,129,0.5)', borderColor:'#10b981', borderWidth:1.5, borderRadius:5 },
      { label:'Churn', data:genChurn, backgroundColor:'rgba(239,68,68,0.5)', borderColor:'#ef4444', borderWidth:1.5, borderRadius:5 }
    ]
  },
  options: { ...JSON.parse(JSON.stringify(CD)), scales: { ...JSON.parse(JSON.stringify(CD.scales)), x:{...CD.scales.x, stacked:false} } }
});

// Age
new Chart(document.getElementById('ageChart'), {
  type: 'bar',
  data: {
    labels: ageLabels,
    datasets: [
      { label:'Churn', data:ageChurn, backgroundColor:'rgba(239,68,68,0.5)', borderColor:'#ef4444', borderWidth:1.5, borderRadius:5 },
      { label:'Aktif', data:ageAktif, backgroundColor:'rgba(16,185,129,0.45)', borderColor:'#10b981', borderWidth:1.5, borderRadius:5 }
    ]
  },
  options: JSON.parse(JSON.stringify(CD))
});

// Contract Donut
new Chart(document.getElementById('contractChart'), {
  type: 'doughnut',
  data: {
    labels: conLabels,
    datasets: [{
      data: conChurn,
      backgroundColor: ['rgba(59,130,246,0.65)','rgba(245,158,11,0.65)','rgba(6,182,212,0.65)'],
      borderColor: ['#3b82f6','#f59e0b','#06b6d4'],
      borderWidth: 2, hoverOffset: 5
    }]
  },
  options: {
    responsive:true, maintainAspectRatio:false, cutout:'60%',
    plugins:{ legend:{ position:'bottom', labels:{ color:'#94a3b8', padding:14, font:{size:11,family:'Space Grotesk'} } } }
  }
});
</script>
</body>
</html>
