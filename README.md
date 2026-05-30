# 📊 CRM Analytics — MIS Dashboard
> Tugas UAS Mata Kuliah Business Intelligence A B C 2024  
> Universitas Mulawarman

---

## 📋 Deskripsi Proyek

Sistem **CRM Analytics** berbasis web yang menampilkan dashboard analisis *customer churn* secara real-time. Dibangun dengan PHP + MySQL menggunakan dataset **Customer Churn** (440.833+ record), sistem ini menyediakan fitur manajemen data customer, analisis visual churn, serta operasi CRUD lengkap.

---

## 🚀 Fitur Utama

| Fitur | Keterangan |
|-------|-----------|
| 🔐 Login & Session | Autentikasi admin dengan session PHP |
| 📊 Dashboard | KPI cards + charts real-time dari database |
| 👥 Data Customer | Tabel dengan search, filter, pagination |
| ➕ Tambah Customer | Form input lengkap dengan validasi |
| ✏️ Edit Customer | Update data customer langsung ke DB |
| 🗑️ Hapus Customer | Delete dengan konfirmasi |
| 📈 Churn Analysis | 4 jenis chart + tabel segmen + high-risk list |

---

## 🛠️ Teknologi

| Layer | Stack |
|-------|-------|
| Backend | PHP 7.4+ |
| Database | MySQL 5.7+ / MariaDB |
| Frontend | HTML5, CSS3, JavaScript |
| Charts | Chart.js 4.4 |
| Font | Space Grotesk + JetBrains Mono |
| Server | XAMPP (Apache + MySQL) |

---

## 🗂️ Struktur Folder

```
crm_mis/
│
├── 📄 index.php          ← Dashboard utama (KPI + Charts + Tabel terbaru)
├── 📄 login.php           ← Halaman login
├── 📄 logout.php          ← Handler logout
├── 📄 customers.php       ← Data customer (search, filter, pagination, hapus)
├── 📄 tambah.php          ← Form tambah customer baru
├── 📄 edit.php            ← Form edit customer
├── 📄 hapus.php           ← Handler redirect hapus
├── 📄 analytics.php       ← Halaman analisis churn (chart + high-risk)
├── 📄 koneksi.php         ← Konfigurasi koneksi database
├── 📄 db_setup.sql        ← Script setup database & tabel
│
├── 📁 assets/
│   └── style.css          ← Global stylesheet (dark theme)
│
└── 📁 partials/
    └── sidebar.php        ← Sidebar navigasi (shared component)
```

---

## ⚙️ Cara Menjalankan

### 1. Persiapan
- Install **XAMPP** (Apache + MySQL)
- Pastikan sudah ada dataset `final_customer_churn_csv.xls`

### 2. Copy Project
```
Pindahkan folder crm_mis/ ke:
C:\xampp\htdocs\crm_mis\
```

### 3. Setup Database

**Opsi A — Import via db_setup.sql:**
```bash
# Buka phpMyAdmin → SQL → jalankan isi db_setup.sql
# Atau via terminal:
mysql -u root -p < db_setup.sql
```

**Opsi B — Import Dataset CSV lengkap:**
```sql
-- Setelah membuat tabel via db_setup.sql:
LOAD DATA INFILE 'C:/path/to/final_customer_churn_csv.xls'
INTO TABLE customer_churn
FIELDS TERMINATED BY ','
LINES TERMINATED BY '\n'
IGNORE 1 ROWS
(customerid, age, gender, tenure, usage_frequency, support_calls,
 payment_delay, subscription_type, contract_length, total_spend,
 last_interaction, churn);
```

> ⚠️ Atau gunakan **phpMyAdmin → Import** untuk file CSV tersebut.

### 4. Jalankan Aplikasi
```
Buka browser: http://localhost/crm_mis/
```

### 5. Login
```
Username : admin
Password : admin123
```

---

## 🗄️ Struktur Database

### Tabel `customer_churn`

| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | INT AUTO_INCREMENT | Primary Key |
| customerid | VARCHAR(50) | ID unik customer |
| age | INT | Usia customer |
| gender | VARCHAR(10) | Male / Female |
| tenure | INT | Lama berlangganan (bulan) |
| usage_frequency | INT | Frekuensi penggunaan |
| support_calls | INT | Jumlah panggilan support |
| payment_delay | INT | Keterlambatan bayar (hari) |
| subscription_type | VARCHAR(20) | Basic / Standard / Premium |
| contract_length | VARCHAR(20) | Monthly / Quarterly / Annual |
| total_spend | DECIMAL(10,2) | Total pengeluaran ($) |
| last_interaction | INT | Hari sejak interaksi terakhir |
| churn | TINYINT(1) | 0 = Aktif, 1 = Churn |
| created_at | TIMESTAMP | Waktu ditambahkan |
| updated_at | TIMESTAMP | Waktu diperbarui |

---

## 📊 Tampilan Aplikasi

### Dashboard
- **4 KPI Cards**: Total Customer, Customer Aktif, Total Churn, Total Revenue
- **Donut Chart**: Perbandingan Churn vs Aktif
- **Bar Stats**: Distribusi Subscription (Basic/Standard/Premium)
- **Tabel**: 10 customer terbaru

### Churn Analysis
- **KPI**: Avg Usia, Avg Support Calls, Avg Payment Delay, Avg Tenure
- **Chart 1**: Churn per Subscription Type (Grouped Bar)
- **Chart 2**: Distribusi Gender (Bar)
- **Chart 3**: Churn per Kelompok Usia (Bar)
- **Chart 4**: Churn per Kontrak (Donut)
- **Tabel**: Detail churn rate per segmen
- **High Risk**: Customer berisiko tinggi (churn + delay > 20 hr + calls > 6x)

---

## 👤 Informasi Mahasiswa

| | |
|---|---|
| **Nama** | [Nama Lengkap] |
| **NIM** | [NIM] |
| **Kelas** | Business Intelligence A / B / C |
| **Dosen** | [Nama Dosen] |
| **Semester** | Genap 2024/2025 |
| **Universitas** | Universitas Mulawarman |

---

## 🔒 Keamanan

- Session-based authentication
- Input disanitasi menggunakan `mysqli_real_escape_string()`
- Output di-escape menggunakan `htmlspecialchars()`
- Password di-hash menggunakan SHA-256

---

<div align="center">
  <strong>Universitas Mulawarman — Business Intelligence A B C 2024</strong>
</div>
