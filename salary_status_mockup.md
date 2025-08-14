# Mockup Desain: Fitur Status Gaji Karyawan

## 🎯 Tujuan
Memberikan indikator visual yang jelas di halaman manajemen karyawan untuk menunjukkan status input gaji bulan berjalan, serta popup informatif yang membantu admin mengetahui karyawan mana yang perlu diinput gajinya.

---

## 📋 Opsi Desain 1: Badge + Popup Ringkasan

### A. Indikator Visual di Daftar Karyawan
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│ Manajemen Karyawan                                    [📊 Status Gaji] [+ Tambah] │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ ┌─────────────────────────────────────────────────────────────────────────────┐ │
│ │ Karyawan          │ Kontak        │ Posisi      │ Status Gaji │ Aksi        │ │
│ ├─────────────────────────────────────────────────────────────────────────────┤ │
│ │ 👤 Ahmad Rizki    │ 081234567890  │ Developer   │ ✅ 12/15    │ 👁️ ✏️ 🗑️    │ │
│ │    EMP001         │ ahmad@...     │ IT          │ [LENGKAP]   │             │ │
│ ├─────────────────────────────────────────────────────────────────────────────┤ │
│ │ 👤 Siti Nurhaliza │ 081234567891  │ Designer    │ ⚠️ 8/15     │ 👁️ ✏️ 🗑️    │ │
│ │    EMP002         │ siti@...      │ IT          │ [KURANG]    │             │ │
│ ├─────────────────────────────────────────────────────────────────────────────┤ │
│ │ 👤 Budi Santoso   │ 081234567892  │ Manager     │ ❌ 0/15     │ 👁️ ✏️ 🗑️    │ │
│ │    EMP003         │ budi@...      │ Operations  │ [BELUM]     │             │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### B. Popup Status Gaji (Klik tombol "📊 Status Gaji")
```
┌─────────────────────────────────────────────────────────────────┐
│ 📊 Status Input Gaji - Januari 2025                      [✖️]   │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│ 📈 Ringkasan Bulan Ini:                                        │
│ • Total Karyawan: 15                                           │
│ • Sudah Lengkap: 5 karyawan (33%)                             │
│ • Masih Kurang: 7 karyawan (47%)                              │
│ • Belum Input: 3 karyawan (20%)                               │
│                                                                 │
│ ⚠️ Perlu Perhatian:                                            │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ ❌ Budi Santoso (EMP003) - Belum ada input sama sekali     │ │
│ │ ❌ Rina Wati (EMP007) - Belum ada input sama sekali        │ │
│ │ ❌ Joko Widodo (EMP012) - Belum ada input sama sekali      │ │
│ │ ⚠️ Siti Nurhaliza (EMP002) - Kurang 7 hari (8/15)         │ │
│ │ ⚠️ Andi Pratama (EMP005) - Kurang 5 hari (10/15)          │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│ 📅 Tanggal Terakhir Input: 14 Januari 2025                    │
│                                                                 │
│                                    [📝 Input Gaji] [Tutup]     │
└─────────────────────────────────────────────────────────────────┘
```

---

## 📋 Opsi Desain 2: Progress Bar + Popup Detail

### A. Indikator Visual di Daftar Karyawan
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│ Manajemen Karyawan                                    [📊 Status Gaji] [+ Tambah] │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ ┌─────────────────────────────────────────────────────────────────────────────┐ │
│ │ Karyawan          │ Kontak        │ Posisi      │ Progress Gaji │ Aksi      │ │
│ ├─────────────────────────────────────────────────────────────────────────────┤ │
│ │ 👤 Ahmad Rizki    │ 081234567890  │ Developer   │ ████████████  │ 👁️ ✏️ 🗑️  │ │
│ │    EMP001         │ ahmad@...     │ IT          │ 80% (12/15)   │           │ │
│ ├─────────────────────────────────────────────────────────────────────────────┤ │
│ │ 👤 Siti Nurhaliza │ 081234567891  │ Designer    │ ████████░░░░  │ 👁️ ✏️ 🗑️  │ │
│ │    EMP002         │ siti@...      │ IT          │ 53% (8/15)    │           │ │
│ ├─────────────────────────────────────────────────────────────────────────────┤ │
│ │ 👤 Budi Santoso   │ 081234567892  │ Manager     │ ░░░░░░░░░░░░  │ 👁️ ✏️ ✏️  │ │
│ │    EMP003         │ budi@...      │ Operations  │ 0% (0/15)     │           │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### B. Popup Detail Status (Klik tombol "📊 Status Gaji")
```
┌─────────────────────────────────────────────────────────────────┐
│ 📊 Detail Status Gaji - Januari 2025                     [✖️]   │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│ 📅 Kalender Input Gaji:                                        │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │  S  M  T  W  T  F  S                                        │ │
│ │           1  2  3  4                                        │ │
│ │  5  6  7  8  9 10 11    ✅ = Semua karyawan sudah input    │ │
│ │ 12 13 14 15 16 17 18    ⚠️ = Ada yang belum input          │ │
│ │ 19 20 21 22 23 24 25    ❌ = Belum ada input sama sekali   │ │
│ │ 26 27 28 29 30 31       🔒 = Hari libur/weekend           │ │
│ │                                                             │ │
│ │ ✅✅⚠️⚠️🔒🔒✅⚠️✅✅❌🔒🔒❌⚠️                                │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│ 🎯 Target Bulan Ini: 22 hari kerja                            │
│ 📊 Progress Keseluruhan: 68% (15/22 hari)                     │
│                                                                 │
│                                    [📝 Input Gaji] [Tutup]     │
└─────────────────────────────────────────────────────────────────┘
```

