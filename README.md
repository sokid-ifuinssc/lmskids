# LMS Ujian Online & Rekap Nilai Legger

Sistem Ujian Terintegrasi (CBT - Computer Based Test) berbasis Laravel untuk perguruan tinggi / sekolah dengan 2 peran utama: **Dosen / Admin** dan **Mahasiswa**. Mendukung ujian CPMK, UTS, UAS, bank soal (Pilihan Ganda & Essay otomatis koreksi), import/export Excel, anti-curang (tab-switch tracking & countdown timer), serta rekap nilai legger lengkap.

---

## 🌟 Fitur Utama

### 1. Panel Dosen / Admin
- **Dashboard Ringkasan**: Statistik mahasiswa, kelas, mata kuliah, bank soal, dan daftar ujian aktif.
- **Kelola Mahasiswa**: Input mahasiswa (NIM sebagai username, nama, kelas, password), edit, hapus, reset password, dan filter kelas.
- **Kelola Kelas & Mata Kuliah**: Manajemen data master kelas dan mata kuliah.
- **Bank Soal & Import/Export Excel**:
  - **Pilihan Ganda (PG)**: Input soal, 4 opsi pilihan (A, B, C, D), kunci jawaban, dan bobot point.
  - **Essay**: Input soal, kata kunci jawaban (*keywords*), dan bobot point.
  - **Import & Export Template Excel** (.xlsx) untuk soal PG dan Essay menggunakan PhpSpreadsheet.
- **Kelola Ujian (CPMK, UTS, UAS)**:
  - Buat jadwal ujian (tanggal & jam mulai, tanggal & jam selesai, durasi pengerjaan dalam menit, batas toleransi keluar layar).
  - Pilih/generate soal dari Bank Soal ke dalam ujian.
  - Aktifkan / Nonaktifkan ujian secara instan.
- **Penilaian Otomatis & Koreksi Cerdas**:
  - Soal PG otomatis dikoreksi sistem 100% tepat.
  - Soal Essay dihitung otomatis berdasarkan persentase kemunculan kata kunci jawaban (Dosen tetap dapat menyesuaikan/koreksi manual).
- **Rekap Nilai Legger**:
  - Rekap per kelas & mata kuliah yang menampilkan nilai CPMK 1..n, UTS, UAS, dan rata-rata akhir.
  - Export legger nilai ke format Excel (.xlsx).

### 2. Panel Mahasiswa
- **Daftar Ujian Terjadwal**: Melihat ujian yang tersedia, aktif, selesai, atau terlewat.
- **Ruang Ujian Interaktif (CBT)**:
  - Countdown timer tersinkronisasi otomatis.
  - Navigasi nomor soal fleksibel (ragu-ragu / selesai).
  - Autosave jawaban secara berkala dan perpindahan soal.
  - **Anti-Cheat System**: Deteksi otomatis saat mahasiswa berpindah tab / minimize browser dengan peringatan dan pencatatan pelanggaran.
- **Hasil & Riwayat Nilai**: Menampilkan nilai akhir ujian, status kelulusan, dan feedback dosen.

---

## 🚀 Panduan Instalasi

### 1. Prasyarat
- PHP >= 8.2 (ekstensi `pdo_mysql`, `mbstring`, `openssl`, `zip`, `gd`)
- Composer
- MySQL / MariaDB

### 2. Langkah Instalasi

```bash
# 1. Clone repositori
git clone https://github.com/sokid-ifuinssc/lmskids.git
cd lmskids

# 2. Install dependensi composer
composer install

# 3. Salin file environment & generate app key
cp .env.example .env
php artisan key:generate

# 4. Konfigurasi Database di file .env
# Sesuaikan DB_DATABASE, DB_USERNAME, dan DB_PASSWORD

# 5. Jalankan migrasi dan seeder
php artisan migrate --seed

# 6. Jalankan server lokal
php artisan serve
```

Akses sistem di browser: `http://127.0.0.1:8000`

---

## 🔐 Akun Default (Hasil Seeder)

| Peran | Username / NIM | Password | Keterangan |
| :--- | :--- | :--- | :--- |
| **Dosen / Admin** | `sokidbae` | `password123` | Akun Dosen Utama |
| **Admin Default** | `admin` | `password` | Akun Administrator |
| **Mahasiswa** | `2530801036` | `password` | Mahasiswa Kelas TI-3A |

---

## 🛠️ Lisensi & Pengembang

Dikembangkan untuk kebutuhan sistem evaluasi dan pembelajaran digital perguruan tinggi.
Lisensi: Open-source di bawah lisensi MIT.
