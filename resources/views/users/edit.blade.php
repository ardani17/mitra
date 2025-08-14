<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-4 sm:space-y-0">
            <div class="min-w-0 flex-1">
                <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight break-words">
                    Edit User: {{ $user->name }}
                </h2>
            </div>
            <a href="{{ route('users.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-3 sm:px-4 py-2 rounded-lg transition duration-200 text-center text-sm sm:text-base">
                <i class="fas fa-arrow-left mr-2"></i>
                <span class="hidden sm:inline">Kembali</span>
                <span class="sm:hidden">Kembali</span>
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-4 sm:space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Informasi Akun -->
                        <div class="border-b border-gray-200 pb-4 sm:pb-6">
                            <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4">Informasi Akun</h3>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                                <div>
                                    <x-input-label for="name" :value="__('Nama Lengkap')" />
                                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus />
                                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                                </div>

                                <div>
                                    <x-input-label for="email" :value="__('Email')" />
                                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                                </div>

                                <div>
                                    <x-input-label for="password" :value="__('Password Baru (Kosongkan jika tidak ingin mengubah)')" />
                                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" />
                                    <x-input-error class="mt-2" :messages="$errors->get('password')" />
                                </div>

                                <div>
                                    <x-input-label for="password_confirmation" :value="__('Konfirmasi Password Baru')" />
                                    <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" />
                                    <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
                                </div>

                                <div class="sm:col-span-2">
                                    <x-input-label for="role" :value="__('Role')" />
                                    <select id="role" name="role" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm sm:text-base" required>
                                        <option value="">Pilih Role</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->name }}"
                                                {{ old('role', $user->roles->first()?->name) == $role->name ? 'selected' : '' }}>
                                                {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('role')" />
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Perusahaan -->
                        <div>
                            <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4">Informasi Perusahaan</h3>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                                <div>
                                    <x-input-label for="company_name" :value="__('Nama Perusahaan')" />
                                    <x-text-input id="company_name" name="company_name" type="text" class="mt-1 block w-full" :value="old('company_name', $user->company_name)" />
                                    <x-input-error class="mt-2" :messages="$errors->get('company_name')" />
                                </div>

                                <div>
                                    <x-input-label for="company_email" :value="__('Email Perusahaan')" />
                                    <x-text-input id="company_email" name="company_email" type="email" class="mt-1 block w-full" :value="old('company_email', $user->company_email)" />
                                    <x-input-error class="mt-2" :messages="$errors->get('company_email')" />
                                </div>

                                <div>
                                    <x-input-label for="company_phone" :value="__('Telepon Perusahaan')" />
                                    <x-text-input id="company_phone" name="company_phone" type="text" class="mt-1 block w-full" :value="old('company_phone', $user->company_phone)" />
                                    <x-input-error class="mt-2" :messages="$errors->get('company_phone')" />
                                </div>

                                <div class="sm:col-span-2">
                                    <x-input-label for="company_address" :value="__('Alamat Perusahaan')" />
                                    <textarea id="company_address" name="company_address" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm sm:text-base">{{ old('company_address', $user->company_address) }}</textarea>
                                    <x-input-error class="mt-2" :messages="$errors->get('company_address')" />
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end space-y-2 sm:space-y-0 sm:space-x-4 pt-4 sm:pt-6 border-t border-gray-200">
                            <a href="{{ route('users.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 sm:px-6 py-2 rounded-lg transition duration-200 text-center text-sm sm:text-base">
                                Batal
                            </a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 sm:px-6 py-2 rounded-lg transition duration-200 text-sm sm:text-base">
                                <i class="fas fa-save mr-2"></i>Update User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
