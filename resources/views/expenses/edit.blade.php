@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 sm:py-8">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 space-y-4 sm:space-y-0">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Edit Pengeluaran</h1>
        </div>
        <a href="{{ route('expenses.show', $expense) }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-center text-sm sm:text-base">
            <span class="hidden sm:inline">Kembali ke Pengeluaran</span>
            <span class="sm:hidden">Kembali</span>
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
        <form action="{{ route('expenses.update', $expense) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Proyek *</label>
                    <div class="relative">
                        <input type="text"
                               id="project_search"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base"
                               placeholder="Ketik untuk mencari proyek..."
                               autocomplete="off"
                               value="{{ $expense->project->code ? $expense->project->code . ' - ' . $expense->project->name : $expense->project->name }}">
                        <input type="hidden"
                               name="project_id"
                               id="project_id"
                               value="{{ old('project_id', $expense->project_id) }}"
                               required>
                        <div id="project_suggestions" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 hidden max-h-60 overflow-y-auto">
                            <!-- Suggestions will be populated here -->
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Mulai mengetik nama atau kode proyek untuk mencari</p>
                    @error('project_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kategori *</label>
                    <select name="category" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base">
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" {{ old('category', $expense->category) == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('category')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Pengeluaran *</label>
                    <input type="date" name="expense_date" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base">
                    @error('expense_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah (Rp) *</label>
                    <input type="text" name="amount" value="{{ old('amount', number_format($expense->amount, 0, ',', '.')) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base"
                           placeholder="Masukkan jumlah..."
                           oninput="formatCurrency(this)">
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <div class="mt-4 sm:mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi *</label>
                <textarea name="description" rows="4" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base"
                          placeholder="Jelaskan detail pengeluaran...">{{ old('description', $expense->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mt-6 sm:mt-8 flex flex-col sm:flex-row sm:justify-end space-y-2 sm:space-y-0 sm:space-x-2">
                <a href="{{ route('expenses.show', $expense) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-3 sm:px-4 rounded text-center text-sm sm:text-base order-2 sm:order-1">
                    Batal
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 sm:px-4 rounded text-sm sm:text-base order-1 sm:order-2">
                    Perbarui Pengeluaran
                </button>
            </div>
        </form>
    </div>
    
    @if($expense->status == 'draft')
    <div class="mt-4 sm:mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-3 sm:p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">Tindakan Diperlukan</h3>
                <div class="mt-2 text-xs sm:text-sm text-yellow-700">
                    <p class="hidden sm:block">Pengeluaran ini masih berstatus "Draft". Setelah diperbarui, Anda dapat mengajukan untuk proses persetujuan.</p>
                    <p class="sm:hidden">Pengeluaran masih draft. Setelah diperbarui, dapat diajukan untuk persetujuan.</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
function formatCurrency(input) {
    // Hapus semua karakter non-digit
    let value = input.value.replace(/\D/g, '');
    
    // Format dengan titik sebagai pemisah ribuan
    if (value) {
        value = parseInt(value).toLocaleString('id-ID');
    }
    
    // Set nilai yang sudah diformat
    input.value = value;
}

// Saat form disubmit, hapus format untuk mengirim angka murni
document.querySelector('form').addEventListener('submit', function(e) {
    const amountInput = document.querySelector('input[name="amount"]');
    if (amountInput) {
        // Hapus titik pemisah ribuan sebelum submit
        let rawValue = amountInput.value.replace(/\./g, '');
        amountInput.value = rawValue;
    }
});

// Tambahkan event listener untuk memastikan format saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.querySelector('input[name="amount"]');
    if (amountInput && amountInput.value) {
        formatCurrency(amountInput);
    }
    
    // Project autocomplete functionality
    const projectSearch = document.getElementById('project_search');
    const projectId = document.getElementById('project_id');
    const projectSuggestions = document.getElementById('project_suggestions');
    let projectDebounceTimer;
    
    // Store initial display value
    projectSearch.dataset.selectedDisplay = projectSearch.value;
    
    // Load popular projects on focus
    projectSearch.addEventListener('focus', function() {
        if (this.value.trim() === '') {
            loadPopularProjects();
        }
    });
    
    // Search projects on input
    projectSearch.addEventListener('input', function() {
        const query = this.value.trim();
        
        // Clear the hidden project_id if user is typing (unless it matches current selection)
        if (!this.dataset.selectedDisplay || this.value !== this.dataset.selectedDisplay) {
            projectId.value = '';
            delete this.dataset.selectedDisplay;
        }
        
        clearTimeout(projectDebounceTimer);
        projectDebounceTimer = setTimeout(() => {
            if (query.length >= 1) {
                searchProjects(query);
            } else if (query.length === 0) {
                loadPopularProjects();
            } else {
                hideProjectSuggestions();
            }
        }, 300);
    });
    
    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!projectSearch.contains(e.target) && !projectSuggestions.contains(e.target)) {
            hideProjectSuggestions();
        }
    });
    
    // Load popular projects
    function loadPopularProjects() {
        fetch('{{ route("api.projects.popular") }}')
            .then(response => response.json())
            .then(projects => {
                displayProjectSuggestions(projects, 'Proyek Populer');
            })
            .catch(error => {
                console.error('Error loading popular projects:', error);
            });
    }
    
    // Search projects
    function searchProjects(query) {
        fetch(`{{ route("api.projects.search") }}?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(projects => {
                displayProjectSuggestions(projects, 'Hasil Pencarian');
            })
            .catch(error => {
                console.error('Error searching projects:', error);
            });
    }
    
    // Display project suggestions
    function displayProjectSuggestions(projects, title) {
        if (projects.length === 0) {
            projectSuggestions.innerHTML = `
                <div class="px-3 py-2 text-sm text-gray-500">
                    Tidak ada proyek ditemukan
                </div>
            `;
            projectSuggestions.classList.remove('hidden');
            return;
        }
        
        let html = '';
        if (title && projects.length > 0) {
            html += `<div class="px-3 py-2 text-xs font-medium text-gray-500 bg-gray-50 border-b">${title}</div>`;
        }
        
        projects.forEach(project => {
            html += `
                <div class="px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 project-suggestion"
                     data-project-id="${project.id}"
                     data-project-display="${project.display}">
                    <div class="text-sm text-gray-900">${project.display}</div>
                    ${project.code ? `<div class="text-xs text-gray-500">${project.name}</div>` : ''}
                </div>
            `;
        });
        
        projectSuggestions.innerHTML = html;
        projectSuggestions.classList.remove('hidden');
        
        // Add click event listeners to suggestions
        document.querySelectorAll('.project-suggestion').forEach(suggestion => {
            suggestion.addEventListener('click', function() {
                const id = this.getAttribute('data-project-id');
                const display = this.getAttribute('data-project-display');
                
                projectId.value = id;
                projectSearch.value = display;
                projectSearch.dataset.selectedDisplay = display;
                hideProjectSuggestions();
                
                // Trigger change event on hidden input for any listeners
                const event = new Event('change', { bubbles: true });
                projectId.dispatchEvent(event);
            });
        });
    }
    
    // Hide project suggestions
    function hideProjectSuggestions() {
        projectSuggestions.classList.add('hidden');
        projectSuggestions.innerHTML = '';
    }
    
    // Validate form submission
    document.querySelector('form').addEventListener('submit', function(e) {
        if (!projectId.value) {
            e.preventDefault();
            alert('Silakan pilih proyek dari daftar yang tersedia');
            projectSearch.focus();
            return false;
        }
    });
});
</script>
@endsection
