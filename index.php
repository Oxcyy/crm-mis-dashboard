<?php
session_start();
if (!isset($_SESSION['login'])) { header("Location: login.php"); exit; }
include 'koneksi.php';

$total   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM customer_churn"))['c'] ?? 0;
$churnN  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM customer_churn WHERE churn=1"))['c'] ?? 0;
$aktif   = $total - $churnN;
$rate    = $total > 0 ? round($churnN / $total * 100, 1) : 0;
$revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT ROUND(SUM(total_spend),2) as r FROM customer_churn"))['r'] ?? 0;

$subRes = mysqli_query($conn, "SELECT subscription_type, COUNT(*) as cnt FROM customer_churn GROUP BY subscription_type");
$subStats = [];
while ($r = mysqli_fetch_assoc($subRes)) { $subStats[$r['subscription_type']] = (int)$r['cnt']; }
$subBasic    = $subStats['Basic']    ?? 0;
$subStandard = $subStats['Standard'] ?? 0;
$subPremium  = $subStats['Premium']  ?? 0;
$pctBasic    = $total > 0 ? round($subBasic/$total*100) : 0;
$pctStandard = $total > 0 ? round($subStandard/$total*100) : 0;
$pctPremium  = $total > 0 ? round($subPremium/$total*100) : 0;
?>

<!DOCTYPE html>
<html>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CRM Analytics — MIS Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
:root {
  --bg: #06080f;
  --surface: #0b0f1c;
  --card: #0f1525;
  --card2: #141b2e;
  --accent: #3b82f6;
  --accent2: #06b6d4;
  --accent3: #10b981;
  --danger: #ef4444;
  --warn: #f59e0b;
  --purple: #8b5cf6;
  --text: #e2e8f0;
  --text2: #94a3b8;
  --muted: #475569;
  --border: rgba(59,130,246,0.12);
  --border2: rgba(255,255,255,0.05);
  --sidebar-w: 240px;
  --glow: 0 0 30px rgba(59,130,246,0.15);
}

* { margin: 0; padding: 0; box-sizing: border-box; }
html { scroll-behavior: smooth; }

body {
  font-family: 'Space Grotesk', sans-serif;
  background: var(--bg);
  color: var(--text);
  min-height: 100vh;
  display: flex;
  overflow-x: hidden;
}

/* === NOISE TEXTURE OVERLAY === */
body::before {
  content: '';
  position: fixed;
  inset: 0;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");
  pointer-events: none;
  z-index: 9999;
  opacity: 0.4;
}

/* === SIDEBAR === */
.sidebar {
  width: var(--sidebar-w);
  min-height: 100vh;
  background: var(--surface);
  border-right: 1px solid var(--border2);
  display: flex;
  flex-direction: column;
  position: fixed;
  top: 0; left: 0;
  z-index: 100;
  padding: 24px 14px;
  transition: all 0.3s;
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 10px 24px;
  margin-bottom: 20px;
  border-bottom: 1px solid var(--border2);
}

.brand-logo {
  width: 38px; height: 38px;
  background: linear-gradient(135deg, var(--accent), var(--accent2));
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  position: relative;
  overflow: hidden;
}

.brand-logo::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(255,255,255,0.2), transparent);
}

.brand-logo svg { width: 20px; height: 20px; fill: white; }

.brand-name {
  font-weight: 700;
  font-size: 14px;
  color: var(--text);
  letter-spacing: -0.3px;
}
.brand-sub { font-size: 10px; color: var(--muted); margin-top: 2px; letter-spacing: 0.5px; text-transform: uppercase; }

.nav-label {
  font-size: 9px;
  font-weight: 600;
  letter-spacing: 2px;
  color: var(--muted);
  padding: 0 10px;
  margin: 16px 0 8px;
  text-transform: uppercase;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 10px;
  color: var(--text2);
  text-decoration: none;
  font-size: 13.5px;
  font-weight: 500;
  transition: all 0.2s;
  margin-bottom: 2px;
  position: relative;
}

.nav-link:hover { background: rgba(59,130,246,0.08); color: var(--text); }

.nav-link.active {
  background: rgba(59,130,246,0.12);
  color: #60a5fa;
  border: 1px solid rgba(59,130,246,0.2);
}

.nav-link.active::before {
  content: '';
  position: absolute;
  left: 0; top: 20%; bottom: 20%;
  width: 2px;
  background: var(--accent);
  border-radius: 2px;
}

.nav-icon {
  width: 18px; height: 18px;
  opacity: 0.7;
  flex-shrink: 0;
}

.nav-link.active .nav-icon { opacity: 1; }

.sidebar-footer {
  margin-top: auto;
  padding-top: 16px;
  border-top: 1px solid var(--border2);
}

.user-row {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px;
  border-radius: 10px;
  background: rgba(255,255,255,0.03);
}

.user-avatar {
  width: 32px; height: 32px;
  background: linear-gradient(135deg, var(--accent), var(--purple));
  border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  font-size: 13px;
  font-weight: 700;
  color: white;
  flex-shrink: 0;
}

.user-name { font-size: 13px; font-weight: 600; color: var(--text); }
.user-role { font-size: 11px; color: var(--muted); }

.logout-btn {
  margin-left: auto;
  width: 28px; height: 28px;
  border-radius: 7px;
  border: 1px solid var(--border2);
  background: transparent;
  color: var(--muted);
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.2s;
  text-decoration: none;
  font-size: 13px;
}
.logout-btn:hover { color: var(--danger); border-color: rgba(239,68,68,0.3); background: rgba(239,68,68,0.08); }

/* === MAIN CONTENT === */
.main {
  margin-left: var(--sidebar-w);
  flex: 1;
  padding: 28px 32px;
  min-height: 100vh;
  animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}

/* === PAGE HEADER === */
.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 28px;
}

.page-title {
  font-size: 22px;
  font-weight: 700;
  color: var(--text);
  letter-spacing: -0.5px;
}

.page-sub {
  font-size: 13px;
  color: var(--muted);
  margin-top: 4px;
}

.clock-badge {
  font-family: 'JetBrains Mono', monospace;
  font-size: 12px;
  color: var(--text2);
  background: var(--card);
  border: 1px solid var(--border2);
  padding: 8px 14px;
  border-radius: 8px;
}

/* === INSIGHT BOX === */
.insight-box {
  background: linear-gradient(135deg, rgba(59,130,246,0.06), rgba(6,182,212,0.04));
  border: 1px solid rgba(59,130,246,0.15);
  border-radius: 14px;
  padding: 18px 22px;
  margin-bottom: 24px;
  position: relative;
  overflow: hidden;
}

