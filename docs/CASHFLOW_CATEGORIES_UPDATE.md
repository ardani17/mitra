# Cashflow Categories Update Documentation

## Overview
This document describes the comprehensive update to the cashflow categories system, adding support for debt (hutang), receivables (piutang), and various other income/expense categories.

## Changes Made

### 1. Database Updates

#### Migration: `2025_08_28_000000_add_group_to_cashflow_categories_table.php`
- Added `group` field to categorize entries
- Added `sort_order` field for custom ordering
- Added indexes for better performance

### 2. New Categories Added

#### Income Categories (Pemasukan)

**Proyek Group:**
- Penagihan Proyek (INC_PROJECT_BILLING)
- Penagihan Batch (INC_BATCH_BILLING)

**Hutang & Modal Group:**
- Penerimaan Pinjaman/Hutang (INC_LOAN_RECEIPT)
- Modal Investor (INC_INVESTOR_CAPITAL)
- Modal Awal/Tambahan (INC_INITIAL_CAPITAL)

**Piutang & Tagihan Group:**
- Pembayaran Piutang (INC_RECEIVABLE_PAYMENT)
- Pengembalian Pinjaman (INC_LOAN_RETURN)

**Pendapatan Lainnya Group:**
- Penjualan Aset (INC_ASSET_SALE)
- Sewa/Rental (INC_RENTAL)
- Komisi/Fee (INC_COMMISSION)
- Dividen (INC_DIVIDEND)
- Bunga Bank (INC_BANK_INTEREST)
- Bunga Deposito (INC_DEPOSIT_INTEREST)
- Cashback/Diskon (INC_CASHBACK)
- Klaim Asuransi (INC_INSURANCE_CLAIM)
- Hibah/Bantuan (INC_GRANT)
- Pendapatan Lain-lain (INC_OTHER)

#### Expense Categories (Pengeluaran)

**Proyek Group:**
- Pengeluaran Proyek (EXP_PROJECT)
- Material & Peralatan Proyek (EXP_PROJECT_MATERIAL)

**Hutang & Pinjaman Group:**
- Pembayaran Hutang Pokok (EXP_DEBT_PRINCIPAL)
- Bunga Pinjaman (EXP_LOAN_INTEREST)
- Denda/Penalty (EXP_PENALTY)
- Pemberian Pinjaman (EXP_LOAN_GIVEN)

**Operasional Group:**
- Gaji dan Tunjangan (EXP_SALARY)
- Sewa Kantor/Gudang (EXP_RENT)
- Listrik, Air, Internet (EXP_UTILITIES)
- Transportasi (EXP_TRANSPORT)
- Peralatan dan Supplies Kantor (EXP_OFFICE_SUPPLIES)
- Maintenance/Perbaikan (EXP_MAINTENANCE)
- Asuransi (EXP_INSURANCE)
- Biaya Operasional Lainnya (EXP_OPERATIONAL)

**Aset & Investasi Group:**
- Pembelian Aset (EXP_ASSET_PURCHASE)
- Investasi (EXP_INVESTMENT)

**Pengeluaran Lainnya Group:**
- Pajak dan Retribusi (EXP_TAX)
- Marketing/Promosi (EXP_MARKETING)
- Administrasi Bank (EXP_BANK_ADMIN)
- Legal/Notaris (EXP_LEGAL)
- Konsultan (EXP_CONSULTANT)
- Entertainment (EXP_ENTERTAINMENT)
- CSR/Donasi (EXP_DONATION)
- Pengeluaran Lain-lain (EXP_OTHER)

### 3. New Features

#### Category Management System
- **URL**: `/finance/cashflow-categories`
- Full CRUD operations for categories
- Bulk operations (activate, deactivate, delete)
- Import/Export functionality
- Category statistics and reporting

