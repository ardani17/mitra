# TODO LIST APLIKASI MANAJEMEN PROYEK TELEKOMUNIKASI

## 1. Persiapan Awal
- [x] Membuat proyek Laravel 12 baru
- [x] Konfigurasi database PostgreSQL
- [x] Setup autentikasi pengguna (Laravel Breeze dengan Blade)

## 2. Sistem User Level dan Role Management
- [x] Membuat tabel roles dan role_user
- [x] Implementasi role-based access control (RBAC)
- [x] Membuat middleware untuk setiap role:
  - [x] Direktur (akses penuh ke semua fitur)
  - [x] Project Manager (manajemen proyek, timeline, anggaran)
  - [x] Finance Manager (manajemen penagihan, pendapatan, laporan keuangan)
  - [x] Staf (input data proyek, update status)

## 3. Desain Database
- [x] Membuat migrasi tabel:
  - [x] Users (pengguna)
  - [x] Roles (peran pengguna)
  - [x] Role_user (relasi many-to-many)
  - [x] Companies (perusahaan)
  - [x] Projects (proyek)
  - [x] Project_expenses (pengeluaran proyek)
  - [x] Expense_approvals (sistem approval pengeluaran)
  - [x] Project_activities (aktivitas proyek)
  - [x] Project_timeline (jadwal proyek)
  - [x] Project_billing (penagihan proyek)
  - [x] Project_revenue (pendapatan proyek)
  - [x] Revenue_items (rincian pendapatan)
  - [x] Project_profit_analysis (analisis profit)
  - [x] Import_logs (log import excel)
- [x] **Penyempurnaan struktur proyek:**
  - [x] Menambahkan kode proyek otomatis (PRJ-YYYY-MM-XXX)
  - [x] Menambahkan field nilai jasa dan material (plan & akhir)
  - [x] Memisahkan company info ke user table
  - [x] Update enum values untuk status dan type proyek

## 4. Backend (Laravel MVC)
- [x] Membuat model-model dengan relasi yang sesuai
- [x] Membuat controller untuk semua entitas
- [x] Membuat request untuk validasi form
- [x] Membuat policy untuk setiap model berdasarkan role
- [x] Membuat middleware role-based access control
- [x] Membuat sistem approval untuk pengeluaran:
  - [x] Staf mengajukan expense
  - [x] Finance Manager mereview
  - [x] Direktur & Project Manager memberikan approval
- [x] Membuat sistem perhitungan net profit:
  - [x] Total pendapatan - Total pengeluaran = Net Profit
  - [x] Perhitungan profit margin (di DashboardController)
- [x] Membuat fitur import/export Excel:
  - [x] Import proyek dari Excel (Maatwebsite Excel)
  - [x] Template Excel untuk import proyek
  - [x] Export laporan ke Excel
  - [x] Validasi data import
  - [x] Logging import process

## 5. Frontend dengan Blade (Tampilan Modern)
- [x] Setup Tailwind CSS untuk desain modern
- [x] Membuat layout utama dengan responsive design
- [x] Membuat komponen-komponen:
  - [x] Dashboard utama (berbeda untuk setiap role)
  - [x] Form pembuatan/edit proyek dengan nilai jasa & material
  - [x] Tabel daftar proyek dengan pagination dan kolom nilai
  - [x] Halaman detail proyek dengan informasi lengkap
  - [x] Form edit proyek dengan section nilai akhir
  - [x] Auto-calculation untuk total nilai (jasa + material)
  - [x] Number formatting untuk input nilai rupiah
  - [x] **PERBAIKAN INPUT NILAI:** Fixed masalah input nilai yang menghilangkan angka ribuan
  - [x] **PRIORITAS: Penyempurnaan tampilan proyek:**
    - [x] Redesign halaman daftar proyek dengan filter advanced modern
    - [x] Tambahkan filter advanced (tanggal, budget range, status)
    - [x] Implementasi search real-time dengan debounce
    - [x] Tambahkan sorting untuk semua kolom
    - [x] Progress bar untuk status proyek dengan perhitungan dari timeline
    - [x] Badge modern untuk type dan status
  - [x] **PRIORITAS: Detail proyek dengan tab informasi:**
    - [x] Tab Overview (informasi umum, progress, statistik)
    - [x] Tab Timeline (jadwal dan milestone)
    - [x] Tab Expenses (pengeluaran dan approval)
    - [x] Tab Billing (penagihan dan pendapatan)
    - [x] Tab Activities (log aktivitas)
    - [x] Tab Documents (file dan dokumen)
    - [x] Grafik progress dan budget vs actual
    - [x] Timeline visual dengan milestone
  - [x] Form pengajuan expense dengan workflow approval
  - [x] Panel approval untuk Finance Manager, Direktur, dan Project Manager
  - [x] Form penagihan dan pendapatan dengan rincian
  - [x] Laporan keuangan dengan perhitungan net profit
  - [x] Halaman import/export Excel:
    - [x] Upload template Excel untuk proyek
    - [x] Download template Excel
    - [x] Progress import
    - [x] Log hasil import
  - [x] Halaman dokumentasi aplikasi lengkap
  - [x] **TEMA DAN STYLING:**
    - [x] Implementasi tema biru-putih konsisten di seluruh aplikasi
    - [x] Custom pagination dengan warna biru-putih
    - [x] Header tabel dengan background biru dan teks putih
    - [x] Styling modern untuk semua komponen UI
    - [x] Responsive design untuk mobile dan desktop
  - [x] **PERBAIKAN UI/UX:**
    - [x] Fixed filter lanjutan default hidden di semua halaman (Proyek, Pengeluaran, Penagihan)
    - [x] Konsistensi format rupiah di semua halaman menggunakan formatRupiah()
    - [x] Menambahkan tombol delete proyek untuk role Direktur dengan konfirmasi detail
    - [x] Implementasi cascade delete yang aman untuk proyek dan data terkait
    - [x] Perbaikan format nilai dari "Rp 0.1M" ke format rupiah lengkap
  - [x] **PENYEMPURNAAN PENAGIHAN:**
    - [x] Redesign halaman create billing dengan fitur modern
    - [x] Auto-calculation berdasarkan persentase dari total proyek
    - [x] Template cepat untuk termin pembayaran (25%, 50%, 75%, 100%)
    - [x] Informasi proyek real-time saat memilih proyek
    - [x] Quick actions untuk set tanggal jatuh tempo (+7, +14, +30 hari)
    - [x] Redesign halaman detail billing dengan layout modern
    - [x] Ringkasan penagihan proyek dengan progress bar
    - [x] Status pembayaran dengan notifikasi visual
    - [x] Aksi cepat untuk kirim penagihan dan tandai lunas
    - [x] Perbaikan input amount untuk menangani format rupiah