---

## 📋 Opsi Desain 3: Icon Status + Popup Aksi

### A. Indikator Visual di Daftar Karyawan
```
┌─────────────────────────────────────────────────────────────────────────────────┐
│ Manajemen Karyawan                                    [📊 Status Gaji] [+ Tambah] │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ ┌─────────────────────────────────────────────────────────────────────────────┐ │
│ │ Status │ Karyawan          │ Kontak        │ Posisi      │ Gaji    │ Aksi   │ │
│ ├─────────────────────────────────────────────────────────────────────────────┤ │
│ │   🟢   │ 👤 Ahmad Rizki    │ 081234567890  │ Developer   │ Rp 150K │ 👁️ ✏️ 🗑️ │ │
│ │        │    EMP001         │ ahmad@...     │ IT          │         │        │ │
│ ├─────────────────────────────────────────────────────────────────────────────┤ │
│ │   🟡   │ 👤 Siti Nurhaliza │ 081234567891  │ Designer    │ Rp 140K │ 👁️ ✏️ 🗑️ │ │
│ │        │    EMP002         │ siti@...      │ IT          │         │        │ │
│ ├─────────────────────────────────────────────────────────────────────────────┤ │
│ │   🔴   │ 👤 Budi Santoso   │ 081234567892  │ Manager     │ Rp 200K │ 👁️ ✏️ 🗑️ │ │
│ │        │    EMP003         │ budi@...      │ Operations  │         │        │ │
│ └─────────────────────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────────────────┘

Legend: 🟢 Lengkap | 🟡 Kurang | 🔴 Belum Input
```

### B. Popup Aksi Cepat (Klik tombol "📊 Status Gaji")
```
┌─────────────────────────────────────────────────────────────────┐
│ ⚡ Aksi Cepat - Input Gaji Januari 2025               [✖️]      │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│ 🔴 Prioritas Tinggi (Belum Input):                             │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ • Budi Santoso (EMP003)     [📝 Input Sekarang]           │ │
│ │ • Rina Wati (EMP007)        [📝 Input Sekarang]           │ │
│ │ • Joko Widodo (EMP012)      [📝 Input Sekarang]           │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│ 🟡 Perlu Dilengkapi:                                           │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ • Siti Nurhaliza (8/15 hari) [📝 Lengkapi]                │ │
│ │ • Andi Pratama (10/15 hari)  [📝 Lengkapi]                │ │
│ └─────────────────────────────────────────────────────────────┘ │
│                                                                 │
│ 📊 Statistik Cepat:                                           │
│ • Hari kerja bulan ini: 22 hari                               │
│ • Rata-rata input per karyawan: 68%                           │
│ • Estimasi gaji belum diinput: Rp 45.2 juta                   │
│                                                                 │
│                                          [📋 Lihat Detail]     │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🎨 Kode Warna & Styling

### Status Colors:
- 🟢 **Hijau (Lengkap)**: `bg-green-100 text-green-800` - Sudah input ≥90% hari kerja
- 🟡 **Kuning (Kurang)**: `bg-yellow-100 text-yellow-800` - Input 50-89% hari kerja  
- 🔴 **Merah (Belum)**: `bg-red-100 text-red-800` - Input <50% hari kerja

### Progress Bar Colors:
- **Hijau**: 80-100%
- **Kuning**: 50-79%
- **Merah**: 0-49%

---

## 🔧 Fitur Teknis

### 1. Real-time Updates
- Status berubah otomatis saat ada input gaji baru
- Popup refresh setiap 30 detik atau saat ada perubahan

### 2. Quick Actions
- Tombol "Input Sekarang" langsung ke form input gaji
- Tombol "Lengkapi" ke halaman detail karyawan
- Filter cepat berdasarkan status

### 3. Notifikasi
- Toast notification saat status berubah
- Email reminder untuk karyawan yang belum diinput (opsional)

---

## 📱 Responsive Design
- Mobile: Stack layout dengan status di bawah nama
- Tablet: Compact view dengan icon status
- Desktop: Full layout seperti mockup di atas