#### Category Features
- **Grouping**: Categories are organized by groups for better organization
- **Sorting**: Custom sort order for display
- **System Categories**: Protected categories that cannot be deleted
- **Active/Inactive Status**: Categories can be activated or deactivated
- **Usage Tracking**: Track how many transactions use each category

### 4. Files Created/Modified

#### Created Files:
- `app/Http/Controllers/CashflowCategoryController.php` - Category management controller
- `resources/views/cashflow-categories/index.blade.php` - Category list view
- `resources/views/cashflow-categories/create.blade.php` - Create category view
- `resources/views/cashflow-categories/edit.blade.php` - Edit category view
- `resources/views/cashflow-categories/show.blade.php` - Category detail view
- `resources/views/cashflow-categories/_form.blade.php` - Shared form partial

#### Modified Files:
- `app/Models/CashflowCategory.php` - Added group support and helper methods
- `database/seeders/CashflowCategorySeeder.php` - Added all new categories
- `routes/web.php` - Added category management routes

### 5. How to Use

#### Accessing Category Management
1. Login as Finance Manager or Direktur
2. Navigate to Finance menu
3. Click on "Cashflow" 
4. Access "Manajemen Kategori" from the cashflow page

#### Creating New Cashflow Entry
1. Go to Cashflow page
2. Click "Tambah Transaksi"
3. Select Type (Pemasukan/Pengeluaran)
4. Categories will be grouped by their type for easier selection
5. Select the appropriate category from the grouped list

#### Managing Categories
1. Go to Category Management page
2. Use filters to find specific categories
3. Click eye icon to view details
4. Click edit icon to modify (non-system categories only)
5. Use bulk actions for multiple categories

### 6. API Endpoints

#### Category Management
- `GET /finance/cashflow-categories` - List categories
- `GET /finance/cashflow-categories/create` - Create form
- `POST /finance/cashflow-categories` - Store new category
- `GET /finance/cashflow-categories/{id}` - Show category
- `GET /finance/cashflow-categories/{id}/edit` - Edit form
- `PUT /finance/cashflow-categories/{id}` - Update category
- `DELETE /finance/cashflow-categories/{id}` - Delete category
- `POST /finance/cashflow-categories/{id}/toggle` - Toggle active status
- `POST /finance/cashflow-categories/bulk-update` - Bulk operations
- `GET /finance/cashflow-categories-export` - Export to CSV
- `POST /finance/cashflow-categories-import` - Import from CSV
- `GET /finance/cashflow-categories-template` - Download import template

### 7. Business Logic

#### Category Codes
- Income categories use prefix `INC_`
- Expense categories use prefix `EXP_`
- Custom categories should follow this convention

#### System Categories
- Cannot be deleted
- Cannot be deactivated
- Marked with purple badge in UI

#### Category Deletion Rules
- Category must not be a system category
- Category must have zero transactions
- Only non-system categories can be deleted

### 8. Testing

To test the implementation:

1. **Run migrations:**
   ```bash
   php artisan migrate
   ```

2. **Seed categories:**
   ```bash
   php artisan db:seed --class=CashflowCategorySeeder
   ```

3. **Access the system:**
   - Login as Finance Manager or Direktur
   - Navigate to `/finance/cashflow-categories`
   - Test CRUD operations
   - Create cashflow entries with new categories

### 9. Troubleshooting

#### Categories not showing in dropdown
- Check if categories are active
- Verify the type matches (income/expense)
- Run seeder if categories are missing

#### Cannot delete category
- Check if it's a system category
- Verify no transactions are using it
- Use the UI to check usage count

#### Import fails
- Verify CSV format matches template
- Check for duplicate category codes
- Ensure proper encoding (UTF-8)

### 10. Future Enhancements

Potential improvements for future versions:
- Category budgeting
- Category-based financial goals
- Automated categorization rules
- Category hierarchies (parent-child)
- Category-specific approval workflows
- Integration with accounting software
- Advanced reporting by category groups

## Support

For issues or questions regarding the cashflow categories system, please contact the development team or refer to the main application documentation.