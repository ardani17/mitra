<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Karyawan') }} - {{ $employee->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('finance.employees.show', $employee) }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-eye mr-2"></i>Lihat Detail
                </a>
                <a href="{{ route('finance.employees.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">Error!</strong>
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">Validation Errors:</strong>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('finance.employees.update', $employee) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Dasar</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Employee Code -->
                                <div>
                                    <label for="employee_code" class="block text-sm font-medium text-gray-700">
                                        Kode Karyawan <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="employee_code" id="employee_code" 
                                           value="{{ old('employee_code', $employee->employee_code) }}" 
                                           placeholder="Contoh: EMP001"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('employee_code') border-red-500 @enderror">
                                    @error('employee_code')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Name -->
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">
                                        Nama Lengkap <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="name" id="name" 
                                           value="{{ old('name', $employee->name) }}" 
                                           placeholder="Masukkan nama lengkap karyawan"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">
                                        Email
                                    </label>
                                    <input type="email" name="email" id="email" 
                                           value="{{ old('email', $employee->email) }}" 
                                           placeholder="contoh@email.com"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('email') border-red-500 @enderror">
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Phone -->
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700">
                                        Nomor Telepon
                                    </label>
                                    <input type="tel" name="phone" id="phone" 
                                           value="{{ old('phone', $employee->phone) }}" 
                                           placeholder="08123456789"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('phone') border-red-500 @enderror">
                                    @error('phone')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Employment Information -->
                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Pekerjaan</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Position -->
                                <div>
                                    <label for="position" class="block text-sm font-medium text-gray-700">
                                        Posisi/Jabatan <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="position" id="position" 
                                           value="{{ old('position', $employee->position) }}" 
                                           placeholder="Contoh: Teknisi, Supervisor, dll"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('position') border-red-500 @enderror">
                                    @error('position')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Department -->
                                <div>
                                    <label for="department" class="block text-sm font-medium text-gray-700">
                                        Departemen <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="department" id="department" 
                                           value="{{ old('department', $employee->department) }}" 
                                           placeholder="Contoh: Teknik, Operasional, dll"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('department') border-red-500 @enderror">
                                    @error('department')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Employment Type -->
                                <div>
                                    <label for="employment_type" class="block text-sm font-medium text-gray-700">
                                        Tipe Karyawan <span class="text-red-500">*</span>
                                    </label>
                                    <select name="employment_type" id="employment_type" 
                                            onchange="toggleContractDate()"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('employment_type') border-red-500 @enderror">
                                        <option value="">Pilih Tipe Karyawan</option>
                                        <option value="permanent" {{ old('employment_type', $employee->employment_type) === 'permanent' ? 'selected' : '' }}>Tetap</option>
                                        <option value="contract" {{ old('employment_type', $employee->employment_type) === 'contract' ? 'selected' : '' }}>Kontrak</option>
                                        <option value="freelance" {{ old('employment_type', $employee->employment_type) === 'freelance' ? 'selected' : '' }}>Freelance</option>
                                    </select>
                                    @error('employment_type')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Hire Date -->
                                <div>
                                    <label for="hire_date" class="block text-sm font-medium text-gray-700">
                                        Tanggal Masuk <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="hire_date" id="hire_date" 
                                           value="{{ old('hire_date', $employee->hire_date->format('Y-m-d')) }}" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('hire_date') border-red-500 @enderror">
                                    @error('hire_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Contract End Date -->
                                <div id="contract_end_date_field" style="display: {{ old('employment_type', $employee->employment_type) === 'contract' ? 'block' : 'none' }};">
                                    <label for="contract_end_date" class="block text-sm font-medium text-gray-700">
                                        Tanggal Berakhir Kontrak <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="contract_end_date" id="contract_end_date" 
                                           value="{{ old('contract_end_date', $employee->contract_end_date ? $employee->contract_end_date->format('Y-m-d') : '') }}" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('contract_end_date') border-red-500 @enderror">
                                    @error('contract_end_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Status -->
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700">
                                        Status <span class="text-red-500">*</span>
                                    </label>
                                    <select name="status" id="status" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('status') border-red-500 @enderror">
                                        <option value="">Pilih Status</option>
                                        <option value="active" {{ old('status', $employee->status) === 'active' ? 'selected' : '' }}>Aktif</option>
                                        <option value="inactive" {{ old('status', $employee->status) === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                                    </select>
                                    @error('status')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Daily Rate -->
                                <div>
                                    <label for="daily_rate" class="block text-sm font-medium text-gray-700">
                                        Gaji Harian <span class="text-red-500">*</span>
                                    </label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">Rp</span>
                                        </div>
                                        <input type="number" name="daily_rate" id="daily_rate" 
                                               value="{{ old('daily_rate', $employee->daily_rate) }}" 
                                               placeholder="0"
                                               min="0" step="1000"
                                               class="pl-12 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('daily_rate') border-red-500 @enderror">
                                    </div>
                                    @error('daily_rate')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Warning for salary data -->
                        @if($employee->dailySalaries()->exists())
                            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-yellow-800">
                                            Perhatian
                                        </h3>
                                        <div class="mt-2 text-sm text-yellow-700">
                                            <p>Karyawan ini sudah memiliki data gaji harian. Perubahan gaji harian hanya akan berlaku untuk input gaji yang baru.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                            <a href="{{ route('finance.employees.show', $employee) }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Batal
                            </a>
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                <i class="fas fa-save mr-2"></i>Simpan Perubahan
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
                // Don't clear value in edit form to preserve existing data
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleContractDate();
        });
    </script>
</x-app-layout>