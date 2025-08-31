{{-- Advanced Vue File Explorer with Progress Bar, Bulk Upload, and Drag & Drop --}}
<!-- Load Vue 3 from CDN first -->
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<!-- Load Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div id="vue-file-explorer-app" class="desktop-file-explorer">
    <!-- Upload Modal -->
    <div v-if="showUploadModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Upload Files</h3>
            </div>
            <div class="px-6 py-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Upload to folder:</label>
                    <p class="text-sm text-gray-600 bg-gray-50 p-2 rounded">@{{ currentFolder ? currentFolder.name : 'Select a folder first' }}</p>
                </div>
                
                <!-- Drag and Drop Zone -->
                <div 
                    @drop.prevent="handleDrop"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    :class="['border-2 border-dashed rounded-lg p-8 text-center transition-colors', 
                             isDragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300 bg-gray-50']">
                    
                    <div v-if="!selectedFiles || selectedFiles.length === 0">
                        <i class="fas fa-cloud-upload-alt text-4xl mb-3" 
                           :class="isDragging ? 'text-blue-500' : 'text-gray-400'"></i>
                        <p class="text-sm text-gray-600 mb-2">
                            Drag and drop files here, or click to browse
                        </p>
                        <input type="file"
                               ref="fileInput"
                               @change="handleFileSelect"
                               multiple
                               class="hidden">
                        <button @click="$refs.fileInput.click()"
                                class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded text-sm">
                            Select Files
                        </button>
                        <p class="text-xs text-gray-500 mt-2">Maximum file size: 2GB per file</p>
                    </div>
                    
                    <!-- Selected Files List -->
                    <div v-else class="text-left">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="font-medium text-gray-700">Selected Files (@{{ selectedFiles.length }})</h4>
                            <button @click="clearFiles"
                                    class="text-sm text-red-500 hover:text-red-700">
                                Clear All
                            </button>
                        </div>
                        <div class="max-h-48 overflow-y-auto space-y-2">
                            <div v-for="(file, index) in selectedFiles" :key="index"
                                 class="flex items-center justify-between p-2 bg-white rounded border">
                                <div class="flex items-center flex-1 min-w-0">
                                    <i :class="getFileIcon(file.name.split('.').pop())" class="mr-2"></i>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">@{{ file.name }}</p>
                                        <p class="text-xs text-gray-500">@{{ formatSize(file.size) }}</p>
                                    </div>
                                </div>
                                <button @click="removeFile(index)"
                                        class="ml-2 text-red-500 hover:text-red-700">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <button @click="$refs.fileInput.click()"
                                class="mt-3 text-sm text-blue-500 hover:text-blue-700">
                            + Add more files
                        </button>
                    </div>
                </div>
                
                <!-- Upload Progress -->
                <div v-if="uploadProgress.show" class="mt-4">
                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                        <span>Uploading @{{ uploadProgress.current }} of @{{ uploadProgress.total }} files</span>
                        <span>@{{ uploadProgress.percentage }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full transition-all duration-300"
                             :style="{ width: uploadProgress.percentage + '%' }"></div>
                    </div>
                    <p v-if="uploadProgress.currentFile" class="text-xs text-gray-500 mt-1 truncate">
                        Current: @{{ uploadProgress.currentFile }}
                    </p>
                </div>
                
                <!-- Upload Results -->
                <div v-if="uploadResults.length > 0" class="mt-4 p-3 bg-green-50 rounded">
                    <p class="text-sm font-medium text-green-800 mb-1">
                        Successfully uploaded @{{ uploadResults.length }} file(s)
                    </p>
                    <ul class="text-xs text-green-700 list-disc list-inside">
                        <li v-for="result in uploadResults" :key="result">@{{ result }}</li>
                    </ul>
                </div>
                
                <!-- Upload Errors -->
                <div v-if="uploadErrors.length > 0" class="mt-4 p-3 bg-red-50 rounded">
                    <p class="text-sm font-medium text-red-800 mb-1">
                        Failed to upload @{{ uploadErrors.length }} file(s)
                    </p>
                    <ul class="text-xs text-red-700 list-disc list-inside">
                        <li v-for="error in uploadErrors" :key="error">@{{ error }}</li>
                    </ul>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-2">
                <button @click="closeUploadModal"
                        :disabled="uploading"
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded text-sm disabled:opacity-50">
                    @{{ uploadResults.length > 0 ? 'Close' : 'Cancel' }}
                </button>
                <button @click="performUpload"
                        v-if="selectedFiles && selectedFiles.length > 0 && !uploading"
                        :disabled="!currentFolder"
                        class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    Upload @{{ selectedFiles.length }} File(s)
                </button>
            </div>
        </div>
    </div>
    
    <!-- Create Folder Modal -->
    <div v-if="showCreateFolderModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Create New Folder</h3>
            </div>
            <div class="px-6 py-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Parent Folder:</label>
                    <p class="text-sm text-gray-600 bg-gray-50 p-2 rounded">@{{ currentFolder ? currentFolder.name : 'Root' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Folder Name:</label>
                    <input v-model="newFolderName"
                           type="text"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Enter folder name"
                           @keyup.enter="createFolder">
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-2">
                <button @click="closeCreateFolderModal"
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded text-sm">
                    Cancel
                </button>
                <button @click="createFolder"
                        :disabled="!newFolderName"
                        class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    Create Folder
                </button>
            </div>
        </div>
    </div>

    <!-- Rename Folder Modal -->
    <div v-if="showRenameFolderModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Rename Folder</h3>
            </div>
            <div class="px-6 py-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Name:</label>
                    <p class="text-sm text-gray-600 bg-gray-50 p-2 rounded">@{{ folderToRename ? folderToRename.name : '' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">New Name:</label>
                    <input v-model="renameFolderName"
                           type="text"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Enter new folder name"
                           @keyup.enter="renameFolder">
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-2">
                <button @click="closeRenameFolderModal"
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded text-sm">
                    Cancel
                </button>
                <button @click="renameFolder"
                        :disabled="!renameFolderName"
                        class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    Rename
                </button>
            </div>
        </div>
    </div>

    <!-- Folder Context Menu -->
    <div v-if="showContextMenu"
         :style="{ top: contextMenuY + 'px', left: contextMenuX + 'px' }"
         class="fixed bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
        <button @click="openRenameFolderModal"
                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
            <i class="fas fa-edit mr-2"></i> Rename
        </button>
        <button @click="confirmDeleteFolder"
                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
            <i class="fas fa-trash mr-2"></i> Delete
        </button>
        <button @click="downloadFolderAsZip"
                class="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-gray-100">
            <i class="fas fa-file-archive mr-2"></i> Download ZIP
        </button>
    </div>
    
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold">Document Manager</h3>
            <div class="flex items-center space-x-2">
                <button @click="openCreateFolderModal" class="px-3 py-1 bg-indigo-500 text-white rounded hover:bg-indigo-600 text-sm">
                    <i class="fas fa-folder-plus mr-1"></i> Add Folder
                </button>
                <button @click="uploadFile" class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-sm">
                    <i class="fas fa-upload mr-1"></i> Upload
                </button>
                <button @click="refreshFolders" class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm">
                    <i class="fas fa-sync-alt mr-1"></i> Refresh
                </button>
                <button @click="handleSyncButton"
                        :class="syncButtonClass"
                        :disabled="syncChecking || syncing"
                        class="px-3 py-1 text-white rounded text-sm transition-colors">
                    <i v-if="syncChecking || syncing" class="fas fa-spinner fa-spin mr-1"></i>
                    <i v-else :class="syncButtonIcon" class="mr-1"></i>
                    @{{ syncButtonText }}
                </button>
            </div>
        </div>
        
        <div class="flex" style="min-height: 500px;">
            <!-- Sidebar -->
            <div class="w-64 border-r bg-gray-50 p-3 overflow-y-auto" style="max-height: 500px;">
                <div v-if="loading" class="text-center py-4">
                    <svg class="animate-spin h-5 w-5 text-blue-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-sm text-gray-500 mt-2">Loading folders...</p>
                </div>
                <div v-else-if="error" class="text-center py-4">
                    <p class="text-sm text-red-500">@{{ error }}</p>
                </div>
                <div v-else>
                    <!-- Folder Tree will be rendered here -->
                    <div v-for="folder in rootFolders" :key="folder.path">
                        <folder-tree :folder="folder" :level="0"></folder-tree>
                    </div>
                </div>
            </div>
            
            <!-- Content Area with Drag & Drop -->
            <div class="flex-1 p-4"
                 @drop.prevent="handleQuickDrop"
                 @dragover.prevent="isQuickDragging = true"
                 @dragleave.prevent="isQuickDragging = false">
                 
                <!-- Quick Drop Overlay -->
                <div v-if="isQuickDragging && currentFolder" 
                     class="absolute inset-0 bg-blue-500 bg-opacity-10 border-2 border-dashed border-blue-500 rounded-lg flex items-center justify-center z-40">
                    <div class="bg-white p-4 rounded-lg shadow-lg">
                        <i class="fas fa-cloud-upload-alt text-3xl text-blue-500 mb-2"></i>
                        <p class="text-sm font-medium text-gray-700">Drop files to upload to @{{ currentFolder.name }}</p>
                    </div>
                </div>
                
                <div v-if="currentFolder">
                    <div class="mb-4">
                        <h4 class="font-semibold text-lg">@{{ currentFolder.name }}</h4>
                        <p class="text-sm text-gray-500">@{{ currentFolder.documents ? currentFolder.documents.length : 0 }} files</p>
                    </div>
                    
                    <!-- Simple List View -->
                    <div class="space-y-1">
                        <div v-for="doc in currentFolder.documents" :key="doc.id"
                             @click="selectFile(doc)"
                             class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer group transition-colors">
                            <i :class="getFileIcon(doc.type)" class="mr-3"></i>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">@{{ doc.name }}</p>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="text-xs text-gray-500">@{{ formatSize(doc.size) }}</span>
                                <div class="opacity-0 group-hover:opacity-100 transition-opacity flex items-center space-x-1">
                                    <button @click.stop="downloadFile(doc)"
                                            class="text-blue-500 hover:text-blue-700 p-1"
                                            title="Download">
                                        <i class="fas fa-download text-xs"></i>
                                    </button>
                                    <button @click.stop="deleteFile(doc)"
                                            class="text-red-500 hover:text-red-700 p-1"
                                            title="Delete">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div v-if="!currentFolder.documents || currentFolder.documents.length === 0"
                         class="text-center text-gray-400 py-12">
                        <i class="fas fa-folder-open text-4xl mb-3"></i>
                        <p class="text-sm">This folder is empty</p>
                        <p class="text-xs mt-2">Drag and drop files here to upload</p>
                    </div>
                </div>
                <div v-else class="text-center text-gray-400 py-12">
                    <i class="fas fa-folder text-4xl mb-3"></i>
                    <p class="text-sm">Select a folder to view files</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Folder Tree Template -->
<script type="text/x-template" id="folder-tree-template">
    <div class="folder-item">
        <div
            @click="selectFolder"
            @contextmenu.prevent="showFolderContextMenu"
            class="flex items-center p-2 hover:bg-blue-50 rounded cursor-pointer group"
            :style="{ paddingLeft: (level * 20) + 'px' }"
        >
            <i v-if="hasChildren"
               @click.stop="toggleExpand"
               :class="expanded ? 'fa-chevron-down' : 'fa-chevron-right'"
               class="fas text-xs text-gray-400 mr-1 cursor-pointer"></i>
            <i v-else class="w-3 mr-1"></i>
            <i :class="expanded ? 'fa-folder-open' : 'fa-folder'"
               class="fas text-yellow-500 mr-2"></i>
            <span class="text-sm flex-1">@{{ folder.name }}</span>
            <span v-if="folder.documents && folder.documents.length > 0"
                  class="text-xs text-gray-500 mr-2">
                (@{{ folder.documents.length }})
            </span>
            <!-- Folder Actions (visible on hover) -->
            <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                <button @click.stop="$root.openFolderMenu(folder, $event)"
                        class="text-gray-400 hover:text-gray-600 p-1"
                        title="Folder options">
                    <i class="fas fa-ellipsis-v text-xs"></i>
                </button>
            </div>
        </div>
        <div v-if="hasChildren && expanded" class="ml-2">
            <folder-tree 
                v-for="child in folder.children"
                :key="child.path"
                :folder="child"
                :level="level + 1"
            ></folder-tree>
        </div>
    </div>
</script>

<script>
// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    const { createApp } = Vue;

    // Main App
    const app = createApp({
        data() {
            return {
                loading: true,
                error: null,
                folders: null,
                currentFolder: null,
                rootFolders: [],
                showUploadModal: false,
                selectedFiles: null,
                uploading: false,
                isDragging: false,
                isQuickDragging: false,
                uploadProgress: {
                    show: false,
                    current: 0,
                    total: 0,
                    percentage: 0,
                    currentFile: null
                },
                uploadResults: [],
                uploadErrors: [],
                showCreateFolderModal: false,
                newFolderName: '',
                showRenameFolderModal: false,
                folderToRename: null,
                renameFolderName: '',
                showContextMenu: false,
                contextMenuX: 0,
                contextMenuY: 0,
                contextMenuFolder: null,
                // Sync status
                syncStatus: 'unknown', // unknown, synced, out-of-sync
                syncButtonText: 'Cek Sinkronisasi',
                syncButtonClass: 'bg-gray-500 hover:bg-gray-600',
                syncButtonIcon: 'fas fa-check-circle',
                syncChecking: false,
                syncing: false,
                syncIssues: null
            };
        },
        mounted() {
            this.loadFolders();
            // Close context menu when clicking outside
            document.addEventListener('click', this.closeContextMenu);
        },
        beforeUnmount() {
            document.removeEventListener('click', this.closeContextMenu);
        },
        methods: {
            async loadFolders() {
                this.loading = true;
                this.error = null;
                
                try {
                    const response = await fetch('/api/file-explorer/project/{{ $project->id }}/folders', {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    
                    if (!response.ok) {
                        throw new Error('HTTP error! status: ' + response.status);
                    }
                    
                    const data = await response.json();
                    
                    if (data.data && data.data.folders) {
                        this.folders = data.data.folders;
                        
                        // Set root folders (children of main folder)
                        if (this.folders.children) {
                            this.rootFolders = this.folders.children;
                        }
                    } else {
                        console.error('Vue: Invalid data structure', data);
                        this.error = 'Invalid data received from server';
                    }
                    
                } catch (error) {
                    console.error('Vue: Error loading folders', error);
                    this.error = 'Failed to load folders: ' + error.message;
                } finally {
                    this.loading = false;
                }
            },
            
            refreshFolders() {
                this.loadFolders();
            },
            
            selectFolder(folder) {
                this.currentFolder = folder;
            },
            
            formatSize(bytes) {
                if (!bytes) return '0 B';
                const units = ['B', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(1024));
                return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + units[i];
            },
            
            getFileIcon(type) {
                const icons = {
                    'pdf': 'fas fa-file-pdf text-red-500',
                    'doc': 'fas fa-file-word text-blue-500',
                    'docx': 'fas fa-file-word text-blue-500',
                    'xls': 'fas fa-file-excel text-green-500',
                    'xlsx': 'fas fa-file-excel text-green-500',
                    'ppt': 'fas fa-file-powerpoint text-orange-500',
                    'pptx': 'fas fa-file-powerpoint text-orange-500',
                    'txt': 'fas fa-file-alt text-gray-500',
                    'csv': 'fas fa-file-csv text-green-600',
                    'zip': 'fas fa-file-archive text-yellow-600',
                    'rar': 'fas fa-file-archive text-yellow-600',
                    'jpg': 'fas fa-file-image text-purple-500',
                    'jpeg': 'fas fa-file-image text-purple-500',
                    'png': 'fas fa-file-image text-purple-500',
                    'gif': 'fas fa-file-image text-purple-500',
                    'mp4': 'fas fa-file-video text-indigo-500',
                    'avi': 'fas fa-file-video text-indigo-500',
                    'mp3': 'fas fa-file-audio text-pink-500',
                    'kml': 'fas fa-map-marked-alt text-teal-500'
                };
                
                return icons[type] || 'fas fa-file text-gray-400';
            },
            
            selectFile(file) {
                // You can add preview functionality here
            },
            
            downloadFile(file) {
                // Use the file path directly for download
                if (file.path) {
                    // Create download link with GET request
                    const link = document.createElement('a');
                    link.href = '/api/file-explorer/project/{{ $project->id }}/documents/download-by-path?path=' + encodeURIComponent(file.path);
                    link.download = file.name;
                    link.style.display = 'none';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                } else {
                    alert('Cannot download this file. File path not found.');
                }
            },
            
            deleteFile(file) {
                if (confirm('Are you sure you want to delete ' + file.name + '?')) {
                    // Use the file path directly for deletion
                    if (file.path) {
                        fetch('/api/file-explorer/project/{{ $project->id }}/documents/delete-by-path', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                path: file.path
                            })
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Delete request failed');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                alert('File deleted successfully');
                                this.refreshFolders();
                            } else {
                                alert('Failed to delete file: ' + (data.message || 'Unknown error'));
                            }
                        })
                        .catch(error => {
                            console.error('Error deleting file:', error);
                            alert('Error deleting file: ' + error.message);
                        });
                    } else {
                        alert('Cannot delete this file. File path not found.');
                    }
                }
            },
            
            uploadFile() {
                if (!this.currentFolder) {
                    alert('Please select a folder first');
                    return;
                }
                this.showUploadModal = true;
                this.resetUploadState();
            },
            
            closeUploadModal() {
                this.showUploadModal = false;
                this.resetUploadState();
                if (this.uploadResults.length > 0) {
                    this.refreshFolders();
                }
            },
            
            resetUploadState() {
                this.selectedFiles = null;
                this.uploadResults = [];
                this.uploadErrors = [];
                this.uploadProgress = {
                    show: false,
                    current: 0,
                    total: 0,
                    percentage: 0,
                    currentFile: null
                };
                this.isDragging = false;
                if (this.$refs.fileInput) {
                    this.$refs.fileInput.value = '';
                }
            },
            
            handleFileSelect(event) {
                const files = event.target.files;
                if (files && files.length > 0) {
                    this.addFiles(Array.from(files));
                }
            },
            
            handleDrop(event) {
                this.isDragging = false;
                const files = event.dataTransfer.files;
                if (files && files.length > 0) {
                    this.addFiles(Array.from(files));
                }
            },
            
            handleQuickDrop(event) {
                this.isQuickDragging = false;
                if (!this.currentFolder) {
                    alert('Please select a folder first');
                    return;
                }
                
                const files = event.dataTransfer.files;
                if (files && files.length > 0) {
                    // Quick upload without modal
                    this.selectedFiles = Array.from(files);
                    this.performUpload(true); // true = quick upload
                }
            },
            
            addFiles(newFiles) {
                // Check file sizes (2GB = 2147483648 bytes)
                const maxSize = 2 * 1024 * 1024 * 1024; // 2GB in bytes
                const oversizedFiles = newFiles.filter(file => file.size > maxSize);
                if (oversizedFiles.length > 0) {
                    alert('Some files exceed the 2GB limit:\n' + oversizedFiles.map(f => f.name + ' (' + this.formatSize(f.size) + ')').join('\n'));
                    newFiles = newFiles.filter(file => file.size <= maxSize);
                }
                
                if (!this.selectedFiles) {
                    this.selectedFiles = [];
                }
                this.selectedFiles = [...this.selectedFiles, ...newFiles];
            },
            
            removeFile(index) {
                this.selectedFiles.splice(index, 1);
                if (this.selectedFiles.length === 0) {
                    this.selectedFiles = null;
                }
            },
            
            clearFiles() {
                this.selectedFiles = null;
                if (this.$refs.fileInput) {
                    this.$refs.fileInput.value = '';
                }
            },
            
            async performUpload(isQuickUpload = false) {
                if (!this.selectedFiles || this.selectedFiles.length === 0 || !this.currentFolder) {
                    return;
                }
                
                this.uploading = true;
                this.uploadResults = [];
                this.uploadErrors = [];
                this.uploadProgress = {
                    show: true,
                    current: 0,
                    total: this.selectedFiles.length,
                    percentage: 0,
                    currentFile: null
                };
                
                // Get the full folder path relative to project
                let folderPath = this.currentFolder.path;
                const projectPrefix = 'proyek/{{ Str::slug($project->name) }}/';
                if (folderPath.startsWith(projectPrefix)) {
                    folderPath = folderPath.substring(projectPrefix.length);
                }
                
                // Upload files with XMLHttpRequest for progress tracking
                for (let i = 0; i < this.selectedFiles.length; i++) {
                    const file = this.selectedFiles[i];
                    this.uploadProgress.current = i + 1;
                    this.uploadProgress.currentFile = file.name;
                    
                    try {
                        await this.uploadSingleFile(file, folderPath);
                        this.uploadResults.push(file.name);
                    } catch (error) {
                        console.error('Upload error for ' + file.name + ':', error);
                        this.uploadErrors.push(file.name + ': ' + error.message);
                    }
                    
                    // Update overall progress
                    this.uploadProgress.percentage = Math.round(((i + 1) / this.selectedFiles.length) * 100);
                }
                
                this.uploading = false;
                this.uploadProgress.show = false;
                
                // Show results
                if (isQuickUpload) {
                    if (this.uploadResults.length > 0) {
                        alert('Successfully uploaded ' + this.uploadResults.length + ' file(s)');
                        this.refreshFolders();
                    }
                    if (this.uploadErrors.length > 0) {
                        alert('Failed to upload ' + this.uploadErrors.length + ' file(s):\n' + this.uploadErrors.join('\n'));
                    }
                    this.resetUploadState();
                } else {
                    // Keep modal open to show results
                    this.selectedFiles = null;
                }
            },
            
            uploadSingleFile(file, folderPath) {
                return new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();
                    const formData = new FormData();
                    
                    formData.append('file', file);
                    formData.append('folder', folderPath);
                    
                    // Track upload progress for individual file
                    xhr.upload.addEventListener('progress', (e) => {
                        if (e.lengthComputable) {
                            const fileProgress = Math.round((e.loaded / e.total) * 100);
                            // You can add per-file progress tracking here if needed
                        }
                    });
                    
                    xhr.addEventListener('load', () => {
                        if (xhr.status === 200) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.success) {
                                    resolve(response);
                                } else {
                                    reject(new Error(response.message || 'Upload failed'));
                                }
                            } catch (e) {
                                reject(new Error('Invalid server response'));
                            }
                        } else {
                            reject(new Error('HTTP ' + xhr.status));
                        }
                    });
                    
                    xhr.addEventListener('error', () => {
                        reject(new Error('Network error'));
                    });
                    
                    xhr.open('POST', '/api/file-explorer/project/{{ $project->id }}/documents/upload');
                    xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
                    xhr.send(formData);
                });
            },
            
            selectFolderByPath(path) {
                // Helper function to re-select folder after refresh
                const findFolder = (folders, targetPath) => {
                    for (let folder of folders) {
                        if (folder.path === targetPath) {
                            return folder;
                        }
                        if (folder.children && folder.children.length > 0) {
                            const found = findFolder(folder.children, targetPath);
                            if (found) return found;
                        }
                    }
                    return null;
                };
                
                const folder = findFolder(this.rootFolders, path);
                if (folder) {
                    this.selectFolder(folder);
                }
            },
            
            openCreateFolderModal() {
                this.showCreateFolderModal = true;
                this.newFolderName = '';
            },
            
            closeCreateFolderModal() {
                this.showCreateFolderModal = false;
                this.newFolderName = '';
            },
            
            async createFolder() {
                if (!this.newFolderName) return;
                
                try {
                    // Get the parent path - remove the base proyek/project-slug/ prefix
                    let parentPath = '';
                    if (this.currentFolder && this.currentFolder.path) {
                        const projectPrefix = 'proyek/{{ Str::slug($project->name) }}/';
                        if (this.currentFolder.path.startsWith(projectPrefix)) {
                            parentPath = this.currentFolder.path.substring(projectPrefix.length);
                        } else {
                            parentPath = this.currentFolder.path;
                        }
                    }
                    
                    const response = await fetch('/api/file-explorer/project/{{ $project->id }}/folders/create', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            name: this.newFolderName,
                            parent_path: parentPath
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        this.closeCreateFolderModal();
                        await this.refreshFolders();
                        alert('Folder created successfully');
                    } else {
                        alert('Failed to create folder: ' + (data.message || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error creating folder:', error);
                    alert('Failed to create folder: ' + error.message);
                }
            },
            
            openFolderMenu(folder, event) {
                this.contextMenuFolder = folder;
                this.contextMenuX = event.clientX;
                this.contextMenuY = event.clientY;
                this.showContextMenu = true;
                event.stopPropagation();
            },
            
            closeContextMenu() {
                this.showContextMenu = false;
                this.contextMenuFolder = null;
            },
            
            openRenameFolderModal() {
                this.folderToRename = this.contextMenuFolder;
                this.renameFolderName = this.folderToRename.name;
                this.showRenameFolderModal = true;
                this.closeContextMenu();
            },
            
            closeRenameFolderModal() {
                this.showRenameFolderModal = false;
                this.folderToRename = null;
                this.renameFolderName = '';
            },
            
            async renameFolder() {
                if (!this.renameFolderName || !this.folderToRename) return;
                
                try {
                    // Extract the folder path relative to the project
                    let folderPath = this.folderToRename.path;
                    const projectPrefix = 'proyek/{{ Str::slug($project->name) }}/';
                    if (folderPath.startsWith(projectPrefix)) {
                        folderPath = folderPath.substring(projectPrefix.length);
                    }
                    
                    const response = await fetch('/api/file-explorer/project/{{ $project->id }}/folders/' + encodeURIComponent(folderPath) + '/rename', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            name: this.renameFolderName
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        this.closeRenameFolderModal();
                        await this.refreshFolders();
                        alert('Folder renamed successfully');
                    } else {
                        alert('Failed to rename folder: ' + (data.message || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error renaming folder:', error);
                    alert('Failed to rename folder: ' + error.message);
                }
            },
            
            async confirmDeleteFolder() {
                const folder = this.contextMenuFolder;
                this.closeContextMenu();
                
                if (!folder) return;
                
                const hasContents = (folder.documents && folder.documents.length > 0) ||
                                   (folder.children && folder.children.length > 0);
                
                let confirmMessage = 'Are you sure you want to delete the folder "' + folder.name + '"?';
                if (hasContents) {
                    confirmMessage += '\n\nThis folder contains files or subfolders. All contents will be deleted permanently.';
                }
                
                if (!confirm(confirmMessage)) return;
                
                try {
                    // Extract the folder path relative to the project
                    let folderPath = folder.path;
                    const projectPrefix = 'proyek/{{ Str::slug($project->name) }}/';
                    if (folderPath.startsWith(projectPrefix)) {
                        folderPath = folderPath.substring(projectPrefix.length);
                    }
                    
                    const response = await fetch('/api/file-explorer/project/{{ $project->id }}/folders/' + encodeURIComponent(folderPath), {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            force: hasContents
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Clear current folder if it was deleted
                        if (this.currentFolder && this.currentFolder.path === folder.path) {
                            this.currentFolder = null;
                        }
                        await this.refreshFolders();
                        alert('Folder deleted successfully');
                    } else {
                        alert('Failed to delete folder: ' + (data.message || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error deleting folder:', error);
                    alert('Failed to delete folder: ' + error.message);
                }
            },
            
            async checkSync() {
                this.syncChecking = true;
                
                try {
                    const response = await fetch('/api/file-explorer/project/{{ $project->id }}/check-sync', {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    
                    if (!response.ok) {
                        throw new Error('HTTP error! status: ' + response.status);
                    }
                    
                    const result = await response.json();
                    
                    if (result.success && result.data) {
                        const data = result.data;
                        
                        if (data.is_synced) {
                            this.syncStatus = 'synced';
                            this.syncButtonText = 'Tersinkronisasi';
                            this.syncButtonClass = 'bg-green-500 hover:bg-green-600';
                            this.syncButtonIcon = 'fas fa-check-circle';
                        } else {
                            this.syncStatus = 'out-of-sync';
                            this.syncButtonText = 'Sinkronkan (' + data.stats.total_issues + ')';
                            this.syncButtonClass = 'bg-orange-500 hover:bg-orange-600';
                            this.syncButtonIcon = 'fas fa-sync';
                            this.syncIssues = data.issues;
                            
                            // Show summary of issues
                            let message = 'Ditemukan masalah sinkronisasi:\n';
                            if (data.stats.orphaned_files > 0) {
                                message += '\n• ' + data.stats.orphaned_files + ' file belum tercatat';
                            }
                            if (data.stats.missing_files > 0) {
                                message += '\n• ' + data.stats.missing_files + ' file tidak ditemukan';
                            }
                            if (data.stats.modified_files > 0) {
                                message += '\n• ' + data.stats.modified_files + ' file berubah';
                            }
                            if (data.stats.orphaned_folders > 0) {
                                message += '\n• ' + data.stats.orphaned_folders + ' folder belum tercatat';
                            }
                            if (data.stats.missing_folders > 0) {
                                message += '\n• ' + data.stats.missing_folders + ' folder tidak ditemukan';
                            }
                            message += '\n\nKlik "Sinkronkan" untuk memperbaiki.';
                            
                            alert(message);
                        }
                    }
                    
                } catch (error) {
                    console.error('Error checking sync:', error);
                    alert('Gagal memeriksa sinkronisasi: ' + error.message);
                } finally {
                    this.syncChecking = false;
                }
            },
            
            async performSync() {
                if (this.syncStatus !== 'out-of-sync' || !this.syncIssues) {
                    return;
                }
                
                if (!confirm('Sinkronisasi akan:\n• Menambah file belum tercatat\n• Hapus record file hilang\n• Update metadata\n\nLanjutkan?')) {
                    return;
                }
                
                this.syncing = true;
                this.syncButtonText = 'Menyinkronkan...';
                
                try {
                    const response = await fetch('/api/file-explorer/project/{{ $project->id }}/sync-storage-db', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            soft_delete: false
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        let message = 'Sinkronisasi berhasil!\n';
                        if (result.data && result.data.results) {
                            const res = result.data.results;
                            if (res.added_files > 0) {
                                message += '\n• ' + res.added_files + ' file ditambahkan';
                            }
                            if (res.removed_files > 0) {
                                message += '\n• ' + res.removed_files + ' record dihapus';
                            }
                            if (res.updated_files > 0) {
                                message += '\n• ' + res.updated_files + ' file diperbarui';
                            }
                            if (res.added_folders > 0) {
                                message += '\n• ' + res.added_folders + ' folder ditambahkan';
                            }
                            if (res.removed_folders > 0) {
                                message += '\n• ' + res.removed_folders + ' folder dihapus';
                            }
                            
                            if (res.errors && res.errors.length > 0) {
                                message += '\n\nError:';
                                res.errors.slice(0, 3).forEach(err => {
                                    message += '\n• ' + err.substring(0, 50) + '...';
                                });
                            }
                        }
                        
                        alert(message);
                        
                        // Reset sync status
                        this.syncStatus = 'synced';
                        this.syncButtonText = 'Tersinkronisasi';
                        this.syncButtonClass = 'bg-green-500 hover:bg-green-600';
                        this.syncButtonIcon = 'fas fa-check-circle';
                        this.syncIssues = null;
                        
                        // Refresh folder structure
                        await this.refreshFolders();
                        
                    } else {
                        alert('Sinkronisasi gagal: ' + (result.message || 'Unknown error'));
                    }
                    
                } catch (error) {
                    console.error('Error performing sync:', error);
                    alert('Gagal sinkronisasi: ' + error.message);
                } finally {
                    this.syncing = false;
                    // Re-check sync status
                    await this.checkSync();
                }
            },
            
            handleSyncButton() {
                if (this.syncStatus === 'unknown' || this.syncStatus === 'synced') {
                    this.checkSync();
                } else if (this.syncStatus === 'out-of-sync') {
                    this.performSync();
                }
            },
            
            async downloadFolderAsZip() {
                const folder = this.contextMenuFolder;
                this.closeContextMenu();
                
                if (!folder) return;
                
                try {
                    // Extract folder path relative to project
                    let folderPath = folder.path;
                    const projectPrefix = 'proyek/{{ Str::slug($project->name) }}/';
                    if (folderPath.startsWith(projectPrefix)) {
                        folderPath = folderPath.substring(projectPrefix.length);
                    }
                    
                    // Create a form and submit it to trigger download
                    // This is more reliable for file downloads from Laravel
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '/projects/{{ $project->id }}/download-folder-zip';
                    form.style.display = 'none';
                    
                    // Add CSRF token
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
                    form.appendChild(csrfInput);
                    
                    // Add folder path
                    const pathInput = document.createElement('input');
                    pathInput.type = 'hidden';
                    pathInput.name = 'folder_path';
                    pathInput.value = folderPath;
                    form.appendChild(pathInput);
                    
                    // Append to body and submit
                    document.body.appendChild(form);
                    form.submit();
                    
                    // Clean up
                    setTimeout(() => {
                        document.body.removeChild(form);
                    }, 100);
                    
                } catch (error) {
                    alert('Failed to download folder as ZIP: ' + error.message);
                }
            }
        }
    });

    // Register FolderTree component
    app.component('folder-tree', {
        template: '#folder-tree-template',
        props: ['folder', 'level'],
        data() {
            return {
                expanded: this.level < 2 // Auto expand first 2 levels
            };
        },
        computed: {
            hasChildren() {
                return this.folder.children && this.folder.children.length > 0;
            }
        },
        methods: {
            toggleExpand() {
                this.expanded = !this.expanded;
            },
            selectFolder() {
                this.$root.selectFolder(this.folder);
            },
            showFolderContextMenu(event) {
                this.$root.openFolderMenu(this.folder, event);
            }
        }
    });
    
    // Check if element exists before mounting
    const mountElement = document.querySelector('#vue-file-explorer-app.desktop-file-explorer');
    if (mountElement) {
        app.mount('#vue-file-explorer-app');
    }
});
</script>