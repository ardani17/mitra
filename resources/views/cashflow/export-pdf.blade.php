<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Cashflow - {{ now()->format('d M Y') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #2563eb;
        }
        
        .header p {
            margin: 5px 0;
            color: #666;
        }
        
        .summary {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            background-color: #f8fafc;
            padding: 20px;
            border-radius: 8px;
        }
        
        .summary-item {
            text-align: center;
            flex: 1;
        }
        
        .summary-item h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #666;
        }
        
        .summary-item .amount {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }
        
        .income { color: #16a34a; }
        .expense { color: #dc2626; }
        .balance { color: #2563eb; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #334155;
        }
        
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        .type-income { color: #16a34a; font-weight: bold; }
        .type-expense { color: #dc2626; font-weight: bold; }
        
        .status-confirmed { 
            background-color: #dcfce7; 
            color: #166534; 
            padding: 2px 6px; 
            border-radius: 4px; 
            font-size: 10px;
        }
        
        .status-pending { 
            background-color: #fef3c7; 
            color: #92400e; 
            padding: 2px 6px; 
            border-radius: 4px; 
            font-size: 10px;
        }
        
        .status-cancelled { 
            background-color: #fee2e2; 
            color: #991b1b; 
            padding: 2px 6px; 
            border-radius: 4px; 
            font-size: 10px;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
            font-size: 10px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Cashflow</h1>
        <p>Periode: {{ request('start_date', now()->startOfMonth()->format('d M Y')) }} - {{ request('end_date', now()->endOfMonth()->format('d M Y')) }}</p>
        <p>Dicetak pada: {{ now()->format('d M Y H:i:s') }}</p>
    </div>

    <div class="summary">
        <div class="summary-item">
            <h3>Total Pemasukan</h3>
            <p class="amount income">Rp {{ number_format($summary['total_income'], 0, ',', '.') }}</p>
        </div>
        <div class="summary-item">
            <h3>Total Pengeluaran</h3>
            <p class="amount expense">Rp {{ number_format($summary['total_expense'], 0, ',', '.') }}</p>
        </div>
        <div class="summary-item">
            <h3>Saldo Bersih</h3>
            <p class="amount balance">Rp {{ number_format($summary['balance'], 0, ',', '.') }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="10%">Tanggal</th>
                <th width="25%">Deskripsi</th>
                <th width="15%">Kategori</th>
                <th width="15%">Proyek</th>
                <th width="8%">Tipe</th>
                <th width="12%">Jumlah</th>
                <th width="8%">Status</th>
                <th width="7%">Metode</th>
            </tr>
        </thead>
        <tbody>
            @forelse($entries as $entry)
                <tr>
                    <td class="text-center">{{ $entry->transaction_date->format('d/m/Y') }}</td>
                    <td>{{ $entry->description }}</td>
                    <td>{{ $entry->category->name }}</td>
                    <td>{{ $entry->project?->name ?? '-' }}</td>
                    <td class="text-center">
                        <span class="{{ $entry->type === 'income' ? 'type-income' : 'type-expense' }}">
                            {{ $entry->formatted_type }}
                        </span>
                    </td>
                    <td class="text-right">Rp {{ number_format($entry->amount, 0, ',', '.') }}</td>
                    <td class="text-center">
                        <span class="status-{{ $entry->status }}">
                            {{ ucfirst($entry->status) }}
                        </span>
                    </td>
                    <td class="text-center">{{ $entry->payment_method ?? '-' }}</td>
                </tr>
                @if($entry->notes)
                    <tr>
                        <td></td>
                        <td colspan="7" style="font-style: italic; color: #666; font-size: 10px;">
                            Catatan: {{ $entry->notes }}
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 40px; color: #666;">
                        Tidak ada data transaksi untuk periode ini
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($entries->count() > 0)
        <div style="margin-top: 30px;">
            <h3>Ringkasan Statistik</h3>
            <table style="width: 50%;">
                <tr>
                    <td><strong>Total Transaksi:</strong></td>
                    <td class="text-right">{{ $entries->count() }}</td>
                </tr>
                <tr>
                    <td><strong>Transaksi Pemasukan:</strong></td>
                    <td class="text-right">{{ $entries->where('type', 'income')->count() }}</td>
                </tr>
                <tr>
                    <td><strong>Transaksi Pengeluaran:</strong></td>
                    <td class="text-right">{{ $entries->where('type', 'expense')->count() }}</td>
                </tr>
                <tr>
                    <td><strong>Transaksi Terkonfirmasi:</strong></td>
                    <td class="text-right">{{ $entries->where('status', 'confirmed')->count() }}</td>
                </tr>
                <tr>
                    <td><strong>Transaksi Pending:</strong></td>
                    <td class="text-right">{{ $entries->where('status', 'pending')->count() }}</td>
                </tr>
            </table>
        </div>

        @if($entries->where('type', 'income')->count() > 0)
            <div style="margin-top: 20px;">
                <h4>Breakdown Pemasukan per Kategori</h4>
                <table style="width: 60%;">
                    @foreach($entries->where('type', 'income')->groupBy('category.name') as $categoryName => $categoryEntries)
                        <tr>
                            <td>{{ $categoryName }}</td>
                            <td class="text-right">{{ $categoryEntries->count() }} transaksi</td>
                            <td class="text-right income">Rp {{ number_format($categoryEntries->sum('amount'), 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif

        @if($entries->where('type', 'expense')->count() > 0)
            <div style="margin-top: 20px;">
                <h4>Breakdown Pengeluaran per Kategori</h4>
                <table style="width: 60%;">
                    @foreach($entries->where('type', 'expense')->groupBy('category.name') as $categoryName => $categoryEntries)
                        <tr>
                            <td>{{ $categoryName }}</td>
                            <td class="text-right">{{ $categoryEntries->count() }} transaksi</td>
                            <td class="text-right expense">Rp {{ number_format($categoryEntries->sum('amount'), 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif
    @endif

    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh Sistem Manajemen Keuangan</p>
        <p>{{ config('app.name') }} - {{ now()->format('Y') }}</p>
    </div>
</body>
</html>