.insight-box::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(59,130,246,0.4), transparent);
}

.insight-title {
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: #60a5fa;
  margin-bottom: 10px;
}

.insight-list { list-style: none; display: flex; flex-direction: column; gap: 6px; }

.insight-list li {
  font-size: 13px;
  color: var(--text2);
  padding-left: 14px;
  position: relative;
}
.insight-list li::before {
  content: '—';
  position: absolute;
  left: 0;
  color: var(--accent);
  font-size: 10px;
}
.insight-list li strong { color: var(--text); }

/* === KPI CARDS === */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 24px;
}

.kpi-card {
  background: var(--card);
  border: 1px solid var(--border2);
  border-radius: 14px;
  padding: 20px;
  position: relative;
  overflow: hidden;
  transition: transform 0.2s, border-color 0.2s, box-shadow 0.2s;
  animation: slideUp 0.5s ease both;
}

.kpi-card:nth-child(1) { animation-delay: 0.05s; }
.kpi-card:nth-child(2) { animation-delay: 0.1s; }
.kpi-card:nth-child(3) { animation-delay: 0.15s; }
.kpi-card:nth-child(4) { animation-delay: 0.2s; }

@keyframes slideUp {
  from { opacity: 0; transform: translateY(16px); }
  to { opacity: 1; transform: translateY(0); }
}

.kpi-card:hover {
  transform: translateY(-2px);
  border-color: rgba(59,130,246,0.25);
  box-shadow: 0 8px 30px rgba(0,0,0,0.3);
}

.kpi-card::after {
  content: '';
  position: absolute;
  top: 0; right: 0;
  width: 80px; height: 80px;
  border-radius: 50%;
  filter: blur(30px);
  opacity: 0.12;
}

.kpi-card.blue::after { background: var(--accent); }
.kpi-card.green::after { background: var(--accent3); }
.kpi-card.red::after { background: var(--danger); }
.kpi-card.purple::after { background: var(--purple); }

.kpi-indicator {
  width: 6px; height: 6px;
  border-radius: 50%;
  margin-bottom: 14px;
}
.blue .kpi-indicator { background: var(--accent); box-shadow: 0 0 8px var(--accent); }
.green .kpi-indicator { background: var(--accent3); box-shadow: 0 0 8px var(--accent3); }
.red .kpi-indicator { background: var(--danger); box-shadow: 0 0 8px var(--danger); }
.purple .kpi-indicator { background: var(--purple); box-shadow: 0 0 8px var(--purple); }

.kpi-label { font-size: 11px; color: var(--muted); font-weight: 500; text-transform: uppercase; letter-spacing: 0.8px; }

.kpi-value {
  font-size: 28px;
  font-weight: 700;
  color: var(--text);
  letter-spacing: -1px;
  margin: 6px 0;
  font-variant-numeric: tabular-nums;
}

.kpi-sub { font-size: 11px; color: var(--text2); }
.kpi-sub.danger { color: #f87171; }
.kpi-sub.success { color: #34d399; }

/* === SECTION CARDS === */
.section-card {
  background: var(--card);
  border: 1px solid var(--border2);
  border-radius: 14px;
  padding: 22px;
  margin-bottom: 20px;
  transition: border-color 0.2s;
}

.section-card:hover { border-color: rgba(59,130,246,0.15); }

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 18px;
  padding-bottom: 14px;
  border-bottom: 1px solid var(--border2);
}

.section-title {
  font-size: 13px;
  font-weight: 600;
  color: var(--text);
  letter-spacing: 0.2px;
}

.section-badge {
  font-size: 10px;
  color: var(--muted);
  background: rgba(255,255,255,0.04);
  border: 1px solid var(--border2);
  padding: 4px 10px;
  border-radius: 20px;
}

/* === CHARTS GRID === */
.charts-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 18px;
  margin-bottom: 20px;
}

.chart-wrap {
  height: 240px;
  position: relative;
}

/* === TABLE === */
.table-wrap { overflow-x: auto; }

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}

thead th {
  padding: 10px 14px;
  text-align: left;
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 1.2px;
  text-transform: uppercase;
  color: var(--muted);
  border-bottom: 1px solid var(--border2);
  white-space: nowrap;
}

tbody tr {
  border-bottom: 1px solid rgba(255,255,255,0.03);
  transition: background 0.15s;
}

tbody tr:hover { background: rgba(59,130,246,0.04); }
tbody tr:last-child { border-bottom: none; }

td {
  padding: 11px 14px;
  color: var(--text2);
  vertical-align: middle;
}

td strong { color: var(--text); }

/* === BADGES === */
.badge {
  display: inline-flex;
  align-items: center;
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.3px;
}

