
# File Explorer Component dengan Alpine.js

## Komponen Utama File Explorer

```blade
<!-- resources/views/components/file-explorer.blade.php -->
<div x-data="fileExplorer({{ $project->id }})" class="file-explorer bg-white rounded-lg shadow-md p-4">
    <!-- Toolbar -->
    <div class="toolbar flex justify-between items-center mb-4 pb-4 border-b">
        <div class="flex space-x-2">
            <button @click="createFolder" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                </svg>
                Folder Baru
            </button>
            <button @click="openUploadModal" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                Upload
            </button>
            <button @click="syncWithCloud" :disabled="syncing" class="bg-cyan-600 hover:bg-cyan-700 text-white font-bold py-2 px-4 rounded text-sm disabled:opacity-50">
                <svg class="w-4 h-4 inline mr-1" :class="{'animate-spin': syncing}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span x-text="syncing ? 'Syncing...' : 'Sync'"></span>
            </button>
        </div>
        
        <!-- Breadcrumb -->
        <div class="flex items-center text-sm text-gray-600">
            <span class="cursor-pointer hover:text-blue-600" @click="navigateToRoot()">Root</span>
            <template x-for="(crumb, index) in breadcrumbs" :key="index">
                <span>
                    <svg class="w-4 h-4 inline mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <span class="cursor-pointer hover:text-blue-600" @click="navigateToBreadcrumb(index)" x-text="crumb.name"></span>
                </span>
            </template>
        </div>
    </div>

    <!-- Main Content -->
    <div class="explorer-content grid grid-cols-1 lg:grid-cols-4 gap-4">
        <!-- Tree View -->
        <div class="tree-panel lg:col-span-1 border-r pr-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Folder Structure</h3>
            <div class="tree-view">
                <template x-for="folder in folders" :key="folder.id">
                    <div class="tree-item">
                        <div 
                            @click="selectFolder(folder)" 
                            class="flex items-center py-1 px-2 hover:bg-gray-100 rounded cursor-pointer"
                            :class="{'bg-blue-50 border-l-2 border-blue-500': selectedFolder && selectedFolder.id === folder.id}"
                        >
                            <svg x-show="folder.children && folder.children.length > 0" 
                                 @click.stop="toggleFolder(folder)"
                                 class="w-4 h-4 mr-1 cursor-pointer transition-transform"
                                 :class="{'rotate-90': folder.expanded}"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                            <svg class="w-4 h-4 mr-2 text-yellow-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M10 4H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2h-8l-2-2z"></path>
                            </svg>
                            <span class="text-sm" x-text="folder.name"></span>
                            <span class="ml-auto text-xs text-gray-500" x-text="`(${folder.file_count || 0})`"></span>
                        </div>
                        
                        <!-- Children folders -->
                        <div x-show="folder.expanded && folder.children" class="ml-4">
                            <template x-for="child in folder.children" :key="child.id">
                                <div @click="selectFolder(child)" 
                                     class="flex items-center py-1 px-2 hover:bg-gray-100 rounded cursor-pointer"
                                     :class="{'bg-blue-50': selectedFolder && selectedFolder.id === child.id}">
                                    <svg class="w-4 h-4 mr-2 text-yellow-500" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M10 4H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2h-8l-2-2z"></path>
                                    </svg>
                                    <span class="text-sm" x-text="child.name"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- File List -->
        <div class="file-panel lg:col-span-3">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-sm font-semibold text-gray-700">
                    Files <span x-show="selectedFolder" x-text="`in ${selectedFolder?.name || ''}`"></span>
                </h3>
                <div class="flex items-center space-x-2">
                    <!-- View toggle -->
                    <button @click="viewMode = 'grid'" :class="{'text-blue-600': viewMode === 'grid'}" class="p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                    </button>
                    <button @click="viewMode = 'list'" :class="{'text-blue-600': viewMode === 'list'}" class="p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Loading State -->
            <div x-show="loading" class="flex justify-center items-center py-12">
                <svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <!-- Grid View -->
            <div x-show="!loading && viewMode === 'grid'" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <template x-for="file in currentFiles" :key="file.id">
                    <div class="border rounded-lg p-3 hover:shadow-md transition-shadow cursor-pointer relative group">
                        <div class="flex flex-col items-center" @click="openFile(file)">
                            <svg class="w-12 h-12 mb-2" :class="getFileIconClass(file.extension)" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"></path>
                            </svg>
                            <p class="text-sm text-center truncate w-full" x-text="file.name"></p>
                            <p class="text-xs text-gray-500 mt-1" x-text="formatFileSize(file.size)"></p>
                        </div>
                        
                        <!-- File Actions -->
                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button @click.stop="showFileMenu(file, $event)" class="p-1 bg-white rounded shadow">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12,16A2,2 0 0,1 14,18A2,2 0 0,1 12,20A2,2 0 0,1 10,18A2,2 0 0,1 12,16M12,10A2,2 0 0,1 14,12A2,2 0 0,1 12,14A2,2 0 0,1 10,12A2,2 0 0,1 12,10M12,4A2,2 0 0,1 14,6A2,2 0 0,1 12,8A2,2 0 0,1 10,6A2,2 0 0,1 12,4Z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- List View -->
            <div x-show="!loading && viewMode === 'list'" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Size</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Modified</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="file in currentFiles" :key="file.id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <div class="flex items-center cursor-pointer" @click="openFile(file)">
                                        <svg class="w-5 h-5 mr-2" :class="getFileIconClass(file.extension)" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"></path>
                                        </svg>
                                        <span class="text-sm" x-text="file.name"></span>
                                    </div>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500" x-text="formatFileSize(file.size)"></td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500" x-text="formatDate(file.updated_at)"></td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm">
                                    <button @click="showFileMenu(file, $event)" class="text-gray-400 hover:text-gray-600">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12,16A2,2 0 0,1 14,18A2,2 0 0,1 12,20A2,2 0 0,1 10,18A2,2 0 0,1 12,16M12,10A2,2 0 0,1 14,12A2,2 0 0,1 12,14A2,2 0 0,1 10,12A2,2 0 0,1 12,10M12,4A2,2 0 0,1 14,6A2,2 0 0,1 12,8A2,2 0 0,1 10,6A2,2 0 0,1 12,4Z"></path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div x-show="!loading && currentFiles.length === 0" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada file</h3>
                <p class="mt-1 text-sm text-gray-500">Mulai dengan mengupload file ke folder ini.</p>
                <div class="mt-4">
                    <button @click="openUploadModal" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                        Upload File
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Sync Status -->
    <div x-show="lastSync" class="mt-4 pt-4 border-t flex justify-between items-center text-sm text-gray-600">
        <span>Last sync: <span x-text="formatDate(lastSync)"></span></span>
        <span :class="{
            'text-green-600': syncStatus === 'completed',
            'text-yellow-600': syncStatus === 'in_progress', 
            'text-red-600': syncStatus === 'failed'
        }" x-text="syncStatus"></span>
    </div>

    <!-- File Context Menu -->
    <div x-show="showContextMenu" 
         @click.away="showContextMenu = false"
         x-transition
         :style="`top: ${contextMenuY}px; left: ${contextMenuX}px`"
         class="fixed bg-white rounded-md shadow-lg z-50 border">
        <a @click="downloadFile()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer">
            Download
        </a>
        <a @click="renameFile()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer">
            Rename
        </a>
        <a @click="deleteFile()" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100 cursor-pointer">
            Delete
        </a>
    </div>
</div>
```