## 6. Fitur Utama Berdasarkan Role
- [ ] Direktur:
  - [ ] Dashboard overview semua proyek
  - [ ] Approval pengeluaran
  - [ ] Laporan keuangan perusahaan
  - [ ] Analisis profitabilitas
  - [ ] Manajemen user
  - [ ] Import/export data
- [ ] Project Manager:
  - [ ] Manajemen proyek (fiber optic, penanaman tiang, dll)
  - [ ] Approval pengeluaran proyek
  - [ ] Tracking anggaran (plan vs aktual)
  - [ ] Timeline proyek (mulai - selesai)
  - [ ] Progress report
  - [ ] Import proyek
- [ ] Finance Manager:
  - [ ] Manajemen penagihan
  - [ ] Review pengeluaran
  - [ ] Tracking pendapatan
  - [ ] Laporan keuangan
  - [ ] Cash flow analysis
  - [ ] Export laporan
- [ ] Staf:
  - [ ] Input data proyek
  - [ ] Update status proyek
  - [ ] Pengajuan pengeluaran

## 7. Fitur Keuangan dan Laporan
- [x] Perhitungan anggaran plan vs aktual
- [x] Tracking tanggal pengerjaan dan penyelesaian
- [x] Sistem penagihan dengan status (draft, terkirim, lunas)
- [x] Rincian pendapatan proyek untuk perhitungan net profit
- [x] Laporan bulanan/tahunan
- [x] Analisis profit per proyek
- [x] Dashboard keuangan real-time

## 8. Fitur Improvement Perusahaan
- [ ] Dashboard analytics untuk direktur
- [ ] Rekomendasi berdasarkan data historis
- [ ] Perbandingan performa proyek
- [ ] Identifikasi area yang perlu perbaikan
- [ ] Tracking efisiensi biaya
- [ ] Analisis profit margin per proyek/jenis proyek

## 9. Responsif dan Modern UI
- [x] Desain responsif untuk mobile dan desktop
- [x] Penggunaan Tailwind CSS untuk tampilan modern
- [x] Animasi halus dan transisi
- [ ] Dark mode support

## 10. Fitur Tambahan
- [x] Export laporan ke PDF/Excel
- [x] Import proyek dari Excel
- [x] Filter dan pencarian proyek
- [ ] Notifikasi sistem (email/push) untuk approval
- [x] Audit trail untuk perubahan data penting
- [x] History approval pengeluaran
- [x] Log aktivitas user

## 11. Testing dan Deployment
- [ ] Testing fungsionalitas
- [ ] Testing workflow approval
- [ ] Testing role-based access control
- [ ] Testing perhitungan net profit
- [ ] Testing import/export Excel
- [ ] Optimasi performa
- [ ] Dokumentasi user guide

## 12. Deliverables
- [ ] Aplikasi web lengkap
- [ ] Template Excel untuk import proyek
- [ ] Dokumentasi teknis dan user guide
- [ ] Video tutorial penggunaan (opsional)
