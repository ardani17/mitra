@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Buat Proyek Baru</h1>
        <a href="{{ route('projects.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Kembali ke Proyek
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('projects.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Proyek *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kode Proyek</label>
                    <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-500">
                        Akan di-generate otomatis (PRJ-YYYY-MM-XXX)
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Kode proyek akan dibuat otomatis berdasarkan tahun dan bulan</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Proyek *</label>
                    <select name="type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Tipe</option>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nilai Jasa Plan (Rp)</label>
                    <input type="text" name="planned_service_value_display" id="planned_service_value_display" value="{{ old('planned_service_value') ? number_format(old('planned_service_value'), 0, ',', '.') : '' }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="0">
                    <input type="hidden" name="planned_service_value" id="planned_service_value" value="{{ old('planned_service_value') }}">
                    @error('planned_service_value')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nilai Material Plan (Rp)</label>
                    <input type="text" name="planned_material_value_display" id="planned_material_value_display" value="{{ old('planned_material_value') ? number_format(old('planned_material_value'), 0, ',', '.') : '' }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="0">
                    <input type="hidden" name="planned_material_value" id="planned_material_value" value="{{ old('planned_material_value') }}">
                    @error('planned_material_value')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Total Nilai Plan (Rp)</label>
                    <input type="text" name="planned_total_value_display" id="planned_total_value_display" value="{{ old('planned_total_value') ? number_format(old('planned_total_value'), 0, ',', '.') : '' }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50"
                           placeholder="0" readonly>
                    <input type="hidden" name="planned_total_value" id="planned_total_value" value="{{ old('planned_total_value') }}">
                    <p class="mt-1 text-xs text-gray-500">Otomatis dihitung dari nilai jasa + material</p>
                    @error('planned_total_value')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                    <select name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Status</option>
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" {{ old('status') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Prioritas *</label>
                    <select name="priority" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Prioritas</option>
                        @foreach($priorities as $key => $label)
                            <option value="{{ $key }}" {{ old('priority') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('priority')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai *</label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('start_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai</label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div id="duration_info" class="mt-1 text-xs text-gray-500"></div>
                    @error('end_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi</label>
                    <div class="relative">
                        <input type="text" name="location" id="location_input" value="{{ old('location') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Ketik untuk mencari lokasi yang sudah ada..."
                               autocomplete="off">
                        <div id="location_suggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 hidden max-h-60 overflow-y-auto">
                            <!-- Suggestions will be populated here -->
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Lokasi yang pernah digunakan akan muncul sebagai saran</p>
                    @error('location')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Client</label>
                    <div class="relative">
                        <input type="text" name="client" id="client_input" value="{{ old('client') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Ketik untuk mencari client yang sudah ada..."
                               autocomplete="off">
                        <div id="client_suggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 hidden max-h-60 overflow-y-auto">
                            <!-- Suggestions will be populated here -->
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Client yang pernah digunakan akan muncul sebagai saran</p>
                    @error('client')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                <textarea name="description" rows="4" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                <textarea name="notes" rows="3" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mt-8 flex justify-end">
                <a href="{{ route('projects.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                    Batal
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Buat Proyek
                </button>
            </div>
        </form>
    </div>
    
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Informasi Proyek</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>Proyek akan dibuat dengan kode otomatis dan dapat dikelola melalui timeline, pengeluaran, dan penagihan setelah dibuat.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Format number with dots as thousand separators
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    
    // Remove dots and convert to number
    function unformatNumber(str) {
        return str.replace(/\./g, '');
    }
    
    // Setup number formatting for all value fields
    const valueFields = [
        'planned_service_value',
        'planned_material_value',
        'planned_total_value'
    ];
    
    valueFields.forEach(fieldName => {
        const displayInput = document.getElementById(fieldName + '_display');
        const hiddenInput = document.getElementById(fieldName);
        
        if (displayInput && hiddenInput) {
            // Handle input event
            displayInput.addEventListener('input', function(e) {
                let value = e.target.value;
                
                // Remove all non-digit characters
                value = value.replace(/[^\d]/g, '');
                
                // Update hidden input with raw number
                hiddenInput.value = value;
                
                // Format and display with dots
                if (value) {
                    e.target.value = formatNumber(value);
                } else {
                    e.target.value = '';
                }
                
                // Calculate total if this is service or material value
                if (fieldName === 'planned_service_value' || fieldName === 'planned_material_value') {
                    calculateTotal();
                }
            });
            
            // Handle paste event
            displayInput.addEventListener('paste', function(e) {
                setTimeout(() => {
                    let value = e.target.value.replace(/[^\d]/g, '');
                    hiddenInput.value = value;
                    if (value) {
                        e.target.value = formatNumber(value);
                    }
                    
                    // Calculate total if this is service or material value
                    if (fieldName === 'planned_service_value' || fieldName === 'planned_material_value') {
                        calculateTotal();
                    }
                }, 10);
            });
        }
    });
    
    // Calculate total value
    function calculateTotal() {
        const serviceValue = parseInt(document.getElementById('planned_service_value').value) || 0;
        const materialValue = parseInt(document.getElementById('planned_material_value').value) || 0;
        const totalValue = serviceValue + materialValue;
        
        // Update total fields
        document.getElementById('planned_total_value').value = totalValue;
        document.getElementById('planned_total_value_display').value = totalValue > 0 ? formatNumber(totalValue.toString()) : '';
    }
    
    // Calculate duration between start and end date
    function calculateDuration() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const durationInfo = document.getElementById('duration_info');
        
        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            
            if (end >= start) {
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                durationInfo.textContent = `Durasi pengerjaan: ${diffDays} hari`;
                durationInfo.className = 'mt-1 text-xs text-green-600';
            } else {
                durationInfo.textContent = 'Tanggal selesai harus setelah tanggal mulai';
                durationInfo.className = 'mt-1 text-xs text-red-600';
            }
        } else {
            durationInfo.textContent = '';
        }
    }
    
    // Add event listeners for date fields
    document.getElementById('start_date').addEventListener('change', calculateDuration);
    document.getElementById('end_date').addEventListener('change', calculateDuration);
    
    // Calculate duration on page load if both dates exist
    calculateDuration();
    
    // Location autocomplete functionality
    const locationInput = document.getElementById('location_input');
    const locationSuggestions = document.getElementById('location_suggestions');
    let debounceTimer;
    
    // Load popular locations on focus
    locationInput.addEventListener('focus', function() {
        if (this.value.trim() === '') {
            loadPopularLocations();
        }
    });
    
    // Search locations on input
    locationInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            if (query.length >= 1) {
                searchLocations(query);
            } else if (query.length === 0) {
                loadPopularLocations();
            } else {
                hideSuggestions();
            }
        }, 300);
    });
    
    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!locationInput.contains(e.target) && !locationSuggestions.contains(e.target)) {
            hideSuggestions();
        }
    });
    
    // Load popular locations
    function loadPopularLocations() {
        fetch('{{ route("api.projects.locations.popular") }}')
            .then(response => response.json())
            .then(locations => {
                displaySuggestions(locations, 'Lokasi Populer');
            })
            .catch(error => {
                console.error('Error loading popular locations:', error);
            });
    }
    
    // Search locations
    function searchLocations(query) {
        fetch(`{{ route("api.projects.locations.search") }}?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(locations => {
                displaySuggestions(locations, 'Hasil Pencarian');
            })
            .catch(error => {
                console.error('Error searching locations:', error);
            });
    }
    
    // Display suggestions
    function displaySuggestions(locations, title) {
        if (locations.length === 0) {
            hideSuggestions();
            return;
        }
        
        let html = '';
        if (title && locations.length > 0) {
            html += `<div class="px-3 py-2 text-xs font-medium text-gray-500 bg-gray-50 border-b">${title}</div>`;
        }
        
        locations.forEach(location => {
            html += `
                <div class="px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 location-suggestion"
                     data-location="${location}">
                    <div class="text-sm text-gray-900">${location}</div>
                </div>
            `;
        });
        
        locationSuggestions.innerHTML = html;
        locationSuggestions.classList.remove('hidden');
        
        // Add click event listeners to suggestions
        document.querySelectorAll('.location-suggestion').forEach(suggestion => {
            suggestion.addEventListener('click', function() {
                const location = this.getAttribute('data-location');
                locationInput.value = location;
                hideSuggestions();
            });
        });
    }
    
    // Hide suggestions
    function hideSuggestions() {
        locationSuggestions.classList.add('hidden');
        locationSuggestions.innerHTML = '';
    }
    
    // Client autocomplete functionality
    const clientInput = document.getElementById('client_input');
    const clientSuggestions = document.getElementById('client_suggestions');
    let clientDebounceTimer;
    
    // Load popular clients on focus
    clientInput.addEventListener('focus', function() {
        if (this.value.trim() === '') {
            loadPopularClients();
        }
    });
    
    // Search clients on input
    clientInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(clientDebounceTimer);
        clientDebounceTimer = setTimeout(() => {
            if (query.length >= 1) {
                searchClients(query);
            } else if (query.length === 0) {
                loadPopularClients();
            } else {
                hideClientSuggestions();
            }
        }, 300);
    });
    
    // Hide client suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!clientInput.contains(e.target) && !clientSuggestions.contains(e.target)) {
            hideClientSuggestions();
        }
    });
    
    // Load popular clients
    function loadPopularClients() {
        fetch('{{ route("api.projects.clients.popular") }}')
            .then(response => response.json())
            .then(clients => {
                displayClientSuggestions(clients, 'Client Populer');
            })
            .catch(error => {
                console.error('Error loading popular clients:', error);
            });
    }
    
    // Search clients
    function searchClients(query) {
        fetch(`{{ route("api.projects.clients.search") }}?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(clients => {
                displayClientSuggestions(clients, 'Hasil Pencarian');
            })
            .catch(error => {
                console.error('Error searching clients:', error);
            });
    }
    
    // Display client suggestions
    function displayClientSuggestions(clients, title) {
        if (clients.length === 0) {
            hideClientSuggestions();
            return;
        }
        
        let html = '';
        if (title && clients.length > 0) {
            html += `<div class="px-3 py-2 text-xs font-medium text-gray-500 bg-gray-50 border-b">${title}</div>`;
        }
        
        clients.forEach(client => {
            html += `
                <div class="px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 client-suggestion"
                     data-client="${client}">
                    <div class="text-sm text-gray-900">${client}</div>
                </div>
            `;
        });
        
        clientSuggestions.innerHTML = html;
        clientSuggestions.classList.remove('hidden');
        
        // Add click event listeners to client suggestions
        document.querySelectorAll('.client-suggestion').forEach(suggestion => {
            suggestion.addEventListener('click', function() {
                const client = this.getAttribute('data-client');
                clientInput.value = client;
                hideClientSuggestions();
            });
        });
    }
    
    // Hide client suggestions
    function hideClientSuggestions() {
        clientSuggestions.classList.add('hidden');
        clientSuggestions.innerHTML = '';
    }
});
</script>
@endsection
