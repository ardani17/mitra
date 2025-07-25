@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">{{ $company->name }}</h1>
        <div class="flex space-x-2">
            <a href="{{ route('companies.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Companies
            </a>
            <a href="{{ route('companies.edit', $company) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Edit Company
            </a>
        </div>
    </div>

    <!-- Company Details -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Company Information</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Name</label>
                        <div class="mt-1 text-sm text-gray-900">{{ $company->name }}</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Email</label>
                        <div class="mt-1 text-sm text-gray-900">{{ $company->email ?? '-' }}</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Phone</label>
                        <div class="mt-1 text-sm text-gray-900">{{ $company->phone ?? '-' }}</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Contact Person</label>
                        <div class="mt-1 text-sm text-gray-900">{{ $company->contact_person ?? '-' }}</div>
                    </div>
                </div>
            </div>
            
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Address</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Full Address</label>
                        <div class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ $company->address ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-6 pt-6 border-t border-gray-200">
            <div class="flex justify-between items-center">
                <div>
                    <span class="text-sm text-gray-600">Created:</span>
                    <span class="text-sm font-medium text-gray-900">{{ $company->created_at->format('d M Y H:i') }}</span>
                </div>
                <div>
                    <span class="text-sm text-gray-600">Last Updated:</span>
                    <span class="text-sm font-medium text-gray-900">{{ $company->updated_at->format('d M Y H:i') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Company Statistics -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Company Statistics</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-sm text-blue-600">Total Projects</div>
                <div class="text-2xl font-bold text-blue-800">{{ $company->projects()->count() }}</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-sm text-green-600">Active Projects</div>
                <div class="text-2xl font-bold text-green-800">{{ $company->projects()->whereIn('status', ['planning', 'on_progress'])->count() }}</div>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <div class="text-sm text-yellow-600">Completed Projects</div>
                <div class="text-2xl font-bold text-yellow-800">{{ $company->projects()->where('status', 'completed')->count() }}</div>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <div class="text-sm text-purple-600">Total Revenue</div>
                <div class="text-2xl font-bold text-purple-800">Rp {{ number_format($company->projects()->with('revenues')->get()->sum(function($project) { return $project->revenues->sum('total_amount'); }), 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <!-- Projects Section -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button class="tab-button active text-blue-600 border-blue-600 px-4 py-2 font-medium text-sm border-b-2" data-tab="projects">
                    Projects ({{ $company->projects()->count() }})
                </button>
            </nav>
        </div>

        <!-- Projects Tab -->
        <div class="tab-content active p-6" id="projects-tab">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Company Projects</h3>
                <a href="{{ route('projects.create') }}?company={{ $company->id }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                    Create New Project
                </a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Budget</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($company->projects as $project)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $project->name }}</div>
                                <div class="text-sm text-gray-500">{{ Str::limit($project->description, 50) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                      @if($project->type == 'fiber_optic') bg-blue-100 text-blue-800
                                      @elseif($project->type == 'pole_planting') bg-green-100 text-green-800
                                      @elseif($project->type == 'tower_installation') bg-purple-100 text-purple-800
                                      @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $project->type)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">Plan: Rp {{ number_format($project->planned_budget, 0, ',', '.') }}</div>
                                <div class="text-sm text-gray-500">Actual: Rp {{ number_format($project->actual_budget, 0, ',', '.') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                      @if($project->status == 'completed') bg-green-100 text-green-800
                                      @elseif($project->status == 'on_progress') bg-blue-100 text-blue-800
                                      @elseif($project->status == 'on_hold') bg-yellow-100 text-yellow-800
                                      @elseif($project->status == 'draft') bg-gray-100 text-gray-800
                                      @else bg-purple-100 text-purple-800 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($project->start_date)
                                    <div>Start: {{ $project->start_date->format('d M Y') }}</div>
                                @endif
                                @if($project->end_date)
                                    <div>End: {{ $project->end_date->format('d M Y') }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('projects.show', $project) }}" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                <a href="{{ route('projects.edit', $project) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                No projects found for this company.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active', 'text-blue-600', 'border-blue-600'));
            tabButtons.forEach(btn => btn.classList.add('text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300'));
            tabContents.forEach(content => content.style.display = 'none');
            
            // Add active class to clicked button
            this.classList.add('active', 'text-blue-600', 'border-blue-600');
            this.classList.remove('text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            
            // Show corresponding content
            const tabId = this.getAttribute('data-tab') + '-tab';
            document.getElementById(tabId).style.display = 'block';
        });
    });
});
</script>
@endsection
