<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Konfirmasi Hapus Proyek') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Warning Header -->
                    <div class="flex items-center mb-6">
                        <div class="flex-shrink-0">
                            <svg class="h-12 w-12 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-red-800">
                                Peringatan: Penghapusan Permanen
                            </h3>
                            <p class="text-sm text-red-600">
                                Tindakan ini tidak dapat dibatalkan. Semua data terkait akan dihapus secara permanen.
                            </p>
                        </div>
                    </div>

                    <!-- Project Info -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-2">Proyek yang akan dihapus:</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Nama Proyek:</p>
                                <p class="font-semibold text-gray-900">{{ $project->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Kode Proyek:</p>
                                <p class="font-semibold text-gray-900">{{ $project->code }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Status:</p>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($project->status == 'completed') bg-green-100 text-green-800
                                    @elseif($project->status == 'in_progress') bg-blue-100 text-blue-800
                                    @elseif($project->status == 'planning') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Total Nilai:</p>
                                <p class="font-semibold text-gray-900">Rp {{ number_format($project->planned_total_value ?? 0) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Deletion Summary -->
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                        <h4 class="text-lg font-semibold text-red-800 mb-3">Data yang akan ikut terhapus:</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-red-600">{{ $deletionSummary['expenses_count'] }}</div>
                                <div class="text-sm text-red-700">Pengeluaran</div>
                                @if($deletionSummary['total_expenses_amount'] > 0)
                                    <div class="text-xs text-red-600">Rp {{ number_format($deletionSummary['total_expenses_amount']) }}</div>
                                @endif
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-red-600">{{ $deletionSummary['expense_approvals_count'] }}</div>
                                <div class="text-sm text-red-700">Persetujuan</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-red-600">{{ $deletionSummary['activities_count'] }}</div>
                                <div class="text-sm text-red-700">Aktivitas</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-red-600">{{ $deletionSummary['timelines_count'] }}</div>
                                <div class="text-sm text-red-700">Timeline</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-red-600">{{ $deletionSummary['billings_count'] }}</div>
                                <div class="text-sm text-red-700">Tagihan</div>
                                @if($deletionSummary['total_billed_amount'] > 0)
                                    <div class="text-xs text-red-600">Rp {{ number_format($deletionSummary['total_billed_amount']) }}</div>
                                @endif
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-red-600">{{ $deletionSummary['revenues_count'] }}</div>
                                <div class="text-sm text-red-700">Pendapatan</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-red-600">{{ $deletionSummary['revenue_items_count'] }}</div>
                                <div class="text-sm text-red-700">Item Pendapatan</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-red-600">{{ $deletionSummary['documents_count'] }}</div>
                                <div class="text-sm text-red-700">Dokumen</div>
                            </div>
                        </div>
                    </div>

                    <!-- Warnings -->
                    @if(!empty($deleteCheck['warnings']))
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                            <h4 class="text-lg font-semibold text-yellow-800 mb-2">Peringatan:</h4>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($deleteCheck['warnings'] as $warning)
                                    <li class="text-sm text-yellow-700">{{ $warning }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Blockers (if any) -->
                    @if(!empty($deleteCheck['blockers']))
                        <div class="bg-red-100 border border-red-300 rounded-lg p-4 mb-6">
                            <h4 class="text-lg font-semibold text-red-800 mb-2">Tidak dapat menghapus karena:</h4>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($deleteCheck['blockers'] as $blocker)
                                    <li class="text-sm text-red-700">{{ $blocker }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Confirmation Form -->
                    @if($deleteCheck['can_delete'])
                        <form method="POST" action="{{ route('projects.destroy', $project->id) }}" class="space-y-4">
                            @csrf
                            @method('DELETE')
                            
                            <div class="bg-gray-100 rounded-lg p-4">
                                <label class="flex items-center">
                                    <input type="checkbox" name="confirm_delete" required class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700">
                                        Saya memahami bahwa tindakan ini akan menghapus proyek <strong>"{{ $project->name }}"</strong> 
                                        beserta semua data terkait secara permanen dan tidak dapat dibatalkan.
                                    </span>
                                </label>
                            </div>

                            <div class="flex justify-between items-center pt-4">
                                <a href="{{ route('projects.show', $project->id) }}" 
                                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    Batal
                                </a>
                                
                                <button type="submit" 
                                        class="bg-red-600 hover:bg-red-800 text-white font-bold py-2 px-6 rounded"
                                        onclick="return confirm('Apakah Anda yakin ingin menghapus proyek ini? Tindakan ini tidak dapat dibatalkan!')">
                                    Hapus Proyek Permanen
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="flex justify-center pt-4">
                            <a href="{{ route('projects.show', $project->id) }}" 
                               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Kembali ke Proyek
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
