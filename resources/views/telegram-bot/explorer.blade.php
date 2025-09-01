<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Telegram Bot - File Explorer') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Search Bar -->
                    <div class="mb-6">
                        <div class="flex gap-4">
                            <div class="flex-1">
                                <form method="GET" action="{{ route('telegram-bot.explorer') }}" class="flex gap-2">
                                    <input type="hidden" name="path" value="{{ $currentPath }}">
                                    <div class="relative flex-1">
                                        <input type="text" 
                                               name="search" 
                                               id="searchInput"
                                               value="{{ $search }}" 
                                               placeholder="Search files and folders..." 
                                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                        <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-150 ease-in-out">
                                        Search
                                    </button>
                                    @if($search)
                                    <a href="{{ route('telegram-bot.explorer', ['path' => $currentPath]) }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition duration-150 ease-in-out">
                                        Clear
                                    </a>
                                    @endif
                                </form>
                            </div>
                            <button onclick="showAdvancedSearch()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-150 ease-in-out">
                                <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Live Search Results -->
                        <div id="searchResults" class="mt-2 hidden">
                            <div class="bg-white border border-gray-200 rounded-lg shadow-lg max-h-96 overflow-y-auto">
                                <div id="searchResultsContent" class="p-2">
                                    <!-- Results will be populated here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Breadcrumb -->
                    <div class="mb-4 flex items-center space-x-2 text-sm">
                        <a href="{{ route('telegram-bot.explorer') }}" class="text-indigo-600 hover:text-indigo-800 flex items-center">
                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            Projects Root
                        </a>
                        @foreach($breadcrumb as $crumb)
                        <span class="text-gray-400">/</span>
                        <a href="{{ route('telegram-bot.explorer', ['path' => $crumb['path']]) }}" class="text-indigo-600 hover:text-indigo-800">
                            {{ $crumb['name'] }}
                        </a>
                        @endforeach
                    </div>

                    <!-- Action Buttons - Responsive -->
                    <div class="mb-4 flex flex-col sm:flex-row gap-2 sm:gap-0 sm:justify-between">
                        <div class="flex flex-wrap gap-2">
                            <!-- Upload Button -->
                            <button onclick="showUploadModal()"
                                    class="px-3 py-2 sm:px-4 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-150 ease-in-out flex items-center text-sm sm:text-base">
                                <svg class="h-4 w-4 sm:h-5 sm:w-5 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <span class="hidden sm:inline">Upload</span>
                                <span class="sm:hidden">Up</span>
                            </button>
                            
                            <!-- Refresh Button -->
                            <button onclick="refreshExplorer()"
                                    class="px-3 py-2 sm:px-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out flex items-center text-sm sm:text-base">
                                <svg class="h-4 w-4 sm:h-5 sm:w-5 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                <span class="hidden sm:inline">Refresh</span>
                                <span class="sm:hidden">Ref</span>
                            </button>
                            
                            <!-- Cek Sinkronisasi Button -->
                            <button id="syncButton"
                                    onclick="handleSyncButton()"
                                    class="px-3 py-2 sm:px-4 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition duration-150 ease-in-out flex items-center text-sm sm:text-base">
                                <svg id="syncIcon" class="h-4 w-4 sm:h-5 sm:w-5 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span id="syncText" class="hidden sm:inline">Cek Sinkronisasi</span>
                                <span id="syncTextMobile" class="sm:hidden">Sync</span>
                            </button>
                        </div>
                        
                        <!-- New Folder Button -->
                        <button onclick="showCreateFolderModal()"
                                class="px-3 py-2 sm:px-4 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-150 ease-in-out flex items-center justify-center text-sm sm:text-base">
                            <svg class="h-4 w-4 sm:h-5 sm:w-5 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <span class="hidden sm:inline">New Folder</span>
                            <span class="sm:hidden">New</span>
                        </button>
                    </div>

                    <!-- File/Folder Grid -->
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        @if(count($items) > 0)
                            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                                <div class="flex items-center justify-between text-xs text-gray-600">
                                    <span>{{ count($items) }} items</span>
                                    @if($search)
                                    <span class="text-indigo-600">Filtered by: "{{ $search }}"</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="divide-y divide-gray-200">
                                @foreach($items as $item)
                                <div class="hover:bg-gray-50 transition duration-150 ease-in-out">
                                    <div class="px-4 py-3 flex items-center justify-between">
                                        <div class="flex items-center flex-1 min-w-0">
                                            @if($item['is_dir'])
                                                <a href="{{ route('telegram-bot.explorer', ['path' => $item['path']]) }}" class="flex items-center flex-1 min-w-0">
                                                    <svg class="h-8 w-8 text-{{ $item['color'] }} mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
                                                    </svg>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $item['name'] }}</p>
                                                        <p class="text-xs text-gray-500">Folder</p>
                                                    </div>
                                                </a>
                                            @else
                                                <div class="flex items-center flex-1 min-w-0">
                                                    @if($item['icon'] == 'image')
                                                        <svg class="h-8 w-8 text-{{ $item['color'] }} mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                    @elseif($item['icon'] == 'film')
                                                        <svg class="h-8 w-8 text-{{ $item['color'] }} mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
                                                        </svg>
                                                    @elseif($item['icon'] == 'document-text')
                                                        <svg class="h-8 w-8 text-{{ $item['color'] }} mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                        </svg>
                                                    @elseif($item['icon'] == 'table')
                                                        <svg class="h-8 w-8 text-{{ $item['color'] }} mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                        </svg>
                                                    @elseif($item['icon'] == 'archive')
                                                        <svg class="h-8 w-8 text-{{ $item['color'] }} mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                                        </svg>
                                                    @else
                                                        <svg class="h-8 w-8 text-{{ $item['color'] }} mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                        </svg>
                                                    @endif
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $item['name'] }}</p>
                                                        <p class="text-xs text-gray-500">
                                                            @if($item['extension'])
                                                                {{ strtoupper($item['extension']) }} file
                                                            @else
                                                                File
                                                            @endif
                                                            @if($item['size'])
                                                                • {{ number_format($item['size'] / 1024, 2) }} KB
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="ml-4 flex items-center space-x-2">
                                            <span class="text-xs text-gray-500">
                                                {{ date('Y-m-d H:i', $item['modified']) }}
                                            </span>
                                            
                                            <!-- Action Buttons -->
                                            <div class="flex items-center space-x-1">
                                                <!-- Rename Button -->
                                                <button onclick="showRenameModal('{{ $item['path'] }}', '{{ $item['name'] }}')"
                                                        class="p-1.5 text-gray-400 hover:text-blue-600 transition duration-150 ease-in-out"
                                                        title="Rename">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </button>
                                                
                                                <!-- Copy Button -->
                                                <button onclick="showCopyModal('{{ $item['path'] }}', '{{ $item['name'] }}')"
                                                        class="p-1.5 text-gray-400 hover:text-green-600 transition duration-150 ease-in-out"
                                                        title="Copy">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                    </svg>
                                                </button>
                                                
                                                <!-- Move Button -->
                                                <button onclick="showMoveModal('{{ $item['path'] }}', '{{ $item['name'] }}')"
                                                        class="p-1.5 text-gray-400 hover:text-yellow-600 transition duration-150 ease-in-out"
                                                        title="Move">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                                    </svg>
                                                </button>
                                                
                                                @if(!$item['is_dir'])
                                                    <!-- Download Button -->
                                                    <a href="{{ route('telegram-bot.download', ['path' => $item['path']]) }}"
                                                       class="p-1.5 text-gray-400 hover:text-indigo-600 transition duration-150 ease-in-out"
                                                       title="Download">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                                                        </svg>
                                                    </a>
                                                @endif
                                                
                                                <!-- Delete Button -->
                                                <button onclick="showDeleteConfirm('{{ $item['path'] }}', '{{ $item['name'] }}')"
                                                        class="p-1.5 text-gray-400 hover:text-red-600 transition duration-150 ease-in-out"
                                                        title="Delete">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No files or folders</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    @if($search)
                                        No items matching "{{ $search }}" found in this directory.
                                    @else
                                        This directory is empty.
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Recent Bot Uploads -->
                    @if(isset($recentUploads) && count($recentUploads) > 0)
                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            <i class="fas fa-clock text-indigo-500 mr-2"></i>
                            Recent Bot Uploads
                        </h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="space-y-2">
                                @foreach($recentUploads as $upload)
                                <div class="flex items-center justify-between py-2 border-b border-gray-200 last:border-0">
                                    <div class="flex items-center">
                                        <svg class="h-5 w-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $upload->file_name ?? 'Unknown file' }}</p>
                                            <p class="text-xs text-gray-500">
                                                @if($upload->project)
                                                    Project: {{ $upload->project->name }}
                                                @endif
                                                • {{ $upload->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                    <span class="text-xs text-gray-500">
                                        By: {{ $upload->user_name ?? 'Unknown' }}
                                    </span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Rename Modal -->
    <div id="renameModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Rename Item</h3>
                <div class="mt-4">
                    <input type="text" id="renameInput"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Enter new name">
                </div>
                <div class="mt-4 flex justify-end space-x-2">
                    <button onclick="closeModal('renameModal')"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Cancel
                    </button>
                    <button onclick="performRename()"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Rename
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Copy Modal -->
    <div id="copyModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-[600px] shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Copy Item</h3>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Destination Folder:</label>
                    
                    <!-- Selected Path Display -->
                    <div class="mb-3 p-2 bg-blue-50 border border-blue-200 rounded-md">
                        <span class="text-sm text-gray-600">Selected: </span>
                        <span id="copySelectedPath" class="text-sm font-medium text-blue-900">/</span>
                    </div>
                    
                    <!-- Folder Tree Container -->
                    <div id="copyFolderTreeApp" class="border border-gray-300 rounded-md p-3 max-h-96 overflow-y-auto bg-gray-50">
                        <div class="text-center py-4">
                            <svg class="animate-spin h-8 w-8 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">Loading folders...</p>
                        </div>
                    </div>
                    
                    <input type="hidden" id="copyDestInput" value="">
                </div>
                <div class="mt-4 flex justify-end space-x-2">
                    <button onclick="closeModal('copyModal')"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Cancel
                    </button>
                    <button onclick="performCopy()"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Copy
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Move Modal -->
    <div id="moveModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-[600px] shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Move Item</h3>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Destination Folder:</label>
                    
                    <!-- Selected Path Display -->
                    <div class="mb-3 p-2 bg-yellow-50 border border-yellow-200 rounded-md">
                        <span class="text-sm text-gray-600">Selected: </span>
                        <span id="moveSelectedPath" class="text-sm font-medium text-yellow-900">/</span>
                    </div>
                    
                    <!-- Folder Tree Container -->
                    <div id="moveFolderTreeApp" class="border border-gray-300 rounded-md p-3 max-h-96 overflow-y-auto bg-gray-50">
                        <div class="text-center py-4">
                            <svg class="animate-spin h-8 w-8 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">Loading folders...</p>
                        </div>
                    </div>
                    
                    <input type="hidden" id="moveDestInput" value="">
                </div>
                <div class="mt-4 flex justify-end space-x-2">
                    <button onclick="closeModal('moveModal')"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Cancel
                    </button>
                    <button onclick="performMove()"
                            class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                        Move
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center">
                    <svg class="h-6 w-6 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Confirm Delete</h3>
                </div>
                <div class="mt-4">
                    <p class="text-sm text-gray-500">
                        Are you sure you want to delete "<span id="deleteItemName" class="font-medium text-gray-900"></span>"?
                        This action cannot be undone.
                    </p>
                </div>
                <div class="mt-4 flex justify-end space-x-2">
                    <button onclick="closeModal('deleteModal')"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Cancel
                    </button>
                    <button onclick="performDelete()"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Folder Modal -->
    <div id="createFolderModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Create New Folder</h3>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Folder Name:</label>
                    <input type="text" id="folderNameInput"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Enter folder name">
                </div>
                <div class="mt-4 flex justify-end space-x-2">
                    <button onclick="closeModal('createFolderModal')"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Cancel
                    </button>
                    <button onclick="performCreateFolder()"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Create
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal - Responsive -->
    <div id="uploadModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Upload Files</h3>
                </div>
                <div class="px-6 py-4">
                    <!-- Current Path Display -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Upload to:</label>
                        <p class="text-sm text-gray-600 bg-gray-50 p-2 rounded">
                            /{{ $currentPath ?: 'Root' }}
                        </p>
                    </div>
                    
                    <!-- Drag and Drop Zone -->
                    <div id="dropZone"
                         class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center transition-colors">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-600">
                            Drag and drop files here, or click to browse
                        </p>
                        <input type="file" id="fileInput" multiple class="hidden">
                        <button onclick="document.getElementById('fileInput').click()"
                                class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Select Files
                        </button>
                    </div>
                    
                    <!-- Selected Files List -->
                    <div id="selectedFiles" class="mt-4 hidden">
                        <h4 class="font-medium text-gray-700 mb-2">Selected Files:</h4>
                        <div id="fileList" class="max-h-48 overflow-y-auto space-y-2"></div>
                    </div>
                    
                    <!-- Upload Progress -->
                    <div id="uploadProgress" class="mt-4 hidden">
                        <div class="flex justify-between text-sm text-gray-600 mb-1">
                            <span id="progressText">Uploading...</span>
                            <span id="progressPercent">0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div id="progressBar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-2">
                    <button onclick="closeUploadModal()"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                        Cancel
                    </button>
                    <button id="uploadButton"
                            onclick="performUpload()"
                            disabled
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        Upload
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Sync Modal -->
    @include('telegram-bot.explorer-sync-modal')

    @push('scripts')
    <!-- Load Vue components compiled by Vite -->
    @vite(['resources/js/telegram-folder-tree.js'])
    
    <script>
        // Global variables
        let selectedFilesArray = [];
        let syncStatus = 'unknown';
        let isUploading = false;
        let isSyncing = false;
        let searchTimeout;
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');
        const searchResultsContent = document.getElementById('searchResultsContent');

        // Live search functionality
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                searchResults.classList.add('hidden');
                return;
            }
            
            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, 300);
        });

        function performSearch(query) {
            fetch(`{{ route('telegram-bot.search-files') }}?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        displaySearchResults(data);
                    } else {
                        searchResultsContent.innerHTML = '<p class="text-sm text-gray-500 p-2">No results found</p>';
                        searchResults.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Search error:', error);
                });
        }

        function displaySearchResults(results) {
            let html = '';
            results.forEach(item => {
                const icon = item.is_dir ?
                    '<svg class="h-5 w-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>' :
                    '<svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>';
                
                const url = item.is_dir ?
                    `{{ route('telegram-bot.explorer') }}?path=${encodeURIComponent(item.path)}` :
                    `{{ route('telegram-bot.download') }}?path=${encodeURIComponent(item.path)}`;
                
                html += `
                    <a href="${url}" class="block px-3 py-2 hover:bg-gray-100 rounded">
                        <div class="flex items-center">
                            ${icon}
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-gray-900">${item.name}</p>
                                <p class="text-xs text-gray-500">${item.path}</p>
                            </div>
                        </div>
                    </a>
                `;
            });
            
            searchResultsContent.innerHTML = html;
            searchResults.classList.remove('hidden');
        }

        // Hide search results when clicking outside
        document.addEventListener('click', function(event) {
            if (!searchInput.contains(event.target) && !searchResults.contains(event.target)) {
                searchResults.classList.add('hidden');
            }
        });

        function showAdvancedSearch() {
            alert('Advanced search coming soon! Will include filters for file type, size, and date.');
        }

        // Modal Management
        let currentItemPath = '';
        let currentItemName = '';

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        // Rename Functions
        function showRenameModal(path, name) {
            currentItemPath = path;
            currentItemName = name;
            document.getElementById('renameInput').value = name;
            document.getElementById('renameModal').classList.remove('hidden');
        }

        function performRename() {
            const newName = document.getElementById('renameInput').value.trim();
            if (!newName) {
                alert('Please enter a new name');
                return;
            }

            fetch('{{ route("telegram-bot.rename-item") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    path: currentItemPath,
                    new_name: newName
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to rename item');
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }

        // Copy Functions
        function showCopyModal(path, name) {
            currentItemPath = path;
            currentItemName = name;
            document.getElementById('copyDestInput').value = '';
            document.getElementById('copySelectedPath').textContent = '/';
            document.getElementById('copyModal').classList.remove('hidden');
            
            // Initialize folder tree
            window.initializeCopyTreeApp();
        }

        function performCopy() {
            const destination = document.getElementById('copyDestInput').value;
            const destPath = destination ? destination + '/' + currentItemName : currentItemName;

            fetch('{{ route("telegram-bot.copy-item") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    source_path: currentItemPath,
                    dest_path: destPath
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to copy item');
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }

        // Move Functions
        function showMoveModal(path, name) {
            currentItemPath = path;
            currentItemName = name;
            document.getElementById('moveDestInput').value = '';
            document.getElementById('moveSelectedPath').textContent = '/';
            document.getElementById('moveModal').classList.remove('hidden');
            
            // Initialize folder tree with exclude path
            window.initializeMoveTreeApp(currentItemPath);
        }

        function performMove() {
            const destination = document.getElementById('moveDestInput').value;
            const destPath = destination ? destination + '/' + currentItemName : currentItemName;

            fetch('{{ route("telegram-bot.move-item") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    source_path: currentItemPath,
                    dest_path: destPath
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to move item');
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }

        // Delete Functions
        function showDeleteConfirm(path, name) {
            currentItemPath = path;
            currentItemName = name;
            document.getElementById('deleteItemName').textContent = name;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function performDelete() {
            // Show loading state
            const deleteButton = event.target;
            const originalText = deleteButton.textContent;
            deleteButton.textContent = 'Deleting...';
            deleteButton.disabled = true;

            fetch('{{ route("telegram-bot.delete-item") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    path: currentItemPath
                })
            })
            .then(response => {
                // Check if response is ok
                if (!response.ok) {
                    // Try to get error message from response
                    return response.text().then(text => {
                        // Check if response is JSON
                        try {
                            const data = JSON.parse(text);
                            throw new Error(data.message || `Server error: ${response.status}`);
                        } catch (e) {
                            // Response is not JSON (probably HTML error page)
                            console.error('Server response:', text);
                            throw new Error(`Server error: ${response.status} - ${response.statusText}`);
                        }
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data && data.success) {
                    // Close modal first
                    closeModal('deleteModal');
                    // Show success message
                    showNotification('Item deleted successfully', 'success');
                    // Reload after a short delay
                    setTimeout(() => {
                        location.reload();
                    }, 500);
                } else {
                    throw new Error(data.message || 'Failed to delete item');
                }
            })
            .catch(error => {
                console.error('Delete error:', error);
                alert('Error: ' + error.message);
                // Reset button state
                if (deleteButton) {
                    deleteButton.textContent = originalText;
                    deleteButton.disabled = false;
                }
            });
        }

        // Helper function to show notifications
        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500 text-white' :
                type === 'error' ? 'bg-red-500 text-white' :
                'bg-blue-500 text-white'
            }`;
            notification.textContent = message;
            
            // Add to body
            document.body.appendChild(notification);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Create Folder Functions
        function showCreateFolderModal() {
            document.getElementById('folderNameInput').value = '';
            document.getElementById('createFolderModal').classList.remove('hidden');
        }

        function performCreateFolder() {
            const folderName = document.getElementById('folderNameInput').value.trim();
            if (!folderName) {
                alert('Please enter a folder name');
                return;
            }

            const currentPath = '{{ $currentPath }}';
            const fullPath = currentPath ? currentPath + '/' + folderName : folderName;

            fetch('{{ route("telegram-bot.create-folder") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    path: fullPath
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to create folder');
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const modals = ['renameModal', 'copyModal', 'moveModal', 'deleteModal', 'createFolderModal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (event.target == modal) {
                    modal.classList.add('hidden');
                }
            });
        }

        // Upload functionality
        function showUploadModal() {
            document.getElementById('uploadModal').classList.remove('hidden');
            resetUploadState();
        }

        function closeUploadModal() {
            document.getElementById('uploadModal').classList.add('hidden');
            resetUploadState();
        }

        function resetUploadState() {
            selectedFilesArray = [];
            document.getElementById('fileInput').value = '';
            document.getElementById('selectedFiles').classList.add('hidden');
            document.getElementById('uploadProgress').classList.add('hidden');
            document.getElementById('uploadButton').disabled = true;
            document.getElementById('fileList').innerHTML = '';
        }

        // Drag and drop handlers
        const dropZone = document.getElementById('dropZone');

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('border-blue-500', 'bg-blue-50');
        });

        dropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
            handleFiles(e.dataTransfer.files);
        });

        document.getElementById('fileInput').addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        function handleFiles(files) {
            selectedFilesArray = Array.from(files);
            displaySelectedFiles();
            document.getElementById('uploadButton').disabled = false;
        }

        function displaySelectedFiles() {
            const fileList = document.getElementById('fileList');
            fileList.innerHTML = '';
            
            selectedFilesArray.forEach((file, index) => {
                const fileItem = document.createElement('div');
                fileItem.className = 'flex items-center justify-between p-2 bg-gray-50 rounded';
                fileItem.innerHTML = `
                    <div class="flex items-center">
                        <svg class="h-4 w-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="text-sm">${file.name}</span>
                        <span class="text-xs text-gray-500 ml-2">(${formatFileSize(file.size)})</span>
                    </div>
                    <button onclick="removeFile(${index})" class="text-red-500 hover:text-red-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                `;
                fileList.appendChild(fileItem);
            });
            
            document.getElementById('selectedFiles').classList.remove('hidden');
        }

        function removeFile(index) {
            selectedFilesArray.splice(index, 1);
            if (selectedFilesArray.length === 0) {
                resetUploadState();
            } else {
                displaySelectedFiles();
            }
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        async function performUpload() {
            if (selectedFilesArray.length === 0 || isUploading) return;
            
            isUploading = true;
            document.getElementById('uploadButton').disabled = true;
            document.getElementById('uploadProgress').classList.remove('hidden');
            
            const currentPath = '{{ $currentPath }}';
            
            // Create FormData with all files
            const formData = new FormData();
            
            // Add all files to the FormData
            selectedFilesArray.forEach(file => {
                formData.append('files[]', file);
            });
            
            // Add the path
            formData.append('path', currentPath);
            
            try {
                // Update progress to show starting
                document.getElementById('progressBar').style.width = '10%';
                document.getElementById('progressPercent').textContent = '10%';
                document.getElementById('progressText').textContent = `Uploading ${selectedFilesArray.length} file(s)...`;
                
                const response = await fetch('{{ route("telegram-bot.upload") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });
                
                // Update progress to show processing
                document.getElementById('progressBar').style.width = '70%';
                document.getElementById('progressPercent').textContent = '70%';
                document.getElementById('progressText').textContent = 'Processing...';
                
                // Check if response is JSON
                const contentType = response.headers.get("content-type");
                let result;
                
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    result = await response.json();
                } else {
                    // If not JSON, likely an error page
                    const text = await response.text();
                    console.error('Non-JSON response:', text);
                    
                    // Check if files were actually uploaded despite the error
                    // This happens when there's a partial success
                    if (response.status === 207) { // Multi-Status
                        result = {
                            success: true,
                            message: 'Files uploaded (with some warnings)',
                            partial: true
                        };
                    } else {
                        throw new Error('Server error - please check logs');
                    }
                }
                
                // Update progress to complete
                document.getElementById('progressBar').style.width = '100%';
                document.getElementById('progressPercent').textContent = '100%';
                
                if (result.success || result.partial) {
                    document.getElementById('progressText').textContent = 'Upload complete!';
                    
                    // Show appropriate message
                    if (result.uploaded && result.uploaded.length > 0) {
                        alert(`Successfully uploaded ${result.uploaded.length} file(s)`);
                    } else if (result.files && result.files.length > 0) {
                        alert(`Successfully uploaded ${result.files.length} file(s)`);
                    } else {
                        alert(result.message || 'Files uploaded successfully!');
                    }
                    
                    // Refresh the page to show new files
                    setTimeout(() => location.reload(), 500);
                } else if (result.errors && result.errors.length > 0) {
                    // Show specific errors
                    const errorMsg = result.errors.map(e => e.error || e).join('\n');
                    throw new Error(errorMsg);
                } else {
                    throw new Error(result.message || 'Upload failed');
                }
            } catch (error) {
                console.error('Upload error:', error);
                
                // Check if it's a network error but files might have been uploaded
                if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
                    if (confirm('Upload may have succeeded despite the error. Refresh page to check?')) {
                        location.reload();
                    }
                } else {
                    alert('Upload error: ' + error.message);
                }
            } finally {
                isUploading = false;
                document.getElementById('uploadButton').disabled = false;
            }
            
            // Show results
            let message = `Upload complete! `;
            if (successCount > 0) {
                message += `${successCount} file(s) uploaded successfully. `;
            }
            if (errorCount > 0) {
                message += `${errorCount} file(s) failed.`;
            }
            
            alert(message);
            
            if (successCount > 0) {
                // Refresh the page to show new files
                location.reload();
            }
            
            isUploading = false;
            closeUploadModal();
        }

        // Refresh functionality
        function refreshExplorer() {
            location.reload();
        }

        // Sync functionality
        async function checkSync() {
            const syncButton = document.getElementById('syncButton');
            const syncIcon = document.getElementById('syncIcon');
            const syncText = document.getElementById('syncText');
            const syncTextMobile = document.getElementById('syncTextMobile');
            
            // Show loading state
            syncIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>';
            syncIcon.classList.add('animate-spin');
            syncText.textContent = 'Checking...';
            if (syncTextMobile) syncTextMobile.textContent = 'Check...';
            
            try {
                const response = await fetch('{{ route("telegram-bot.check-sync") }}', {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Check the sync status
                    if (result.status === 'synced') {
                        syncStatus = 'synced';
                        syncButton.className = 'px-3 py-2 sm:px-4 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-150 ease-in-out flex items-center text-sm sm:text-base';
                        syncIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                        syncText.textContent = 'Tersinkronisasi';
                        if (syncTextMobile) syncTextMobile.textContent = 'Synced';
                    } else {
                        syncStatus = 'out-of-sync';
                        syncButton.className = 'px-3 py-2 sm:px-4 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition duration-150 ease-in-out flex items-center text-sm sm:text-base';
                        syncIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                        
                        // Show pending/failed count if available
                        const pendingCount = result.stats?.queue?.pending || 0;
                        const failedCount = result.stats?.queue?.failed || 0;
                        const totalIssues = pendingCount + failedCount;
                        
                        if (totalIssues > 0) {
                            syncText.textContent = `Sinkronkan (${totalIssues})`;
                            if (syncTextMobile) syncTextMobile.textContent = `Sync (${totalIssues})`;
                        } else {
                            syncText.textContent = 'Sinkronkan';
                            if (syncTextMobile) syncTextMobile.textContent = 'Sync';
                        }
                    }
                } else {
                    throw new Error(result.message || 'Failed to check sync status');
                }
            } catch (error) {
                console.error('Error checking sync:', error);
                syncStatus = 'unknown';
                syncButton.className = 'px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition duration-150 ease-in-out flex items-center';
                syncIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                syncText.textContent = 'Cek Sinkronisasi';
            } finally {
                syncIcon.classList.remove('animate-spin');
            }
        }

        async function performSync() {
            if (isSyncing) return;
            
            isSyncing = true;
            const syncButton = document.getElementById('syncButton');
            const syncIcon = document.getElementById('syncIcon');
            const syncText = document.getElementById('syncText');
            
            // Show syncing state
            syncIcon.classList.add('animate-spin');
            syncText.textContent = 'Menyinkronkan...';
            
            try {
                const response = await fetch('{{ route("telegram-bot.sync-storage") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({})
                });
                
                const result = await response.json();
                
                if (result.success) {
                    let message = 'Sinkronisasi berhasil!\n';
                    if (result.results) {
                        const res = result.results;
                        if (res.processed > 0) {
                            message += `\n• ${res.processed} file diproses`;
                        }
                        if (res.retried > 0) {
                            message += `\n• ${res.retried} file dicoba ulang`;
                        }
                        if (res.cleaned_queue > 0) {
                            message += `\n• ${res.cleaned_queue} antrian dibersihkan`;
                        }
                    }
                    
                    alert(message);
                    
                    // Update button to synced state
                    syncStatus = 'synced';
                    syncButton.className = 'px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-150 ease-in-out flex items-center';
                    syncIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                    syncText.textContent = 'Tersinkronisasi';
                    
                    // Refresh page to show updated files
                    setTimeout(() => location.reload(), 1000);
                } else {
                    alert('Sinkronisasi gagal: ' + (result.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error performing sync:', error);
                alert('Failed to synchronize storage: ' + error.message);
            } finally {
                isSyncing = false;
                syncIcon.classList.remove('animate-spin');
            }
        }

        function handleSyncButton() {
            // Always show the sync modal when button is clicked
            // The modal will be populated by checkSyncStatus function
            checkSyncStatus();
        }
    </script>
    @endpush
</x-app-layout>