<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-3 sm:space-y-0">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">
                {{ __('Tambah Karyawan Baru') }}
            </h2>
            <a href="{{ route('finance.employees.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-medium py-2 px-3 sm:px-4 rounded text-sm sm:text-base text-center">
                <i class="fas fa-arrow-left mr-1 sm:mr-2"></i>Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 bg-white border-b border-gray-200">
                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-3 sm:px-4 py-3 rounded relative mb-4 text-sm sm:text-base" role="alert">
                            <strong class="font-bold">Error!</strong>
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-3 sm:px-4 py-3 rounded relative mb-4 text-sm sm:text-base" role="alert">
                            <strong class="font-bold">Validation Errors:</strong>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('finance.employees.store') }}" class="space-y-4 sm:space-y-6">
                        @csrf

                        <!-- Basic Information -->
                        <div>
                            <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4">Informasi Dasar</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                <!-- Employee Code -->
                                <div>
                                    <label for="employee_code" class="block text-xs sm:text-sm font-medium text-gray-700">
                                        Kode Karyawan <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="employee_code" id="employee_code"
                                           value="{{ old('employee_code') }}"
                                           placeholder="Contoh: EMP001"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm sm:text-base @error('employee_code') border-red-500 @enderror">
                                    @error('employee_code')
                                        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Name -->
                                <div>
                                    <label for="name" class="block text-xs sm:text-sm font-medium text-gray-700">
                                        Nama Lengkap <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="name" id="name"
                                           value="{{ old('name') }}"
                                           placeholder="Masukkan nama lengkap karyawan"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm sm:text-base @error('name') border-red-500 @enderror">
                                    @error('name')
                                        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div>
                                    <label for="email" class="block text-xs sm:text-sm font-medium text-gray-700">
                                        Email
                                    </label>
                                    <input type="email" name="email" id="email"
                                           value="{{ old('email') }}"
                                           placeholder="contoh@email.com"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm sm:text-base @error('email') border-red-500 @enderror">
                                    @error('email')
                                        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Phone -->
                                <div>
                                    <label for="phone" class="block text-xs sm:text-sm font-medium text-gray-700">
                                        Nomor Telepon
                                    </label>
                                    <input type="tel" name="phone" id="phone"
                                           value="{{ old('phone') }}"
                                           placeholder="08123456789"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm sm:text-base @error('phone') border-red-500 @enderror">
                                    @error('phone')
                                        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Employment Information -->
                        <div class="border-t border-gray-200 pt-4 sm:pt-6">
                            <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4">Informasi Pekerjaan</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                <!-- Position -->
                                <div>
                                    <label for="position" class="block text-xs sm:text-sm font-medium text-gray-700">
                                        Posisi/Jabatan <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="position" id="position"
                                           value="{{ old('position') }}"
                                           placeholder="Contoh: Teknisi, Supervisor, dll"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm sm:text-base @error('position') border-red-500 @enderror">
                                    @error('position')
                                        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Department -->
                                <div>
                                    <label for="department" class="block text-xs sm:text-sm font-medium text-gray-700">
                                        Departemen <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="department" id="department"
                                           value="{{ old('department') }}"
                                           placeholder="Contoh: Teknik, Operasional, dll"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm sm:text-base @error('department') border-red-500 @enderror">
                                    @error('department')
                                        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Employment Type -->
                                <div>
                                    <label for="employment_type" class="block text-xs sm:text-sm font-medium text-gray-700">
                                        Tipe Karyawan <span class="text-red-500">*</span>
                                    </label>
                                    <select name="employment_type" id="employment_type"
                                            onchange="toggleContractDate()"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm sm:text-base @error('employment_type') border-red-500 @enderror">
                                        <option value="">Pilih Tipe Karyawan</option>
                                        <option value="permanent" {{ old('employment_type') === 'permanent' ? 'selected' : '' }}>Tetap</option>
                                        <option value="contract" {{ old('employment_type') === 'contract' ? 'selected' : '' }}>Kontrak</option>
                                        <option value="freelance" {{ old('employment_type') === 'freelance' ? 'selected' : '' }}>Freelance</option>
                                    </select>
                                    @error('employment_type')
                                        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Hire Date -->
                                <div>
                                    <label for="hire_date" class="block text-xs sm:text-sm font-medium text-gray-700">
                                        Tanggal Masuk <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="hire_date" id="hire_date"
                                           value="{{ old('hire_date') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm sm:text-base @error('hire_date') border-red-500 @enderror">
                                    @error('hire_date')
                                        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Contract End Date -->
                                <div id="contract_end_date_field" style="display: {{ old('employment_type') === 'contract' ? 'block' : 'none' }};">
                                    <label for="contract_end_date" class="block text-xs sm:text-sm font-medium text-gray-700">
                                        Tanggal Berakhir Kontrak <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="contract_end_date" id="contract_end_date"
                                           value="{{ old('contract_end_date') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm sm:text-base @error('contract_end_date') border-red-500 @enderror">
                                    @error('contract_end_date')
                                        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Status -->
                                <div>
                                    <label for="status" class="block text-xs sm:text-sm font-medium text-gray-700">
                                        Status <span class="text-red-500">*</span>
                                    </label>
                                    <select name="status" id="status"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm sm:text-base @error('status') border-red-500 @enderror">
                                        <option value="">Pilih Status</option>
                                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                                    </select>
                                    @error('status')
                                        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Daily Rate -->
                                <div>
                                    <label for="daily_rate" class="block text-xs sm:text-sm font-medium text-gray-700">
                                        Gaji Harian <span class="text-red-500">*</span>
                                    </label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 text-xs sm:text-sm">Rp</span>
                                        </div>
                                        <input type="number" name="daily_rate" id="daily_rate"
                                               value="{{ old('daily_rate') }}"
                                               placeholder="0"
                                               min="0" step="1000"
                                               class="pl-12 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm sm:text-base @error('daily_rate') border-red-500 @enderror">
                                    </div>
                                    @error('daily_rate')
                                        <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex flex-col sm:flex-row sm:justify-end space-y-2 sm:space-y-0 sm:space-x-3 pt-4 sm:pt-6 border-t border-gray-200">
                            <a href="{{ route('finance.employees.index') }}"
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-3 sm:px-4 rounded text-center text-sm sm:text-base order-2 sm:order-1">
                                Batal
                            </a>
                            <button type="submit"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-medium py-2 px-3 sm:px-4 rounded text-sm sm:text-base order-1 sm:order-2">
                                <i class="fas fa-save mr-1 sm:mr-2"></i>Simpan Karyawan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleContractDate() {
            const employmentType = document.getElementById('employment_type').value;
            const contractField = document.getElementById('contract_end_date_field');
            const contractInput = document.getElementById('contract_end_date');
            
            if (employmentType === 'contract') {
                contractField.style.display = 'block';
                contractInput.required = true;
            } else {
                contractField.style.display = 'none';
                contractInput.required = false;
                contractInput.value = '';
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleContractDate();
        });
    </script>
</x-app-layout>