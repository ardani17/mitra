<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            <i class="fas fa-building mr-2 text-blue-600"></i>Pengaturan Perusahaan
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Atur informasi perusahaan yang akan digunakan di seluruh sistem aplikasi.
        </p>
    </header>

    @if(auth()->user()->hasRole('direktur'))
        <form method="post" action="{{ route('profile.company.update') }}" class="mt-6 space-y-6">
            @csrf
            @method('patch')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="company_name" :value="__('Nama Perusahaan')" />
                    <x-text-input id="company_name" name="company_name" type="text" class="mt-1 block w-full" 
                        :value="old('company_name', $activeCompany?->name)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('company_name')" />
                </div>

                <div>
                    <x-input-label for="company_email" :value="__('Email Perusahaan')" />
                    <x-text-input id="company_email" name="company_email" type="email" class="mt-1 block w-full" 
                        :value="old('company_email', $activeCompany?->email)" />
                    <x-input-error class="mt-2" :messages="$errors->get('company_email')" />
                </div>

                <div>
                    <x-input-label for="company_phone" :value="__('Telepon Perusahaan')" />
                    <x-text-input id="company_phone" name="company_phone" type="text" class="mt-1 block w-full" 
                        :value="old('company_phone', $activeCompany?->phone)" />
                    <x-input-error class="mt-2" :messages="$errors->get('company_phone')" />
                </div>

                <div>
                    <x-input-label for="contact_person" :value="__('Kontak Person')" />
                    <x-text-input id="contact_person" name="contact_person" type="text" class="mt-1 block w-full" 
                        :value="old('contact_person', $activeCompany?->contact_person)" />
                    <x-input-error class="mt-2" :messages="$errors->get('contact_person')" />
                </div>

                <div class="md:col-span-2">
                    <x-input-label for="company_address" :value="__('Alamat Perusahaan')" />
                    <textarea id="company_address" name="company_address" rows="3" 
                        class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">{{ old('company_address', $activeCompany?->address) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('company_address')" />
                </div>
            </div>

            <div class="flex items-center gap-4 pt-6 border-t border-gray-200">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition duration-200">
                    <i class="fas fa-building mr-2"></i>Simpan Pengaturan Perusahaan
                </button>

                @if (session('status') === 'company-updated')
                    <p
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 3000)"
                        class="text-sm text-green-600 font-medium"
                    >
                        <i class="fas fa-check-circle mr-1"></i>Pengaturan perusahaan berhasil diperbarui!
                    </p>
                @endif
            </div>
        </form>
    @else
        <!-- Read-only company info for non-direktur -->
        @if($activeCompany)
            <div class="mt-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-6 rounded-lg">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Nama Perusahaan</label>
                        <p class="text-sm text-gray-900 font-medium">{{ $activeCompany->name ?: '-' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500">Email Perusahaan</label>
                        <p class="text-sm text-gray-900">{{ $activeCompany->email ?: '-' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500">Telepon Perusahaan</label>
                        <p class="text-sm text-gray-900">{{ $activeCompany->phone ?: '-' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500">Kontak Person</label>
                        <p class="text-sm text-gray-900">{{ $activeCompany->contact_person ?: '-' }}</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-sm font-medium text-gray-500">Alamat Perusahaan</label>
                        <p class="text-sm text-gray-900">{{ $activeCompany->address ?: '-' }}</p>
                    </div>
                </div>
                
                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        <p class="text-sm text-blue-800">
                            Informasi perusahaan ini diatur oleh direktur dan berlaku untuk seluruh sistem.
                        </p>
                    </div>
                </div>
            </div>
        @else
            <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                    <p class="text-sm text-yellow-800">
                        Belum ada informasi perusahaan yang diatur. Hubungi direktur untuk mengatur informasi perusahaan.
                    </p>
                </div>
            </div>
        @endif
    @endif
</section>
