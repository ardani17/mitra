<x-app-layout>
    <x-slot name="header">
        <div class="mobile-header sm:flex sm:items-center sm:justify-between">
            <div>
                <h2 class="mobile-header-title sm:font-semibold sm:text-xl sm:text-gray-800 sm:leading-tight">
                    {{ __('Pengaturan Sistem') }}
                </h2>
                <p class="mobile-header-subtitle sm:text-base">Kelola pengaturan aplikasi dan sistem</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mobile-form-container">
                    
                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    <!-- Salary Cut-off Settings -->
                    <div class="mobile-form-section">
                        <div class="mobile-form-section-title text-blue-800">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            Pengaturan Periode Gaji
                        </div>
                        
                        <div class="mb-4">
                            <p class="text-sm text-blue-600 mb-3">
                                Atur periode cut-off gaji karyawan. Periode ini menentukan rentang tanggal untuk perhitungan status gaji.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('settings.update-salary-cutoff') }}">
                            @csrf
                            @method('PATCH')
                            
                            <div class="mobile-filter-row sm:grid sm:grid-cols-2 sm:gap-4">
                                <div class="mobile-form-group">
                                    <label for="salary_cutoff_start_day" class="mobile-form-label">
                                        Tanggal Mulai Periode
                                    </label>
                                    <select name="salary_cutoff_start_day" id="salary_cutoff_start_day" class="mobile-form-select">
                                        @for($i = 1; $i <= 31; $i++)
                                            <option value="{{ $i }}" {{ ($settings['salary_cutoff_start_day'] ?? 11) == $i ? 'selected' : '' }}>
                                                {{ $i }}
                                            </option>
                                        @endfor
                                    </select>
                                    <p class="mobile-form-help">Tanggal mulai periode gaji (1-31)</p>
                                </div>
                                
                                <div class="mobile-form-group">
                                    <label for="salary_cutoff_end_day" class="mobile-form-label">
                                        Tanggal Akhir Periode
                                    </label>
                                    <select name="salary_cutoff_end_day" id="salary_cutoff_end_day" class="mobile-form-select">
                                        @for($i = 1; $i <= 31; $i++)
                                            <option value="{{ $i }}" {{ ($settings['salary_cutoff_end_day'] ?? 10) == $i ? 'selected' : '' }}>
                                                {{ $i }}
                                            </option>
                                        @endfor
                                    </select>
                                    <p class="mobile-form-help">Tanggal akhir periode gaji (1-31)</p>
                                </div>
                            </div>
                            
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-info-circle text-blue-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h4 class="text-sm font-medium text-blue-800">Preview Periode</h4>
                                        <div class="mt-1 text-xs text-blue-700">
                                            <p><strong>Contoh dengan setting saat ini:</strong></p>
                                            <p>• Januari 2025: {{ ($settings['salary_cutoff_start_day'] ?? 11) }} Des 2024 - {{ ($settings['salary_cutoff_end_day'] ?? 10) }} Jan 2025</p>
                                            <p>• Februari 2025: {{ ($settings['salary_cutoff_start_day'] ?? 11) }} Jan 2025 - {{ ($settings['salary_cutoff_end_day'] ?? 10) }} Feb 2025</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mobile-filter-row sm:grid sm:grid-cols-2 sm:gap-4">
                                <div class="mobile-form-group">
                                    <label for="salary_status_complete_threshold" class="mobile-form-label">
                                        Threshold "Lengkap" (%)
                                    </label>
                                    <input type="number" name="salary_status_complete_threshold" id="salary_status_complete_threshold"
                                           value="{{ $settings['salary_status_complete_threshold'] ?? 90 }}" min="1" max="100"
                                           class="mobile-form-input">
                                    <p class="mobile-form-help">Persentase minimum untuk status lengkap</p>
                                </div>
                                
                                <div class="mobile-form-group">
                                    <label for="salary_status_partial_threshold" class="mobile-form-label">
                                        Threshold "Kurang" (%)
                                    </label>
                                    <input type="number" name="salary_status_partial_threshold" id="salary_status_partial_threshold"
                                           value="{{ $settings['salary_status_partial_threshold'] ?? 50 }}" min="1" max="100"
                                           class="mobile-form-input">
                                    <p class="mobile-form-help">Persentase minimum untuk status kurang</p>
                                </div>
                            </div>
                            
                            <div class="mobile-form-actions sm:flex sm:justify-end">
                                <button type="submit" class="mobile-btn-primary sm:w-auto sm:min-h-0 sm:px-4 sm:py-2 sm:text-sm">
                                    <i class="fas fa-save mr-1"></i>
                                    Simpan Pengaturan Gaji
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Director Bypass Setting -->
                    <div class="mobile-form-section">
                        <div class="mobile-form-section-title text-gray-800">
                            <i class="fas fa-user-shield mr-2"></i>
                            Bypass Approval Direktur
                        </div>
                        
                        <div class="mb-4">
                            <p class="text-sm text-gray-600 mb-3">
                                Ketika diaktifkan, direktur yang membuat pengeluaran tidak perlu menunggu approval dari finance manager dan project manager.
                                Pengeluaran akan langsung disetujui.
                            </p>
                            
                            @if($settings['expense_director_bypass_enabled'])
                                <div class="mb-3 p-3 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-lg">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <strong>Peringatan:</strong> Fitur bypass saat ini aktif. Semua pengeluaran yang dibuat direktur akan langsung disetujui.
                                </div>
                            @endif
                        </div>

                        <form method="POST" action="{{ route('settings.update-director-bypass') }}">
                            @csrf
                            @method('PATCH')
                            
                            <div class="mobile-form-group">
                                <div class="flex items-center">
                                    <input type="hidden" name="enabled" value="0">
                                    <input type="checkbox"
                                           name="enabled"
                                           value="1"
                                           id="director_bypass"
                                           {{ $settings['expense_director_bypass_enabled'] ? 'checked' : '' }}
                                           class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 touch-target">
                                    <label for="director_bypass" class="ml-3 text-sm font-medium text-gray-900">
                                        Aktifkan Bypass Approval Direktur
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mobile-form-actions sm:flex sm:items-center sm:space-x-4">
                                <button type="submit" class="mobile-btn-primary sm:w-auto sm:min-h-0 sm:px-4 sm:py-2 sm:text-sm">
                                    <i class="fas fa-save mr-1"></i>
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Notification Setting -->
                    <div class="mobile-form-section">
                        <div class="mobile-form-section-title text-gray-800">
                            <i class="fas fa-bell mr-2"></i>
                            Notifikasi Email
                        </div>
                        
                        <div class="mb-4">
                            <p class="text-sm text-gray-600 mb-3">
                                Kirim notifikasi email ketika ada approval pengeluaran yang perlu ditindaklanjuti.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('settings.update-notification') }}">
                            @csrf
                            @method('PATCH')
                            
                            <div class="mobile-form-group">
                                <div class="flex items-center">
                                    <input type="hidden" name="enabled" value="0">
                                    <input type="checkbox"
                                           name="enabled"
                                           value="1"
                                           id="notification_enabled"
                                           {{ $settings['expense_approval_notification_enabled'] ? 'checked' : '' }}
                                           class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 touch-target">
                                    <label for="notification_enabled" class="ml-3 text-sm font-medium text-gray-900">
                                        Aktifkan Notifikasi Email
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mobile-form-actions sm:flex sm:items-center sm:space-x-4">
                                <button type="submit" class="mobile-btn-primary sm:w-auto sm:min-h-0 sm:px-4 sm:py-2 sm:text-sm">
                                    <i class="fas fa-save mr-1"></i>
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- High Amount Threshold Setting -->
                    <div class="mobile-form-section">
                        <div class="mobile-form-section-title text-gray-800">
                            <i class="fas fa-money-bill-wave mr-2"></i>
                            Batas Nilai Tinggi
                        </div>
                        
                        <div class="mb-4">
                            <p class="text-sm text-gray-600 mb-3">
                                Pengeluaran dengan nilai di atas batas ini memerlukan approval direktur.
                                Nilai di bawah batas ini hanya perlu approval project manager.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('settings.update-threshold') }}">
                            @csrf
                            @method('PATCH')
                            
                            <div class="mobile-form-group">
                                <label for="threshold" class="mobile-form-label">Batas Nilai (Rp)</label>
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium text-gray-900">Rp</span>
                                    <input type="number"
                                           name="threshold"
                                           id="threshold"
                                           value="{{ $settings['expense_high_amount_threshold'] }}"
                                           min="1000000"
                                           max="1000000000"
                                           step="1000000"
                                           class="mobile-form-input flex-1">
                                </div>
                                <p class="mobile-form-help">
                                    Format saat ini: {{ number_format($settings['expense_high_amount_threshold']) }}
                                </p>
                            </div>
                            
                            <div class="mobile-form-actions sm:flex sm:items-center sm:space-x-4">
                                <button type="submit" class="mobile-btn-primary sm:w-auto sm:min-h-0 sm:px-4 sm:py-2 sm:text-sm">
                                    <i class="fas fa-save mr-1"></i>
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Reset to Default -->
                    <div class="mobile-form-section bg-red-50 border border-red-200">
                        <div class="mobile-form-section-title text-red-800">
                            <i class="fas fa-undo mr-2"></i>
                            Reset ke Default
                        </div>
                        
                        <div class="mb-4">
                            <p class="text-sm text-red-600 mb-3">
                                Kembalikan semua pengaturan ke nilai default. Tindakan ini tidak dapat dibatalkan.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('settings.reset-default') }}"
                              onsubmit="return confirm('Apakah Anda yakin ingin mengembalikan semua pengaturan ke nilai default?')">
                            @csrf
                            @method('PATCH')
                            
                            <div class="mobile-form-actions sm:flex sm:items-center sm:space-x-4">
                                <button type="submit" class="mobile-btn-danger sm:w-auto sm:min-h-0 sm:px-4 sm:py-2 sm:text-sm">
                                    <i class="fas fa-undo mr-1"></i>
                                    Reset ke Default
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>