## JavaScript Alpine.js Component

```javascript
// resources/js/components/fileExplorer.js
function fileExplorer(projectId) {
    return {
        projectId: projectId,
        folders: [],
        currentFiles: [],
        selectedFolder: null,
        breadcrumbs: [],
        loading: false,
        syncing: false,
        lastSync: null,
        syncStatus: null,
        viewMode: 'grid',
        showContextMenu: false,
        contextMenuX: 0,
        contextMenuY: 0,
        selectedFile: null,
        
        init() {
            this.loadFolders();
            this.checkSyncStatus();
            
            // Listen for upload complete event
            window.addEventListener('upload-complete', () => {
                if (this.selectedFolder) {
                    this.selectFolder(this.selectedFolder);
                }
            });
        },
        
        async loadFolders() {
            try {
                const response = await fetch(`/api/projects/${this.projectId}/folders`);
                this.folders = await response.json();
            } catch (error) {
                console.error('Error loading folders:', error);
                this.showNotification('Error loading folders', 'error');
            }
        },
        
        async selectFolder(folder) {
            this.selectedFolder = folder;
            this.updateBreadcrumbs(folder);
            this.loading = true;
            
            try {
                const response = await fetch(`/api/projects/${this.projectId}/files?folder_id=${folder.id}`);
                this.currentFiles = await response.json();
            } catch (error) {
                console.error('Error loading files:', error);
                this.showNotification('Error loading files', 'error');
            } finally {
                this.loading = false;
            }
        },
        
        toggleFolder(folder) {
            folder.expanded = !folder.expanded;
        },
        
        updateBreadcrumbs(folder) {
            this.breadcrumbs = [];
            let current = folder;
            while (current && current.parent_id) {
                this.breadcrumbs.unshift(current);
                current = this.findFolderById(current.parent_id);
            }
            if (current && current.id !== folder.id) {
                this.breadcrumbs.unshift(current);
            }
        },
        
        findFolderById(id) {
            const search = (folders) => {
                for (let folder of folders) {
                    if (folder.id === id) return folder;
                    if (folder.children) {
                        const found = search(folder.children);
                        if (found) return found;
                    }
                }
                return null;
            };
            return search(this.folders);
        },
        
        navigateToRoot() {
            this.selectedFolder = null;
            this.breadcrumbs = [];
            this.currentFiles = [];
        },
        
        navigateToBreadcrumb(index) {
            const folder = this.breadcrumbs[index];
            this.selectFolder(folder);
        },
        
        async createFolder() {
            const name = prompt('Nama folder baru:');
            if (!name) return;
            
            // Validate folder name
            if (!/^[a-zA-Z0-9-_\s]+$/.test(name)) {
                alert('Nama folder hanya boleh mengandung huruf, angka, spasi, dash dan underscore');
                return;
            }
            
            try {
                const response = await fetch(`/api/projects/${this.projectId}/folders`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        name: name,
                        parent_id: this.selectedFolder?.id
                    })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.loadFolders();
                    if (this.selectedFolder) {
                        this.selectFolder(this.selectedFolder);
                    }
                    this.showNotification('Folder berhasil dibuat', 'success');
                } else {
                    this.showNotification(data.message || 'Error creating folder', 'error');
                }
            } catch (error) {
                console.error('Error creating folder:', error);
                this.showNotification('Error creating folder', 'error');
            }
        },
        
        openUploadModal() {
            // Trigger upload modal
            window.dispatchEvent(new CustomEvent('open-upload-modal', {
                detail: {
                    projectId: this.projectId,
                    folderId: this.selectedFolder?.id
                }
            }));
        },
        
        async syncWithCloud() {
            this.syncing = true;
            try {
                const response = await fetch(`/api/projects/${this.projectId}/sync`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (response.ok) {
                    this.showNotification('Sinkronisasi dimulai', 'info');
                    setTimeout(() => this.checkSyncStatus(), 2000);
                } else {
                    this.showNotification('Error starting sync', 'error');
                }
            } catch (error) {
                console.error('Error starting sync:', error);
                this.showNotification('Error starting sync', 'error');
            } finally {
                setTimeout(() => {
                    this.syncing = false;
                }, 2000);
            }
        },
        
        async checkSyncStatus() {
            try {
                const response = await fetch(`/api/projects/${this.projectId}/sync-status`);
                const data = await response.json();
                this.lastSync = data.last_sync;
                this.syncStatus = data.status;
                
                // If sync is in progress, check again in 5 seconds
                if (data.status === 'in_progress') {
                    setTimeout(() => this.checkSyncStatus(), 5000);
                }
            } catch (error) {
                console.error('Error checking sync status:', error);
            }
        },
        
        openFile(file) {
            window.open(`/documents/${file.id}/view`, '_blank');
        },
        
        showFileMenu(file, event) {
            this.selectedFile = file;
            this.contextMenuX = event.clientX;
            this.contextMenuY = event.clientY;
            this.showContextMenu = true;
        },
        
        async downloadFile() {
            if (this.selectedFile) {
                window.location.href = `/documents/${this.selectedFile.id}/download`;
            }
            this.showContextMenu = false;
        },
        
        async renameFile() {
            if (!this.selectedFile) return;
            
            const newName = prompt('Nama baru:', this.selectedFile.name);
            if (!newName || newName === this.selectedFile.name) {
                this.showContextMenu = false;
                return;
            }
            
            try {
                const response = await fetch(`/api/documents/${this.selectedFile.id}/rename`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ name: newName })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.selectFolder(this.selectedFolder);
                    this.showNotification('File berhasil direname', 'success');
                } else {
                    this.showNotification(data.message || 'Error renaming file', 'error');
                }
            } catch (error) {
                console.error('Error renaming file:', error);
                this.showNotification('Error renaming file', 'error');
            }
            
            this.showContextMenu = false;
        },
        
        async deleteFile() {
            if (!this.selectedFile) return;
            
            if (!confirm(`Hapus file "${this.selectedFile.name}"?`)) {
                this.showContextMenu = false;
                return;
            }
            
            try {
                const response = await fetch(`/api/documents/${this.selectedFile.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (response.ok) {
                    this.selectFolder(this.selectedFolder);
                    this.showNotification('File berhasil dihapus', 'success');
                } else {
                    this.showNotification('Error deleting file', 'error');
                }
            } catch (error) {
                console.error('Error deleting file:', error);
                this.showNotification('Error deleting file', 'error');
            }
            
            this.showContextMenu = false;
        },
        
        getFileIconClass(extension) {
            const ext = extension?.toLowerCase();
            
            if (['pdf'].includes(ext)) return 'text-red-600';
            if (['doc', 'docx'].includes(ext)) return 'text-blue-600';
            if (['xls', 'xlsx'].includes(ext)) return 'text-green-600';
            if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) return 'text-purple-600';
            if (['zip', 'rar', '7z'].includes(ext)) return 'text-yellow-600';
            
            return 'text-gray-600';
        },
        
        formatFileSize(bytes) {
            if (!bytes) return '0 B';
            
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(1024));
            
            return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
        },
        
        formatDate(dateString) {
            if (!dateString) return '';
            
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },
        
        showNotification(message, type = 'info') {
            // You can integrate with your notification system here
            // For now, using simple alert
            if (type === 'error') {
                console.error(message);
            } else {
                console.log(message);
            }
        }
    };
}

// Register as Alpine.js component
document.addEventListener('alpine:init', () => {
    Alpine.data('fileExplorer', fileExplorer);
});
```

## Upload Modal Component

```blade
<!-- resources/views/components/upload-modal.blade.php -->
<div x-data="uploadModal()" 
     x-show="showModal"
     @open-upload-modal.window="openModal($event.detail)"
     class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50"
     style="display: none;">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full" @click.away="closeModal()">
            <div class="px