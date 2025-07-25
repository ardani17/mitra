# Dokumentasi Integrasi Sistem Tagihan dengan Proyek

## Overview
Integrasi ini menghubungkan sistem tagihan dengan proyek sehingga status tagihan proyek akan otomatis terupdate ketika ada perubahan pada data tagihan. Setiap proyek akan menampilkan informasi lengkap tentang status tagihan, nomor dokumen, dan progress penagihan.

## Fitur Utama

### 1. Status Tagihan Otomatis
- **Belum Ditagih** (`not_billed`): Proyek belum memiliki tagihan sama sekali
- **Sebagian Ditagih** (`partially_billed`): Proyek sudah ditagih tapi belum 100%
- **Sudah Ditagih** (`fully_billed`): Proyek sudah ditagih 100% atau lebih

### 2. Informasi Tagihan Real-time
- Total jumlah yang sudah ditagih
- Persentase tagihan dari nilai proyek
- Nomor faktur terakhir
- Nomor SP/PO terakhir
- Tanggal tagihan terakhir
- Sisa jumlah yang bisa ditagih

### 3. Integrasi UI
- Badge status tagihan di halaman index proyek
- Progress bar visual untuk status tagihan
- Section khusus informasi tagihan di detail proyek
- Filter proyek berdasarkan status tagihan

## Perubahan Database

### Tabel `projects` - Field Baru
```sql
-- Status tagihan: not_billed, partially_billed, fully_billed
billing_status ENUM('not_billed', 'partially_billed', 'fully_billed') DEFAULT 'not_billed'

-- Total jumlah yang sudah ditagih
total_billed_amount DECIMAL(15,2) DEFAULT 0

-- Persentase tagihan (0-100+)
billing_percentage DECIMAL(5,2) DEFAULT 0

-- Nomor faktur terakhir
latest_invoice_number VARCHAR(255) NULL

-- Nomor SP terakhir  
latest_sp_number VARCHAR(255) NULL

-- Nomor PO terakhir
latest_po_number VARCHAR(255) NULL

-- Tanggal tagihan terakhir
last_billing_date TIMESTAMP NULL

-- Index untuk performa
INDEX idx_projects_billing_status (billing_status)
INDEX idx_projects_billing_percentage (billing_percentage)
```

## Model Enhancements

### Project Model - Method Baru

#### Accessor Methods
```php
// Label status tagihan dalam bahasa Indonesia
getBillingStatusLabelAttribute(): string

// Warna badge untuk UI
getBillingStatusBadgeColorAttribute(): string

// Sisa jumlah yang bisa ditagih
getRemainingBillableAmountAttribute(): float

// Ringkasan keuangan lengkap
getFinancialSummaryAttribute(): array

// Dokumen tagihan terakhir
getLatestBillingDocumentsAttribute(): array
```

#### Status Check Methods
```php
isFullyBilled(): bool      // Cek apakah sudah ditagih penuh
isNotBilled(): bool        // Cek apakah belum ditagih
isPartiallyBilled(): bool  // Cek apakah sebagian ditagih
```

#### Scope Methods
```php
byBillingStatus($status)           // Filter berdasarkan status tagihan
needsBilling()                     // Proyek yang perlu ditagih
completedButNotFullyBilled()       // Proyek selesai tapi belum ditagih penuh
```

#### Core Method
```php
updateBillingStatus(): void
```
Method utama yang menghitung dan update semua field tagihan berdasarkan data billing terkini.

## Observer Pattern

### ProjectBillingObserver
Observer yang otomatis trigger ketika ada perubahan pada `ProjectBilling`:
- **created**: Ketika tagihan baru dibuat
- **updated**: Ketika tagihan diupdate
- **deleted**: Ketika tagihan dihapus
- **restored**: Ketika tagihan direstore
- **forceDeleted**: Ketika tagihan dihapus permanen

Observer akan:
1. Memanggil `updateBillingStatus()` pada proyek terkait
2. Mencatat aktivitas di project activities
3. Menyimpan metadata perubahan

## UI Integration

### Halaman Index Proyek
- Kolom baru "Tagihan" dengan:
  - Badge status tagihan
  - Progress bar visual
  - Persentase dan jumlah ditagih
  - Nomor faktur terakhir

