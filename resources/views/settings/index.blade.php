<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pengaturan Sistem') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
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

                    <!-- Director Bypass Setting -->
                    <div class="mb-8 p-6 bg-gray-50 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4 text-gray-800">
                            <i class="fas fa-user-shield mr-2"></i>
                            Bypass Approval Direktur
                        </h3>
                        
                        <div class="mb-4">
                            <p class="text-sm text-gray-600 mb-3">
                                Ketika diaktifkan, direktur yang membuat pengeluaran tidak perlu menunggu approval dari finance manager dan project manager. 
                                Pengeluaran akan langsung disetujui.
                            </p>
                            
                            @if($settings['expense_director_bypass_enabled'])
                                <div class="mb-3 p-3 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <strong>Peringatan:</strong> Fitur bypass saat ini aktif. Semua pengeluaran yang dibuat direktur akan langsung disetujui.
                                </div>
                            @endif
                        </div>

                        <form method="POST" action="{{ route('settings.update-director-bypass') }}" class="flex items-center space-x-4">
                            @csrf
                            @method('PATCH')
                            
                            <div class="flex items-center">
                                <input type="hidden" name="enabled" value="0">
                                <input type="checkbox" 
                                       name="enabled" 
                                       value="1" 
                                       id="director_bypass"
                                       {{ $settings['expense_director_bypass_enabled'] ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                                <label for="director_bypass" class="ml-2 text-sm font-medium text-gray-900">
                                    Aktifkan Bypass Approval Direktur
                                </label>
                            </div>
                            
                            <button type="submit" 
                                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <i class="fas fa-save mr-1"></i>
                                Simpan
                            </button>
                        </form>
                    </div>

                    <!-- Notification Setting -->
                    <div class="mb-8 p-6 bg-gray-50 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4 text-gray-800">
                            <i class="fas fa-bell mr-2"></i>
                            Notifikasi Email
                        </h3>
                        
                        <div class="mb-4">
                            <p class="text-sm text-gray-600 mb-3">
                                Kirim notifikasi email ketika ada approval pengeluaran yang perlu ditindaklanjuti.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('settings.update-notification') }}" class="flex items-center space-x-4">
                            @csrf
                            @method('PATCH')
                            
                            <div class="flex items-center">
                                <input type="hidden" name="enabled" value="0">
                                <input type="checkbox" 
                                       name="enabled" 
                                       value="1" 
                                       id="notification_enabled"
                                       {{ $settings['expense_approval_notification_enabled'] ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                                <label for="notification_enabled" class="ml-2 text-sm font-medium text-gray-900">
                                    Aktifkan Notifikasi Email
                                </label>
                            </div>
                            
                            <button type="submit" 
                                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <i class="fas fa-save mr-1"></i>
                                Simpan
                            </button>
                        </form>
                    </div>

                    <!-- High Amount Threshold Setting -->
                    <div class="mb-8 p-6 bg-gray-50 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4 text-gray-800">
                            <i class="fas fa-money-bill-wave mr-2"></i>
                            Batas Nilai Tinggi
                        </h3>
                        
                        <div class="mb-4">
                            <p class="text-sm text-gray-600 mb-3">
                                Pengeluaran dengan nilai di atas batas ini memerlukan approval direktur. 
                                Nilai di bawah batas ini hanya perlu approval project manager.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('settings.update-threshold') }}" class="flex items-center space-x-4">
                            @csrf
                            @method('PATCH')
                            
                            <div class="flex items-center space-x-2">
                                <label for="threshold" class="text-sm font-medium text-gray-900">Rp</label>
                                <input type="number" 
                                       name="threshold" 
                                       id="threshold"
                                       value="{{ $settings['expense_high_amount_threshold'] }}"
                                       min="1000000"
                                       max="1000000000"
                                       step="1000000"
                                       class="w-32 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <span class="text-sm text-gray-500">
                                    ({{ number_format($settings['expense_high_amount_threshold']) }})
                                </span>
                            </div>
                            
                            <button type="submit" 
                                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <i class="fas fa-save mr-1"></i>
                                Simpan
                            </button>
                        </form>
                    </div>

                    <!-- Reset to Default -->
                    <div class="p-6 bg-red-50 rounded-lg border border-red-200">
                        <h3 class="text-lg font-semibold mb-4 text-red-800">
                            <i class="fas fa-undo mr-2"></i>
                            Reset ke Default
                        </h3>
                        
                        <div class="mb-4">
                            <p class="text-sm text-red-600 mb-3">
                                Kembalikan semua pengaturan ke nilai default. Tindakan ini tidak dapat dibatalkan.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('settings.reset-default') }}" 
                              onsubmit="return confirm('Apakah Anda yakin ingin mengembalikan semua pengaturan ke nilai default?')">
                            @csrf
                            @method('PATCH')
                            
                            <button type="submit" 
                                    class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                <i class="fas fa-undo mr-1"></i>
                                Reset ke Default
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>