<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $salary_release->employee->name }}</title>
    <style>
        /* Print-specific styles */
        @media print {
            body {
                margin: 0;
                padding: 20mm;
                font-family: 'Times New Roman', serif;
                font-size: 12pt;
                line-height: 1.4;
                color: #000;
                background: white;
            }
            
            .no-print {
                display: none !important;
            }
            
            .page-break {
                page-break-before: always;
            }
            
            table {
                page-break-inside: auto;
            }
            
            thead {
                display: table-header-group;
            }
            
            tbody tr {
                page-break-inside: avoid;
            }
            
            .deduction-details table {
                page-break-inside: avoid;
            }
        }
        
        /* Screen styles */
        body {
            font-family: 'Times New Roman', serif;
            font-size: 14px;
            line-height: 1.5;
            color: #333;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        
        .print-container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            padding: 20mm;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            min-height: 297mm;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .employee-info {
            margin-bottom: 25px;
        }
        
        .employee-info table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .employee-info td {
            padding: 8px 0;
            vertical-align: top;
        }
        
        .employee-info .label {
            width: 150px;
            font-weight: bold;
        }
        
        .employee-info .colon {
            width: 20px;
            text-align: center;
        }
        
        .financial-summary {
            margin: 30px 0;
            border: 2px solid #333;
            padding: 20px;
            background: #f9f9f9;
        }
        
        .financial-summary h3 {
            margin: 0 0 15px 0;
            text-align: center;
            font-size: 16px;
            text-transform: uppercase;
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
        }
        
        .financial-summary table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .financial-summary td {
            padding: 10px 0;
            font-size: 14px;
        }
        
        .financial-summary .label {
            width: 200px;
            font-weight: bold;
        }
        
        .financial-summary .amount {
            text-align: right;
            font-weight: bold;
        }
        
        .net-amount {
            border-top: 2px solid #333;
            font-size: 16px !important;
            font-weight: bold !important;
        }
        
        .daily-recap {
            margin-top: 30px;
        }
        
        .daily-recap h3 {
            margin: 0 0 15px 0;
            font-size: 16px;
            text-transform: uppercase;
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
        }
        
        .daily-list {
            margin: 0;
            padding: 0;
            list-style: none;
            counter-reset: day-counter;
        }
        
        .daily-list li {
            counter-increment: day-counter;
            margin-bottom: 8px;
            padding: 8px;
            border: 1px solid #ddd;
            background: #fafafa;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .daily-list li::before {
            content: counter(day-counter) ". ";
            font-weight: bold;
            margin-right: 10px;
        }
        
        .daily-date {
            font-weight: bold;
        }
        
        .daily-status {
            margin: 0 15px;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
        }
        
        .status-hadir {
            background: #d4edda;
            color: #155724;
        }
        
        .status-sakit {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-alpha {
            background: #f8d7da;
            color: #721c24;
        }
        
        .daily-amount {
            font-weight: bold;
            color: #28a745;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .print-button:hover {
            background: #0056b3;
        }
        
        .footer {
            margin-top: 40px;
            text-align: right;
        }
        
        .signature-area {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            text-align: center;
            width: 200px;
        }
        
        .signature-line {
            border-bottom: 1px solid #333;
            height: 60px;
            margin-bottom: 10px;
        }
        
        /* Table styles for detailed breakdown */
        .detailed-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #ddd;
            font-size: 12px;
            margin-top: 15px;
        }
        
        .detailed-table th,
        .detailed-table td {
            padding: 6px 8px;
            border: 1px solid #ddd;
            text-align: left;
        }
        
        .detailed-table th {
            background: #f5f5f5;
            font-weight: bold;
        }
        
        .detailed-table .text-right {
            text-align: right;
        }
        
        .detailed-table .text-center {
            text-align: center;
        }
        
        .detailed-table tbody tr:nth-child(even) {
            background: #fafafa;
        }
        
        .detailed-table tfoot tr {
            background: #f5f5f5;
            font-weight: bold;
        }
        
        @media print {
            .detailed-table {
                font-size: 10px;
            }
            
            .detailed-table th,
            .detailed-table td {
                padding: 4px 6px;
            }
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print Slip Gaji
    </button>
    
    <div class="print-container">
        <!-- Header -->
        <div class="header">
            <h1>Slip Gaji</h1>
        </div>
        
        <!-- Employee Information -->
        <div class="employee-info">
            <table>
                <tr>
                    <td class="label">Karyawan</td>
                    <td class="colon">:</td>
                    <td>{{ $salary_release->employee->name }}</td>
                </tr>
                @if($salary_release->employee->position)
                <tr>
                    <td class="label">Posisi</td>
                    <td class="colon">:</td>
                    <td>{{ $salary_release->employee->position }}</td>
                </tr>
                @endif
                <tr>
                    <td class="label">Periode</td>
                    <td class="colon">:</td>
                    <td>{{ $salary_release->period_label }}</td>
                </tr>
                <tr>
                    <td class="label">Kode Rilis</td>
                    <td class="colon">:</td>
                    <td>{{ $salary_release->release_code }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Cetak</td>
                    <td class="colon">:</td>
                    <td>{{ now()->format('d/m/Y H:i') }}</td>
                </tr>
            </table>
        </div>
        
        <!-- Financial Summary -->
        <div class="financial-summary">
            <h3>Ringkasan Finansial</h3>
            <table>
                <tr>
                    <td class="label">Total Kotor</td>
                    <td class="colon">:</td>
                    <td class="amount">{{ $salary_release->formatted_total_amount }}</td>
                </tr>
                <tr>
                    <td class="label">Potongan</td>
                    <td class="colon">:</td>
                    <td class="amount">{{ $salary_release->formatted_deductions }}</td>
                </tr>
                <tr class="net-amount">
                    <td class="label">Total Bersih</td>
                    <td class="colon">:</td>
                    <td class="amount">{{ $salary_release->formatted_net_amount }}</td>
                </tr>
            </table>
        </div>
        
        <!-- Deduction Details (if any) -->
        @if($salary_release->deductions > 0)
        <div class="deduction-details" style="margin: 30px 0;">
            <h3 style="margin: 0 0 15px 0; font-size: 16px; text-transform: uppercase; border-bottom: 1px solid #333; padding-bottom: 10px;">Rincian Potongan</h3>
            <table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">
                <tr style="background: #f5f5f5;">
                    <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">Jenis Potongan</td>
                    <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; text-align: right;">Jumlah</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd;">
                        {{ $salary_release->notes ? $salary_release->notes : 'Potongan Lain-lain' }}
                    </td>
                    <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">{{ $salary_release->formatted_deductions }}</td>
                </tr>
                <tr style="background: #f9f9f9; font-weight: bold;">
                    <td style="padding: 8px; border: 1px solid #ddd;">Total Potongan</td>
                    <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">{{ $salary_release->formatted_deductions }}</td>
                </tr>
            </table>
        </div>
        @endif

        <!-- Daily Salary Recap -->
        <div class="daily-recap">
            <h3>Lampiran Rekap Gaji</h3>
            <p style="margin-bottom: 15px; font-style: italic;">Sesuai tanggal rincianya:</p>
            
            @if($salary_release->dailySalaries->count() > 0)
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd; font-size: 12px;">
                        <thead>
                            <tr style="background: #f5f5f5;">
                                <th style="padding: 8px; border: 1px solid #ddd; text-align: left;">No.</th>
                                <th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Tanggal</th>
                                <th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Status Kehadiran</th>
                                <th style="padding: 8px; border: 1px solid #ddd; text-align: right;">Gaji Pokok</th>
                                <th style="padding: 8px; border: 1px solid #ddd; text-align: right;">Tunjangan</th>
                                <th style="padding: 8px; border: 1px solid #ddd; text-align: right;">Lembur</th>
                                <th style="padding: 8px; border: 1px solid #ddd; text-align: right;">Potongan</th>
                                <th style="padding: 8px; border: 1px solid #ddd; text-align: right;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salary_release->dailySalaries->sortBy('work_date') as $index => $salary)
                                <tr style="{{ $index % 2 == 0 ? 'background: #fafafa;' : '' }}">
                                    <td style="padding: 6px 8px; border: 1px solid #ddd;">{{ $index + 1 }}</td>
                                    <td style="padding: 6px 8px; border: 1px solid #ddd;">
                                        <div style="font-weight: bold;">{{ $salary->work_date->format('d/m/Y') }}</div>
                                        <div style="font-size: 10px; color: #666;">
                                            @php
                                                $dayNames = [
                                                    'Sunday' => 'Minggu',
                                                    'Monday' => 'Senin',
                                                    'Tuesday' => 'Selasa',
                                                    'Wednesday' => 'Rabu',
                                                    'Thursday' => 'Kamis',
                                                    'Friday' => 'Jumat',
                                                    'Saturday' => 'Sabtu'
                                                ];
                                                $englishDay = $salary->work_date->format('l');
                                            @endphp
                                            {{ $dayNames[$englishDay] ?? $englishDay }}
                                        </div>
                                    </td>
                                    <td style="padding: 6px 8px; border: 1px solid #ddd; text-align: center;">
                                        @php
                                            $status = strtolower($salary->attendance_status);
                                            $statusNames = [
                                                'hadir' => 'Hadir',
                                                'present' => 'Hadir',
                                                'sakit' => 'Sakit',
                                                'sick' => 'Sakit',
                                                'alpha' => 'Libur',
                                                'absent' => 'Libur',
                                                'late' => 'Telat'
                                            ];
                                            $statusColors = [
                                                'hadir' => 'background: #28a745; color: white;',
                                                'present' => 'background: #28a745; color: white;',
                                                'sakit' => 'background: #007bff; color: white;',
                                                'sick' => 'background: #007bff; color: white;',
                                                'alpha' => 'background: #dc3545; color: white;',
                                                'absent' => 'background: #dc3545; color: white;',
                                                'late' => 'background: #ffc107; color: #212529;'
                                            ];
                                        @endphp
                                        <span style="padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: bold; {{ $statusColors[$status] ?? 'background: #6c757d; color: white;' }}">
                                            {{ $statusNames[$status] ?? ucfirst($salary->attendance_status) }}
                                        </span>
                                    </td>
                                    <td style="padding: 6px 8px; border: 1px solid #ddd; text-align: right;">
                                        {{ $salary->formatted_basic_salary }}
                                    </td>
                                    <td style="padding: 6px 8px; border: 1px solid #ddd; text-align: right;">
                                        <div style="font-size: 10px;">
                                            <div>Makan: {{ $salary->formatted_meal_allowance }}</div>
                                            <div style="color: {{ $salary->attendance_bonus >= 0 ? '#28a745' : '#dc3545' }};">
                                                Absen: {{ $salary->formatted_attendance_bonus }}
                                            </div>
                                            <div>Pulsa: {{ $salary->formatted_phone_allowance }}</div>
                                        </div>
                                    </td>
                                    <td style="padding: 6px 8px; border: 1px solid #ddd; text-align: right;">
                                        {{ $salary->formatted_overtime_amount }}
                                    </td>
                                    <td style="padding: 6px 8px; border: 1px solid #ddd; text-align: right; color: #dc3545;">
                                        {{ $salary->formatted_deductions }}
                                    </td>
                                    <td style="padding: 6px 8px; border: 1px solid #ddd; text-align: right; font-weight: bold;">
                                        {{ $salary->formatted_total_amount }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background: #f5f5f5; font-weight: bold;">
                                <td colspan="7" style="padding: 8px; border: 1px solid #ddd; text-align: right;">
                                    Total Keseluruhan:
                                </td>
                                <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">
                                    {{ $salary_release->formatted_total_amount }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <p style="text-align: center; color: #666; font-style: italic;">
                    Tidak ada data gaji harian untuk periode ini.
                </p>
            @endif
        </div>
        
        <!-- Footer with Signature -->
        <div class="footer">
            <div class="signature-area">
                <div class="signature-box">
                    <p>Karyawan</p>
                    <div class="signature-line"></div>
                    <p>{{ $salary_release->employee->name }}</p>
                </div>
                <div class="signature-box">
                    <p>Direktur</p>
                    <div class="signature-line"></div>
                    <p>{{ $salary_release->releasedBy->name ?? '________________' }}</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-focus print dialog when page loads (optional)
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>
</html>