.badge-basic { background: rgba(71,85,105,0.2); color: #94a3b8; border: 1px solid rgba(71,85,105,0.3); }
.badge-standard { background: rgba(59,130,246,0.12); color: #60a5fa; border: 1px solid rgba(59,130,246,0.25); }
.badge-premium { background: rgba(139,92,246,0.12); color: #a78bfa; border: 1px solid rgba(139,92,246,0.25); }
.badge-churn { background: rgba(239,68,68,0.1); color: #f87171; border: 1px solid rgba(239,68,68,0.2); }
.badge-active { background: rgba(16,185,129,0.1); color: #34d399; border: 1px solid rgba(16,185,129,0.2); }

/* === STAT BARS === */
.stat-row {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 0;
  border-bottom: 1px solid rgba(255,255,255,0.03);
}
.stat-row:last-child { border-bottom: none; }
.stat-label { font-size: 12px; color: var(--text2); width: 80px; flex-shrink: 0; }
.stat-bar-bg { flex: 1; height: 5px; background: rgba(255,255,255,0.06); border-radius: 3px; overflow: hidden; }
.stat-bar { height: 100%; border-radius: 3px; transition: width 1s cubic-bezier(0.25, 1, 0.5, 1); }
.stat-val { font-size: 12px; color: var(--text2); width: 70px; text-align: right; font-family: 'JetBrains Mono', monospace; }

/* === RISK COLORS === */
.risk-high { color: #f87171; }
.risk-med  { color: #fbbf24; }
.risk-low  { color: #34d399; }

/* === ACTION BUTTONS === */
.action-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 5px 10px;
  border-radius: 7px;
  font-size: 11px;
  font-weight: 500;
  text-decoration: none;
  transition: all 0.15s;
  cursor: pointer;
  border: 1px solid transparent;
  margin-right: 4px;
}

.btn-edit {
  background: rgba(59,130,246,0.1);
  color: #60a5fa;
  border-color: rgba(59,130,246,0.2);
}
.btn-edit:hover { background: rgba(59,130,246,0.2); }

.btn-delete {
  background: rgba(239,68,68,0.08);
  color: #f87171;
  border-color: rgba(239,68,68,0.15);
}
.btn-delete:hover { background: rgba(239,68,68,0.18); }

.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  background: var(--accent);
  color: white;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
  font-family: 'Space Grotesk', sans-serif;
  letter-spacing: 0.2px;
}
.btn-primary:hover { background: #2563eb; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(59,130,246,0.35); }

.btn-secondary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 9px 16px;
  background: transparent;
  color: var(--text2);
  border-radius: 10px;
  font-size: 13px;
  font-weight: 500;
  text-decoration: none;
  cursor: pointer;
  border: 1px solid var(--border2);
  transition: all 0.2s;
  font-family: 'Space Grotesk', sans-serif;
}
.btn-secondary:hover { background: rgba(255,255,255,0.04); color: var(--text); }

/* === FILTER BAR === */
.filter-bar {
  display: flex;
  gap: 10px;
  align-items: center;
  flex-wrap: wrap;
}

.search-input {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(255,255,255,0.03);
  border: 1px solid var(--border2);
  border-radius: 10px;
  padding: 8px 14px;
  flex: 1;
  min-width: 200px;
  max-width: 300px;
}

.search-input input {
  background: none;
  border: none;
  outline: none;
  color: var(--text);
  font-size: 13px;
  font-family: 'Space Grotesk', sans-serif;
  width: 100%;
}

.search-input input::placeholder { color: var(--muted); }

.search-icon { color: var(--muted); flex-shrink: 0; }

.form-select {
  background: rgba(255,255,255,0.03);
  border: 1px solid var(--border2);
  border-radius: 10px;
  padding: 9px 14px;
  color: var(--text2);
  font-size: 13px;
  font-family: 'Space Grotesk', sans-serif;
  outline: none;
  cursor: pointer;
  transition: border-color 0.2s;
}
.form-select:focus { border-color: rgba(59,130,246,0.3); color: var(--text); }

/* === FORM GRID === */
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 18px;
}

.form-group { display: flex; flex-direction: column; gap: 8px; }

.form-label {
  font-size: 11px;
  font-weight: 600;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.8px;
}

.form-input {
  background: rgba(255,255,255,0.03);
  border: 1px solid var(--border2);
  border-radius: 10px;
  padding: 11px 14px;
  color: var(--text);
  font-size: 14px;
  font-family: 'Space Grotesk', sans-serif;
  outline: none;
  transition: all 0.2s;
  width: 100%;
}
.form-input:focus { border-color: rgba(59,130,246,0.35); background: rgba(59,130,246,0.04); box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
.form-input::placeholder { color: var(--muted); font-size: 13px; }
.form-input:disabled { opacity: 0.4; cursor: not-allowed; }

/* === ALERT === */
.alert {
  padding: 14px 18px;
  border-radius: 12px;
  font-size: 13px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.alert-success { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2); color: #34d399; }
.alert-error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #f87171; }

/* === PAGINATION === */
.pagination {
  display: flex;
  gap: 6px;
  justify-content: center;
  padding-top: 20px;
  padding-bottom: 4px;
}
.page-btn {
  width: 34px; height: 34px;
  display: flex; align-items: center; justify-content: center;
  border-radius: 8px;
  font-size: 13px;
  color: var(--text2);
  background: rgba(255,255,255,0.03);
  border: 1px solid var(--border2);
  cursor: pointer;
  transition: all 0.15s;
  text-decoration: none;
}
.page-btn:hover, .page-btn.active {
  background: rgba(59,130,246,0.15);
  border-color: rgba(59,130,246,0.3);
  color: #60a5fa;
}

/* === TABS (for page switching in demo) === */
.tab-nav {
  display: flex;
  gap: 4px;
  background: rgba(255,255,255,0.03);
  border: 1px solid var(--border2);
  border-radius: 12px;
  padding: 4px;
  margin-bottom: 28px;
  width: fit-content;
}

.tab-btn {
  padding: 8px 18px;
  border-radius: 9px;
  font-size: 13px;
  font-weight: 500;
  color: var(--muted);
  background: none;
  border: none;
  cursor: pointer;
  font-family: 'Space Grotesk', sans-serif;
  transition: all 0.2s;
}

.tab-btn.active {
  background: rgba(59,130,246,0.15);
  color: #60a5fa;
  border: 1px solid rgba(59,130,246,0.25);
}

.tab-btn:hover:not(.active) { color: var(--text2); background: rgba(255,255,255,0.04); }

/* === PAGE SECTIONS === */
.page-section { display: none; }
.page-section.active { display: block; animation: fadeIn 0.3s ease; }

/* === CHURN RATE BADGE === */
.churn-rate-badge {
  background: rgba(239,68,68,0.08);
  border: 1px solid rgba(239,68,68,0.2);
  border-radius: 12px;
  padding: 10px 18px;
  text-align: center;
}
.churn-rate-label { font-size: 9px; color: #f87171; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; }
.churn-rate-value { font-size: 26px; font-weight: 700; color: #ef4444; letter-spacing: -1px; font-variant-numeric: tabular-nums; }

/* Scrollbar */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: var(--bg); }
::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 3px; }
::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.14); }

/* === SPARKLINE MINI === */
.sparkline-row { display: flex; align-items: flex-end; gap: 3px; height: 30px; }
.spark-bar { flex: 1; border-radius: 2px 2px 0 0; transition: height 0.8s cubic-bezier(0.25,1,0.5,1); }

/* RESPONSIVE */
@media (max-width: 1100px) {
  .kpi-grid { grid-template-columns: repeat(2, 1fr); }
  .charts-grid { grid-template-columns: 1fr; }
}

@media (max-width: 768px) {
  .sidebar { transform: translateX(-100%); }
  .main { margin-left: 0; padding: 20px; }
}
</style>
</head>
<body>

<!-- SIDEBAR -->
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

  <a href="#" class="nav-link active" onclick="showPage('dashboard', this)">
    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
    Dashboard
  </a>
  <a href="#" class="nav-link" href="customers.php" onclick="showPage('customers', this)">
    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    Data Customer
  </a>
  <a href="#" class="nav-link" onclick="showPage('analytics', this)">
    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
    Churn Analysis
  </a>
  <a href="#" class="nav-link" onclick="showPage('tambah', this)">
    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
    Tambah Customer
  </a>

  <div class="sidebar-footer">
    <div class="user-row">
      <div class="user-avatar">A</div>
      <div>
        <div class="user-name">Admin</div>
        <div class="user-role">Administrator</div>
      </div>
      <a href="logout.php" class="logout-btn" title="Logout">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      </a>
    </div>
  </div>
</aside>

<!-- MAIN -->
<div class="main">

  <!-- ========== DASHBOARD PAGE ========== -->
  <div class="page-section active" id="page-dashboard">
    <div class="page-header">
      <div>
        <div class="page-title">Dashboard</div>
        <div class="page-sub">Ringkasan performa customer retention</div>
      </div>
      <div class="clock-badge" id="clock">--:--:--</div>
    </div>

    <div class="insight-box">
      <div class="insight-title">Insight Utama</div>
      <ul class="insight-list">
        <li>Churn rate saat ini <strong><?= $rate ?>%</strong> — perlu perhatian ekstra pada customer dengan payment delay tinggi.</li>
        <li>Customer aktif sebanyak <strong><?= number_format($aktif) ?></strong> orang — potensi revenue retention sangat besar.</li>
        <li>Rata-rata pengeluaran customer adalah <strong>$498.24</strong> — fokus upsell ke segmen Standard.</li>
      </ul>
    </div>

    <div class="kpi-grid">
      <div class="kpi-card blue">
        <div class="kpi-indicator"></div>
        <div class="kpi-label">Total Customer</div>
        <div class="kpi-value" data-count="<?= (int)$total ?>">0</div>
        <div class="kpi-sub">Semua segmen</div>
      </div>
      <div class="kpi-card green">
        <div class="kpi-indicator"></div>
        <div class="kpi-label">Customer Aktif</div>
        <div class="kpi-value" data-count="<?= (int)$aktif ?>">0</div>
        <div class="kpi-sub success">Retensi baik</div>
      </div>
      <div class="kpi-card red">
        <div class="kpi-indicator"></div>
        <div class="kpi-label">Total Churn</div>
        <div class="kpi-value" data-count="<?= (int)$churnN ?>">0</div>
        <div class="kpi-sub danger">26.5% dari total</div>
      </div>
      <div class="kpi-card purple">
        <div class="kpi-indicator"></div>
        <div class="kpi-label">Total Revenue</div>
        <div class="kpi-value" id="revenue-kpi">$0</div>
        <div class="kpi-sub">Akumulasi spend</div>
      </div>
    </div>

    <div class="charts-grid">
      <div class="section-card">
        <div class="section-header">
          <div class="section-title">Churn vs Aktif</div>
          <span class="section-badge">Donut</span>
        </div>
        <div class="chart-wrap">
          <canvas id="churnDonut"></canvas>
        </div>
      </div>

      <div class="section-card">
        <div class="section-header">
          <div class="section-title">Distribusi Subscription</div>
          <span class="section-badge">Bar</span>
        </div>
        <div style="padding-top:4px;">
          <div class="stat-row">
            <div class="stat-label">Basic</div>
            <div class="stat-bar-bg"><div class="stat-bar" style="width:0%; background:#64748b;" data-w="<?= $pctBasic ?>"></div></div>
            <div class="stat-val"><?= number_format($subBasic) ?> (<?= $pctBasic ?>%)</div>
          </div>
          <div class="stat-row">
            <div class="stat-label">Standard</div>
            <div class="stat-bar-bg"><div class="stat-bar" style="width:0%; background:#3b82f6;" data-w="<?= $pctStandard ?>"></div></div>
            <div class="stat-val"><?= number_format($subStandard) ?> (<?= $pctStandard ?>%)</div>
          </div>
          <div class="stat-row">
            <div class="stat-label">Premium</div>
            <div class="stat-bar-bg"><div class="stat-bar" style="width:0%; background:#8b5cf6;" data-w="<?= $pctPremium ?>"></div></div>
            <div class="stat-val"><?= number_format($subPremium) ?> (<?= $pctPremium ?>%)</div>
          </div>
        </div>
      </div>
    </div>

    <div class="section-card">
      <div class="section-header">
        <div class="section-title">10 Customer Terbaru</div>
        <a href="#" class="btn-secondary" onclick="showPage('customers',null)" style="font-size:12px;padding:6px 14px;">Lihat Semua</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Customer ID</th><th>Gender</th><th>Usia</th><th>Subscription</th><th>Contract</th><th>Total Spend</th><th>Status</th><th>Aksi</th>
            </tr>
          </thead>
          <tbody id="recent-tbody"></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ========== CUSTOMERS PAGE ========== -->
  <div class="page-section" id="page-customers">
    <div class="page-header">
      <div>
        <div class="page-title">Data Customer</div>
        <div class="page-sub">Kelola dan pantau seluruh data customer</div>
      </div>
      <a href="#" class="btn-primary" onclick="showPage('tambah',null)">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Customer
      </a>
    </div>

    <div class="section-card" style="padding:16px 20px; margin-bottom:16px;">
      <div class="filter-bar">
        <div class="search-input">
          <svg class="search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" id="search-input" placeholder="Cari customer ID / gender..." oninput="filterCustomers()">
        </div>
        <select class="form-select" id="filter-sub" onchange="filterCustomers()" style="width:150px;">
          <option value="">Semua Tipe</option>
          <option value="Basic">Basic</option>
          <option value="Standard">Standard</option>
          <option value="Premium">Premium</option>
        </select>
        <select class="form-select" id="filter-churn" onchange="filterCustomers()" style="width:140px;">
          <option value="">Semua Status</option>
          <option value="0">Aktif</option>
          <option value="1">Churn</option>
        </select>
        <button class="btn-secondary" onclick="resetFilter()" style="font-size:12px;padding:8px 14px;">Reset</button>
        <span style="margin-left:auto; color:var(--muted); font-size:12px;" id="count-label">— customer</span>
      </div>
    </div>

    <div class="section-card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>#</th><th>Customer ID</th><th>Gender</th><th>Usia</th><th>Tenure</th><th>Subscription</th><th>Contract</th><th>Total Spend</th><th>Support Calls</th><th>Payment Delay</th><th>Status</th><th>Aksi</th></tr>
          </thead>
          <tbody id="cust-tbody"></tbody>
        </table>
      </div>
      <div class="pagination" id="pagination"></div>
    </div>
  </div>

  <!-- ========== ANALYTICS PAGE ========== -->
  <div class="page-section" id="page-analytics">
    <div class="page-header">
      <div>
        <div class="page-title">Churn Analysis</div>
        <div class="page-sub">Analisis mendalam pola customer churn</div>
      </div>
      <div class="churn-rate-badge">
        <div class="churn-rate-label">Churn Rate</div>
        <div class="churn-rate-value"><?= $rate ?>%</div>
      </div>
    </div>

    <div class="insight-box">
      <div class="insight-title">Temuan Analisis</div>
      <ul class="insight-list">
        <li>Rata-rata customer yang churn memiliki <strong>19.3 hari</strong> payment delay dan melakukan <strong>5.4x</strong> support call.</li>
        <li>Customer churn rata-rata menghabiskan <strong>$498.11</strong> — lebih rendah dari customer aktif ($521.30).</li>
        <li>Rata-rata masa berlangganan customer yang churn: <strong>32.7 bulan</strong> — perlu program loyalitas lebih agresif di tahun pertama.</li>
      </ul>
    </div>

    <div class="kpi-grid">
      <div class="kpi-card blue"><div class="kpi-indicator"></div><div class="kpi-label">Rata-rata Usia</div><div class="kpi-value">38.2</div><div class="kpi-sub">tahun</div></div>
      <div class="kpi-card red"><div class="kpi-indicator"></div><div class="kpi-label">Avg Support Call</div><div class="kpi-value">5.4</div><div class="kpi-sub danger">kali per customer</div></div>
      <div class="kpi-card" style="--col:#f59e0b;"><div class="kpi-indicator" style="background:var(--warn);box-shadow:0 0 8px var(--warn);"></div><div class="kpi-label">Avg Payment Delay</div><div class="kpi-value">19.3</div><div class="kpi-sub" style="color:#fbbf24;">hari keterlambatan</div></div>
      <div class="kpi-card green"><div class="kpi-indicator"></div><div class="kpi-label">Avg Tenure</div><div class="kpi-value">32.7</div><div class="kpi-sub">bulan berlangganan</div></div>
    </div>

    <div class="charts-grid">
      <div class="section-card">
        <div class="section-header"><div class="section-title">Churn per Subscription Type</div><span class="section-badge">Grouped Bar</span></div>
        <div class="chart-wrap"><canvas id="subChart"></canvas></div>
      </div>
      <div class="section-card">
        <div class="section-header"><div class="section-title">Distribusi per Gender</div><span class="section-badge">Pie</span></div>
        <div class="chart-wrap"><canvas id="genderChart"></canvas></div>
      </div>
    </div>

    <div class="charts-grid">
      <div class="section-card">
        <div class="section-header"><div class="section-title">Churn per Kelompok Usia</div><span class="section-badge">Bar</span></div>
        <div class="chart-wrap"><canvas id="ageChart"></canvas></div>
      </div>
      <div class="section-card">
        <div class="section-header"><div class="section-title">Churn per Jenis Kontrak</div><span class="section-badge">Donut</span></div>
        <div class="chart-wrap"><canvas id="contractChart"></canvas></div>
      </div>
    </div>

    <div class="section-card">
      <div class="section-header"><div class="section-title">Detail Churn per Segmen Subscription</div></div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Subscription</th><th>Total Customer</th><th>Total Churn</th><th>Total Aktif</th><th>Churn Rate</th><th>Risiko</th></tr></thead>
          <tbody>
            <tr>
              <td><span class="badge badge-basic">Basic</span></td>
              <td>1,789</td><td><span class="badge badge-churn">521</span></td><td><span class="badge badge-active">1,268</span></td>
              <td><div style="display:flex;align-items:center;gap:10px;"><div style="flex:1;height:5px;background:rgba(255,255,255,0.06);border-radius:3px;"><div style="width:29.1%;height:100%;background:#10b981;border-radius:3px;"></div></div><strong class="risk-low">29.1%</strong></div></td>
              <td><span class="risk-low">Rendah</span></td>
            </tr>
            <tr>
              <td><span class="badge badge-standard">Standard</span></td>
              <td>1,641</td><td><span class="badge badge-churn">437</span></td><td><span class="badge badge-active">1,204</span></td>
              <td><div style="display:flex;align-items:center;gap:10px;"><div style="flex:1;height:5px;background:rgba(255,255,255,0.06);border-radius:3px;"><div style="width:26.6%;height:100%;background:#10b981;border-radius:3px;"></div></div><strong class="risk-low">26.6%</strong></div></td>
              <td><span class="risk-low">Rendah</span></td>
            </tr>
            <tr>
              <td><span class="badge badge-premium">Premium</span></td>
              <td>1,540</td><td><span class="badge badge-churn">360</span></td><td><span class="badge badge-active">1,180</span></td>
              <td><div style="display:flex;align-items:center;gap:10px;"><div style="flex:1;height:5px;background:rgba(255,255,255,0.06);border-radius:3px;"><div style="width:23.4%;height:100%;background:#10b981;border-radius:3px;"></div></div><strong class="risk-low">23.4%</strong></div></td>
              <td><span class="risk-low">Rendah</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="section-card">
      <div class="section-header"><div class="section-title">Customer Berisiko Tinggi (Churn + High Delay + High Calls)</div></div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Customer ID</th><th>Gender</th><th>Usia</th><th>Subscription</th><th>Total Spend</th><th>Support Calls</th><th>Payment Delay</th><th>Tenure</th></tr></thead>
          <tbody id="highrisk-tbody"></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ========== TAMBAH PAGE ========== -->
  <div class="page-section" id="page-tambah">
    <div class="page-header">
      <div>
        <div class="page-title">Tambah Customer</div>
        <div class="page-sub">Input data customer baru ke sistem</div>
      </div>
      <a href="#" class="btn-secondary" onclick="showPage('customers',null)">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        Kembali
      </a>
    </div>

    <div id="form-alert"></div>

    <div class="section-card" style="max-width:820px;">
      <div class="section-header">
        <div class="section-title">Form Data Customer</div>
        <span class="section-badge">* Wajib diisi</span>
      </div>
      <div class="form-grid">
        <div class="form-group"><label class="form-label">Customer ID *</label><input type="text" id="f-id" class="form-input" placeholder="Contoh: CUST5001"></div>
        <div class="form-group"><label class="form-label">Usia *</label><input type="number" id="f-age" class="form-input" placeholder="18 – 90" min="18" max="90"></div>
        <div class="form-group"><label class="form-label">Gender *</label>
          <select id="f-gender" class="form-select form-input">
            <option value="">Pilih Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
          </select>
        </div>
        <div class="form-group"><label class="form-label">Tenure (Bulan) *</label><input type="number" id="f-tenure" class="form-input" placeholder="Lama berlangganan" min="0"></div>
        <div class="form-group"><label class="form-label">Usage Frequency *</label><input type="number" id="f-usage" class="form-input" placeholder="Frekuensi penggunaan" min="0"></div>
        <div class="form-group"><label class="form-label">Support Calls *</label><input type="number" id="f-calls" class="form-input" placeholder="Jumlah panggilan support" min="0"></div>
        <div class="form-group"><label class="form-label">Payment Delay (Hari) *</label><input type="number" id="f-delay" class="form-input" placeholder="Keterlambatan bayar" min="0"></div>
        <div class="form-group"><label class="form-label">Total Spend ($) *</label><input type="number" id="f-spend" class="form-input" placeholder="Total pengeluaran" min="0" step="0.01"></div>
        <div class="form-group"><label class="form-label">Subscription Type *</label>
          <select id="f-sub" class="form-select form-input">
            <option value="">Pilih Tipe</option>
            <option value="Basic">Basic</option>
            <option value="Standard">Standard</option>
            <option value="Premium">Premium</option>
          </select>
        </div>
        <div class="form-group"><label class="form-label">Contract Length *</label>
          <select id="f-contract" class="form-select form-input">
            <option value="">Pilih Kontrak</option>
            <option value="Monthly">Monthly</option>
            <option value="Quarterly">Quarterly</option>
            <option value="Annual">Annual</option>
          </select>
        </div>
        <div class="form-group"><label class="form-label">Last Interaction (Hari) *</label><input type="number" id="f-last" class="form-input" placeholder="Hari sejak interaksi terakhir" min="0"></div>
        <div class="form-group"><label class="form-label">Status Churn *</label>
          <select id="f-churn" class="form-select form-input">
            <option value="">Pilih Status</option>
            <option value="0">Aktif (Tidak Churn)</option>
            <option value="1">Churn</option>
          </select>
        </div>
      </div>
      <div style="margin-top:24px; display:flex; gap:12px;">
        <button class="btn-primary" onclick="submitForm()">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          Simpan Customer
        </button>
        <button class="btn-secondary" onclick="showPage('customers',null)">Batal</button>
      </div>
    </div>
  </div>

</div><!-- /main -->

<script>
// ==============================
// SAMPLE DATA (demo tanpa backend)
// ==============================
const SAMPLE = [
  {id:'CUST001',gender:'Male',age:42,tenure:24,sub:'Premium',contract:'Annual',spend:680.50,calls:3,delay:5,churn:0},
  {id:'CUST002',gender:'Female',age:29,tenure:8,sub:'Basic',contract:'Monthly',spend:210.00,calls:8,delay:27,churn:1},
  {id:'CUST003',gender:'Male',age:55,tenure:36,sub:'Standard',contract:'Quarterly',spend:490.75,calls:2,delay:3,churn:0},
  {id:'CUST004',gender:'Female',age:33,tenure:12,sub:'Premium',contract:'Annual',spend:720.00,calls:1,delay:0,churn:0},
  {id:'CUST005',gender:'Male',age:47,tenure:5,sub:'Basic',contract:'Monthly',spend:155.20,calls:9,delay:31,churn:1},
  {id:'CUST006',gender:'Female',age:38,tenure:18,sub:'Standard',contract:'Quarterly',spend:380.90,calls:4,delay:12,churn:0},
  {id:'CUST007',gender:'Male',age:62,tenure:48,sub:'Premium',contract:'Annual',spend:890.00,calls:0,delay:1,churn:0},
  {id:'CUST008',gender:'Female',age:25,tenure:3,sub:'Basic',contract:'Monthly',spend:99.99,calls:11,delay:40,churn:1},
  {id:'CUST009',gender:'Male',age:44,tenure:15,sub:'Standard',contract:'Monthly',spend:330.00,calls:5,delay:18,churn:1},
  {id:'CUST010',gender:'Female',age:31,tenure:22,sub:'Premium',contract:'Annual',spend:650.00,calls:2,delay:4,churn:0},
  {id:'CUST011',gender:'Male',age:50,tenure:30,sub:'Basic',contract:'Quarterly',spend:270.00,calls:6,delay:22,churn:1},
  {id:'CUST012',gender:'Female',age:27,tenure:6,sub:'Standard',contract:'Monthly',spend:195.00,calls:7,delay:28,churn:1},
  {id:'CUST013',gender:'Male',age:39,tenure:20,sub:'Premium',contract:'Annual',spend:760.00,calls:1,delay:2,churn:0},
  {id:'CUST014',gender:'Female',age:58,tenure:42,sub:'Standard',contract:'Annual',spend:510.00,calls:3,delay:7,churn:0},
  {id:'CUST015',gender:'Male',age:35,tenure:9,sub:'Basic',contract:'Monthly',spend:185.00,calls:10,delay:35,churn:1},
  {id:'CUST016',gender:'Female',age:46,tenure:28,sub:'Premium',contract:'Quarterly',spend:610.00,calls:2,delay:6,churn:0},
  {id:'CUST017',gender:'Male',age:23,tenure:2,sub:'Basic',contract:'Monthly',spend:89.00,calls:12,delay:45,churn:1},
  {id:'CUST018',gender:'Female',age:52,tenure:38,sub:'Standard',contract:'Annual',spend:440.00,calls:3,delay:8,churn:0},
  {id:'CUST019',gender:'Male',age:40,tenure:14,sub:'Premium',contract:'Quarterly',spend:700.00,calls:4,delay:10,churn:0},
  {id:'CUST020',gender:'Female',age:34,tenure:11,sub:'Basic',contract:'Monthly',spend:165.00,calls:9,delay:30,churn:1},
];

// ==============================
// CLOCK
// ==============================
function updateClock() {
  const now = new Date();
  document.getElementById('clock').textContent = now.toLocaleTimeString('id-ID');
}
updateClock(); setInterval(updateClock, 1000);

// ==============================
// PAGE NAVIGATION
// ==============================
function showPage(name, el) {
  document.querySelectorAll('.page-section').forEach(p => p.classList.remove('active'));
  document.getElementById('page-' + name).classList.add('active');
  document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
  if (el) el.classList.add('active');
  else {
    document.querySelectorAll('.nav-link').forEach(l => {
      if (l.getAttribute('onclick') && l.getAttribute('onclick').includes("'" + name + "'")) l.classList.add('active');
    });
  }
  if (name === 'customers') renderCustomers();
  return false;
}

// ==============================
// ANIMATED COUNTERS
// ==============================
function animateCounter(el, target, prefix='', suffix='') {
  let start = 0; const dur = 1200; const step = 16;
  const inc = target / (dur / step);
  const t = setInterval(() => {
    start += inc;
    if (start >= target) { start = target; clearInterval(t); }
    el.textContent = prefix + Math.floor(start).toLocaleString() + suffix;
  }, step);
}

window.addEventListener('load', () => {
  document.querySelectorAll('[data-count]').forEach(el => {
    animateCounter(el, parseInt(el.dataset.count));
  });
  // Revenue
  const realRevenue = <?= (int)$revenue ?>;
  let rev = 0; const revT = setInterval(() => {
    rev += Math.ceil(realRevenue / 80);
    if (rev >= realRevenue) { rev = realRevenue; clearInterval(revT); }
    document.getElementById('revenue-kpi').textContent = '$' + Math.floor(rev).toLocaleString();
  }, 16);

  // Stat bars animate
  setTimeout(() => {
    document.querySelectorAll('.stat-bar[data-w]').forEach(b => {
      b.style.width = b.dataset.w + '%';
    });
  }, 400);

  renderRecentTable();
  renderCharts();
  renderHighRisk();
  renderCustomers();
});

// ==============================
// RECENT TABLE (Dashboard)
// ==============================
function renderRecentTable() {
  const tbody = document.getElementById('recent-tbody');
  SAMPLE.slice(0, 10).forEach(r => {
    const subClass = r.sub.toLowerCase();
    const churnBadge = r.churn ? '<span class="badge badge-churn">Churn</span>' : '<span class="badge badge-active">Aktif</span>';
    tbody.innerHTML += `<tr>
      <td><strong style="color:#93c5fd;font-family:'JetBrains Mono',monospace;font-size:12px;">${r.id}</strong></td>
      <td style="color:var(--text2);">${r.gender}</td>
      <td>${r.age} th</td>
      <td><span class="badge badge-${subClass}">${r.sub}</span></td>
      <td style="font-size:12px;">${r.contract}</td>
      <td><strong>$${r.spend.toFixed(2)}</strong></td>
      <td>${churnBadge}</td>
      <td>
        <a href="#" class="action-btn btn-edit">
          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Edit
        </a>
        <a href="#" class="action-btn btn-delete" onclick="return confirm('Hapus customer ${r.id}?')">
          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
        </a>
      </td>
    </tr>`;
  });
}

// ==============================
// CUSTOMERS TABLE with FILTER
// ==============================
let filteredData = [...SAMPLE];
let currentPage = 1;
const PER_PAGE = 8;

function filterCustomers() {
  const q = document.getElementById('search-input').value.toLowerCase();
  const sub = document.getElementById('filter-sub').value;
  const churn = document.getElementById('filter-churn').value;
  filteredData = SAMPLE.filter(r => {
    const matchQ = !q || r.id.toLowerCase().includes(q) || r.gender.toLowerCase().includes(q);
    const matchSub = !sub || r.sub === sub;
    const matchChurn = churn === '' || r.churn == churn;
    return matchQ && matchSub && matchChurn;
  });
  currentPage = 1;
  renderCustomers();
}

function resetFilter() {
  document.getElementById('search-input').value = '';
  document.getElementById('filter-sub').value = '';
  document.getElementById('filter-churn').value = '';
  filterCustomers();
}

function renderCustomers() {
  const tbody = document.getElementById('cust-tbody');
  tbody.innerHTML = '';
  document.getElementById('count-label').textContent = filteredData.length + ' customer ditemukan';
  const start = (currentPage - 1) * PER_PAGE;
  const slice = filteredData.slice(start, start + PER_PAGE);
  slice.forEach((r, i) => {
    const subClass = r.sub.toLowerCase();
    const churnBadge = r.churn ? '<span class="badge badge-churn">Churn</span>' : '<span class="badge badge-active">Aktif</span>';
    const callColor = r.calls >= 7 ? '#f87171' : r.calls >= 4 ? '#fbbf24' : '#34d399';
    const delayColor = r.delay >= 20 ? '#f87171' : r.delay >= 10 ? '#fbbf24' : '#34d399';
    tbody.innerHTML += `<tr>
      <td style="color:var(--muted);font-size:11px;">${start + i + 1}</td>
      <td><strong style="color:#93c5fd;font-family:'JetBrains Mono',monospace;font-size:12px;">${r.id}</strong></td>
      <td>${r.gender}</td><td>${r.age}</td><td>${r.tenure} bln</td>
      <td><span class="badge badge-${subClass}">${r.sub}</span></td>
      <td style="font-size:12px;">${r.contract}</td>
      <td><strong>$${r.spend.toFixed(2)}</strong></td>
      <td><span style="color:${callColor};font-weight:600;">${r.calls}x</span></td>
      <td><span style="color:${delayColor};font-weight:600;">${r.delay} hr</span></td>
      <td>${churnBadge}</td>
      <td>
        <a href="#" class="action-btn btn-edit">
          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Edit
        </a>
        <a href="#" class="action-btn btn-delete" onclick="return confirm('Hapus ${r.id}?')">
          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
        </a>
      </td>
    </tr>`;
  });
  renderPagination();
}

function renderPagination() {
  const totalPages = Math.ceil(filteredData.length / PER_PAGE);
  const pag = document.getElementById('pagination');
  pag.innerHTML = '';
  if (totalPages <= 1) return;
  for (let i = Math.max(1, currentPage - 2); i <= Math.min(totalPages, currentPage + 2); i++) {
    const btn = document.createElement('a');
    btn.href = '#';
    btn.className = 'page-btn' + (i === currentPage ? ' active' : '');
    btn.textContent = i;
    btn.onclick = (e) => { e.preventDefault(); currentPage = i; renderCustomers(); };
    pag.appendChild(btn);
  }
}

// ==============================
// HIGH RISK TABLE
// ==============================
function renderHighRisk() {
  const tbody = document.getElementById('highrisk-tbody');
  const highRisk = SAMPLE.filter(r => r.churn === 1 && r.delay > 20 && r.calls > 6).sort((a,b) => b.delay - a.delay);
  highRisk.forEach(r => {
    const subClass = r.sub.toLowerCase();
    tbody.innerHTML += `<tr>
      <td><strong style="color:#f87171;font-family:'JetBrains Mono',monospace;font-size:12px;">${r.id}</strong></td>
      <td>${r.gender}</td><td>${r.age} th</td>
      <td><span class="badge badge-${subClass}">${r.sub}</span></td>
      <td><strong>$${r.spend.toFixed(2)}</strong></td>
      <td><span class="risk-high">${r.calls}x</span></td>
      <td><span class="risk-high">${r.delay} hr</span></td>
      <td>${r.tenure} bln</td>
    </tr>`;
  });
}

// ==============================
// CHARTS
// ==============================
const CHART_DEFAULTS = {
  responsive: true, maintainAspectRatio: false,
  plugins: { legend: { labels: { color: '#94a3b8', font: { size: 11, family: 'Space Grotesk' }, padding: 14 } } },
  scales: {
    x: { ticks: { color: '#64748b', font: { size: 10 } }, grid: { color: 'rgba(255,255,255,0.03)' }, border: { color: 'rgba(255,255,255,0.05)' } },
    y: { ticks: { color: '#64748b', font: { size: 10 } }, grid: { color: 'rgba(255,255,255,0.03)' }, border: { color: 'rgba(255,255,255,0.05)' } }
  }
};

function renderCharts() {
  // Donut
  new Chart(document.getElementById('churnDonut'), {
    type: 'doughnut',
    data: {
      labels: ['Churn', 'Aktif'],
      datasets: [{ data: [<?= (int)$churnN ?>, <?= (int)$aktif ?>], backgroundColor: ['rgba(239,68,68,0.7)', 'rgba(16,185,129,0.7)'], borderColor: ['#ef4444','#10b981'], borderWidth: 2, hoverOffset: 6 }]
    },
    options: {
      responsive: true, maintainAspectRatio: false, cutout: '70%',
      plugins: { legend: { position: 'bottom', labels: { color:'#94a3b8', padding:16, font:{size:11,family:'Space Grotesk'} } } }
    }
  });

  // Subscription grouped bar
  new Chart(document.getElementById('subChart'), {
    type: 'bar',
    data: {
      labels: ['Basic','Standard','Premium'],
      datasets: [
        { label: 'Total', data: [1789,1641,1540], backgroundColor: 'rgba(100,116,139,0.4)', borderColor:'#64748b', borderWidth:1.5, borderRadius:5 },
        { label: 'Churn', data: [521,437,360], backgroundColor: 'rgba(239,68,68,0.45)', borderColor:'#ef4444', borderWidth:1.5, borderRadius:5 }
      ]
    },
    options: { ...JSON.parse(JSON.stringify(CHART_DEFAULTS)) }
  });

  // Gender pie
  new Chart(document.getElementById('genderChart'), {
    type: 'pie',
    data: {
      labels: ['Male (2,480)','Female (2,490)'],
      datasets: [{ data:[2480,2490], backgroundColor:['rgba(59,130,246,0.65)','rgba(6,182,212,0.65)'], borderColor:['#3b82f6','#06b6d4'], borderWidth:2, hoverOffset:5 }]
    },
    options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'bottom', labels:{ color:'#94a3b8', padding:14, font:{size:11,family:'Space Grotesk'} } } } }
  });

  // Age bar
  new Chart(document.getElementById('ageChart'), {
    type: 'bar',
    data: {
      labels: ['< 25','25–34','35–44','45–54','55+'],
      datasets: [
        { label:'Churn', data:[210,340,380,250,138], backgroundColor:'rgba(239,68,68,0.5)', borderColor:'#ef4444', borderWidth:1.5, borderRadius:5 },
        { label:'Aktif', data:[440,720,900,830,762], backgroundColor:'rgba(16,185,129,0.45)', borderColor:'#10b981', borderWidth:1.5, borderRadius:5 }
      ]
    },
    options: { ...JSON.parse(JSON.stringify(CHART_DEFAULTS)) }
  });

  // Contract donut
  new Chart(document.getElementById('contractChart'), {
    type: 'doughnut',
    data: {
      labels: ['Monthly','Quarterly','Annual'],
      datasets: [{ data:[712,380,226], backgroundColor:['rgba(59,130,246,0.65)','rgba(245,158,11,0.65)','rgba(6,182,212,0.65)'], borderColor:['#3b82f6','#f59e0b','#06b6d4'], borderWidth:2, hoverOffset:5 }]
    },
    options: { responsive:true, maintainAspectRatio:false, cutout:'60%', plugins:{ legend:{ position:'bottom', labels:{ color:'#94a3b8', padding:14, font:{size:11,family:'Space Grotesk'} } } } }
  });
}

// ==============================
// FORM SUBMIT (demo)
// ==============================
function submitForm() {
  const id = document.getElementById('f-id').value.trim();
  const age = document.getElementById('f-age').value;
  const gender = document.getElementById('f-gender').value;
  const sub = document.getElementById('f-sub').value;
  const contract = document.getElementById('f-contract').value;
  const churn = document.getElementById('f-churn').value;

  if (!id || !age || !gender || !sub || !contract || churn === '') {
    document.getElementById('form-alert').innerHTML = '<div class="alert alert-error"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> Harap lengkapi semua field yang wajib diisi.</div>';
    return;
  }

  // Add to sample data (demo)
  SAMPLE.unshift({
    id, gender, age: parseInt(age),
    tenure: parseInt(document.getElementById('f-tenure').value) || 0,
    sub, contract,
    spend: parseFloat(document.getElementById('f-spend').value) || 0,
    calls: parseInt(document.getElementById('f-calls').value) || 0,
    delay: parseInt(document.getElementById('f-delay').value) || 0,
    churn: parseInt(churn)
  });

  document.getElementById('form-alert').innerHTML = `<div class="alert alert-success"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Customer <strong>${id}</strong> berhasil ditambahkan! <a href="#" onclick="showPage('customers',null)" style="color:#34d399;margin-left:8px;">Lihat data</a></div>`;

  // Reset form
  ['f-id','f-age','f-gender','f-tenure','f-usage','f-calls','f-delay','f-spend','f-sub','f-contract','f-last','f-churn'].forEach(fid => {
    const el = document.getElementById(fid);
    if (el) el.value = '';
  });
}
</script>
</body>
</html>