### Halaman Detail Proyek
- Section "Informasi Tagihan" dengan:
  - 4 card informatif (Status, Total Ditagih, Sisa Tagihan, Dokumen)
  - Progress bar dengan persentase
  - Detail dokumen tagihan terakhir (PO, SP, Faktur)

## Performance Optimizations

### Database Indexes
```sql
-- Index untuk query filtering
INDEX idx_projects_billing_status (billing_status)
INDEX idx_projects_billing_percentage (billing_percentage)

-- Composite index untuk query kompleks
INDEX idx_projects_status_billing (status, billing_status)
```

### Eager Loading
```php
// Load relasi billing untuk menghindari N+1 query
Project::with(['billings', 'billings.billingBatch'])->get()
```

### Caching Strategy
- Field billing disimpan di tabel projects untuk akses cepat
- Update hanya ketika ada perubahan billing
- Observer pattern memastikan konsistensi data

## Testing

### Test Coverage
1. **Database Schema**: Verifikasi field baru tersedia
2. **Model Methods**: Test semua method dan accessor baru
3. **Scope Methods**: Test query filtering
4. **Observer**: Test auto-update ketika billing berubah
5. **Integration**: Test integrasi dengan BillingBatch
6. **Data Consistency**: Verifikasi konsistensi data
7. **Performance**: Test performa loading data

### Test Scripts
- `test_billing_integration.php`: Test komprehensif semua fitur
- `update_existing_projects_billing_status.php`: Update data existing

## Workflow Integration

### Skenario Penggunaan

#### 1. Proyek Baru
```
Proyek dibuat → billing_status = 'not_billed'
```

#### 2. Tagihan Pertama
```
Billing dibuat → Observer trigger → updateBillingStatus() → 
Status berubah ke 'partially_billed' atau 'fully_billed'
```

#### 3. Tagihan Tambahan
```
Billing baru/update → Observer trigger → Recalculate status
```

#### 4. Pembatalan Tagihan
```
Billing dihapus → Observer trigger → Recalculate status
```

## Monitoring & Maintenance

### Health Checks
- Jalankan `test_billing_integration.php` secara berkala
- Monitor konsistensi data antara projects dan project_billings
- Check performance query dengan EXPLAIN

### Data Maintenance
```php
// Update semua status billing
Project::chunk(100, function($projects) {
    foreach($projects as $project) {
        $project->updateBillingStatus();
    }
});
```

## API Integration

### Endpoint Baru (Opsional)
```php
// GET /api/projects/{id}/billing-status
// Response: status tagihan lengkap

// PUT /api/projects/{id}/recalculate-billing  
// Action: force recalculate billing status
```

## Security Considerations

### Authorization
- Hanya user dengan role yang tepat bisa melihat informasi tagihan
- Sensitive billing data dilindungi dengan policy

### Data Integrity
- Observer memastikan data selalu konsisten
- Validation pada level model dan database
- Audit trail untuk semua perubahan

## Future Enhancements

### Planned Features
1. **Notifikasi Otomatis**: Alert ketika proyek perlu ditagih
2. **Dashboard Analytics**: Grafik status tagihan semua proyek
3. **Export Reports**: Laporan tagihan per periode
4. **Batch Operations**: Update status multiple proyek sekaligus
5. **Integration API**: Webhook untuk sistem eksternal

### Scalability
- Implementasi queue untuk update batch besar
- Caching Redis untuk data yang sering diakses
- Database partitioning untuk data historis

## Troubleshooting

### Common Issues

#### 1. Status Tidak Update
```bash
# Check observer registration
php artisan tinker
>>> App\Models\ProjectBilling::getObservableEvents()

# Manual update
>>> $project->updateBillingStatus()
```

#### 2. Data Inconsistency
```bash
# Run consistency check
php test_billing_integration.php

# Fix inconsistencies
php update_existing_projects_billing_status.php
```

#### 3. Performance Issues
```sql
-- Check query performance
EXPLAIN SELECT * FROM projects WHERE billing_status = 'partially_billed';

-- Verify indexes
SHOW INDEX FROM projects;
```

## Conclusion

Integrasi ini berhasil menghubungkan sistem tagihan dengan proyek secara real-time, memberikan visibilitas penuh tentang status tagihan setiap proyek. Dengan observer pattern dan caching strategy, sistem tetap performant sambil menjaga konsistensi data.

Semua test menunjukkan hasil positif dan sistem siap untuk production use.
