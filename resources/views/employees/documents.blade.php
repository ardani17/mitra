<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-3 sm:space-y-0">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight break-words">
                {{ __('Dokumen Karyawan') }} - {{ $employee->name }}
            </h2>
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                <a href="{{ route('finance.employees.show', $employee) }}"
                   class="bg-blue-500 hover:bg-blue-700 text-white font-medium py-2 px-3 sm:px-4 rounded text-center text-sm sm:text-base">
                    <i class="fas fa-eye mr-1 sm:mr-2"></i>Lihat Detail
                </a>
                <a href="{{ route('finance.employees.index') }}"
                   class="bg-gray-500 hover:bg-gray-700 text-white font-medium py-2 px-3 sm:px-4 rounded text-center text-sm sm:text-base">
                    <i class="fas fa-arrow-left mr-1 sm:mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">
            <!-- Employee Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center space-x-3 sm:space-x-4">
                        <img class="h-12 w-12 sm:h-16 sm:w-16 rounded-full object-cover"
                             src="{{ $employee->avatar_url }}"
                             alt="{{ $employee->name }}">
                        <div>
                            <h3 class="text-lg sm:text-xl font-semibold text-gray-900">{{ $employee->name }}</h3>
                            <p class="text-xs sm:text-sm text-gray-600 break-words">{{ $employee->employee_code }} • {{ $employee->position }} • {{ $employee->department }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Document -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 bg-white border-b border-gray-200">
                    <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4">Upload Dokumen Baru</h3>
                    <form method="POST" action="{{ route('finance.employees.documents.upload', $employee) }}" enctype="multipart/form-data" class="space-y-3 sm:space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                            <div>
                                <label for="document_type" class="block text-xs sm:text-sm font-medium text-gray-700">
                                    Jenis Dokumen <span class="text-red-500">*</span>
                                </label>
                                <select name="document_type" id="document_type"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm sm:text-base @error('document_type') border-red-500 @enderror">
                                    <option value="">Pilih Jenis Dokumen</option>
                                    <option value="cv" {{ old('document_type') === 'cv' ? 'selected' : '' }}>CV/Resume</option>
                                    <option value="ktp" {{ old('document_type') === 'ktp' ? 'selected' : '' }}>KTP</option>
                                    <option value="ijazah" {{ old('document_type') === 'ijazah' ? 'selected' : '' }}>Ijazah</option>
                                    <option value="sertifikat" {{ old('document_type') === 'sertifikat' ? 'selected' : '' }}>Sertifikat</option>
                                    <option value="kontrak" {{ old('document_type') === 'kontrak' ? 'selected' : '' }}>Kontrak Kerja</option>
                                    <option value="skck" {{ old('document_type') === 'skck' ? 'selected' : '' }}>SKCK</option>
                                    <option value="kartu_keluarga" {{ old('document_type') === 'kartu_keluarga' ? 'selected' : '' }}>Kartu Keluarga</option>
                                    <option value="npwp" {{ old('document_type') === 'npwp' ? 'selected' : '' }}>NPWP</option>
                                    <option value="bpjs" {{ old('document_type') === 'bpjs' ? 'selected' : '' }}>BPJS</option>
                                    <option value="lainnya" {{ old('document_type') === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                                </select>
                                @error('document_type')
                                    <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="document" class="block text-xs sm:text-sm font-medium text-gray-700">
                                    File Dokumen <span class="text-red-500">*</span>
                                </label>
                                <input type="file" name="document" id="document"
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                       class="mt-1 block w-full text-xs sm:text-sm text-gray-500 file:mr-2 sm:file:mr-4 file:py-1 sm:file:py-2 file:px-2 sm:file:px-4 file:rounded-full file:border-0 file:text-xs sm:file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('document') border-red-500 @enderror">
                                @error('document')
                                    <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Format: PDF, DOC, DOCX, JPG, PNG. Maksimal 5MB</p>
                            </div>

                            <div>
                                <label for="description" class="block text-xs sm:text-sm font-medium text-gray-700">
                                    Deskripsi
                                </label>
                                <input type="text" name="description" id="description"
                                       value="{{ old('description') }}"
                                       placeholder="Deskripsi dokumen (opsional)"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm sm:text-base @error('description') border-red-500 @enderror">
                                @error('description')
                                    <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-medium py-2 px-3 sm:px-4 rounded text-sm sm:text-base">
                                <i class="fas fa-upload mr-1 sm:mr-2"></i>Upload Dokumen
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Documents List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 bg-white border-b border-gray-200">
                    <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4">Daftar Dokumen</h3>
                    
                    @if($documents->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                            @foreach($documents as $document)
                                <div class="border border-gray-200 rounded-lg p-3 sm:p-4 hover:shadow-md transition-shadow">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center space-x-2 mb-2">
                                                <i class="fas fa-file-{{ $document->file_type === 'pdf' ? 'pdf' : ($document->file_type === 'image' ? 'image' : 'alt') }} text-gray-400 text-sm"></i>
                                                <span class="text-xs sm:text-sm font-medium text-gray-900 truncate">{{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</span>
                                            </div>
                                            @if($document->description)
                                                <p class="text-xs sm:text-sm text-gray-600 mb-2 break-words">{{ $document->description }}</p>
                                            @endif
                                            <p class="text-xs text-gray-500">
                                                Diupload: {{ $document->created_at->format('d/m/Y H:i') }}
                                            </p>
                                        </div>
                                        <div class="flex space-x-1 sm:space-x-2 ml-2">
                                            <a href="{{ asset('storage/' . $document->file_path) }}"
                                               target="_blank"
                                               class="text-blue-600 hover:text-blue-900 p-1"
                                               title="Lihat Dokumen">
                                                <i class="fas fa-eye text-xs sm:text-sm"></i>
                                            </a>
                                            <a href="{{ asset('storage/' . $document->file_path) }}"
                                               download
                                               class="text-green-600 hover:text-green-900 p-1"
                                               title="Download">
                                                <i class="fas fa-download text-xs sm:text-sm"></i>
                                            </a>
                                            @can('update', $employee)
                                                <form action="{{ route('finance.employees.documents.delete', [$employee, $document->id]) }}"
                                                      method="POST" class="inline"
                                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="text-red-600 hover:text-red-900 p-1"
                                                            title="Hapus">
                                                        <i class="fas fa-trash text-xs sm:text-sm"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 sm:py-12">
                            <div class="text-gray-500 mb-4">
                                <i class="fas fa-folder-open text-3xl sm:text-4xl mb-3 sm:mb-4"></i>
                                <p class="text-base sm:text-lg">Belum ada dokumen yang diupload</p>
                            </div>
                            <p class="text-gray-500 text-xs sm:text-sm">Upload dokumen pertama menggunakan form di atas</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Document Categories Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-md p-3 sm:p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400 text-sm sm:text-base"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-xs sm:text-sm font-medium text-blue-800">
                            Jenis Dokumen yang Disarankan
                        </h3>
                        <div class="mt-2 text-xs sm:text-sm text-blue-700">
                            <ul class="list-disc list-inside space-y-1">
                                <li><strong>CV/Resume:</strong> Riwayat hidup dan pengalaman kerja</li>
                                <li><strong>KTP:</strong> Kartu Tanda Penduduk</li>
                                <li><strong>Ijazah:</strong> Sertifikat pendidikan terakhir</li>
                                <li><strong>Sertifikat:</strong> Sertifikat keahlian atau pelatihan</li>
                                <li><strong>Kontrak Kerja:</strong> Dokumen kontrak kerja yang ditandatangani</li>
                                <li><strong>SKCK:</strong> Surat Keterangan Catatan Kepolisian</li>
                                <li><strong>Kartu Keluarga:</strong> Dokumen keluarga</li>
                                <li><strong>NPWP:</strong> Nomor Pokok Wajib Pajak</li>
                                <li><strong>BPJS:</strong> Kartu BPJS Kesehatan/Ketenagakerjaan</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // File upload preview
        document.getElementById('document').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                if (fileSize > 5) {
                    alert('Ukuran file terlalu besar. Maksimal 5MB.');
                    this.value = '';
                    return;
                }
                
                // Show file info
                const fileName = file.name;
                const fileType = file.type;
                console.log(`File selected: ${fileName} (${fileSize}MB)`);
            }
        });

        // Auto-fill description based on document type
        document.getElementById('document_type').addEventListener('change', function() {
            const descriptionField = document.getElementById('description');
            if (!descriptionField.value) {
                const type = this.value;
                const descriptions = {
                    'cv': 'Curriculum Vitae',
                    'ktp': 'Kartu Tanda Penduduk',
                    'ijazah': 'Ijazah Pendidikan',
                    'sertifikat': 'Sertifikat Keahlian',
                    'kontrak': 'Kontrak Kerja',
                    'skck': 'Surat Keterangan Catatan Kepolisian',
                    'kartu_keluarga': 'Kartu Keluarga',
                    'npwp': 'Nomor Pokok Wajib Pajak',
                    'bpjs': 'Kartu BPJS'
                };
                
                if (descriptions[type]) {
                    descriptionField.value = descriptions[type];
                }
            }
        });
    </script>
    @endpush
</x-app-layout>