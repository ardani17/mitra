{{-- Simple Vue File Explorer --}}
<!-- Load Vue 3 from CDN first -->
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<!-- Load Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div id="vue-file-explorer-app">
    <!-- Upload Modal -->
    <div v-if="showUploadModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Upload File</h3>
            </div>
            <div class="px-6 py-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Upload to folder:</label>
                    <p class="text-sm text-gray-600 bg-gray-50 p-2 rounded">@{{ currentFolder ? currentFolder.name : 'Select a folder first' }}</p>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select file:</label>
                    <input type="file"
                           ref="fileInput"
                           @change="handleFileSelect"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div v-if="selectedFile" class="mb-4 p-3 bg-blue-50 rounded">
                    <p class="text-sm text-gray-700">
                        <strong>File:</strong> @{{ selectedFile.name }}<br>
                        <strong>Size:</strong> @{{ formatSize(selectedFile.size) }}
                    </p>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-2">
                <button @click="closeUploadModal"
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded text-sm">
                    Cancel
                </button>
                <button @click="performUpload"
                        :disabled="!selectedFile || !currentFolder || uploading"
                        class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    <span v-if="uploading">
                        <i class="fas fa-spinner fa-spin mr-1"></i> Uploading...
                    </span>
                    <span v-else>Upload</span>
                </button>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold">Document Manager</h3>
            <div class="flex items-center space-x-2">
                <button @click="uploadFile" class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-sm">
                    <i class="fas fa-upload mr-1"></i> Upload
                </button>
                <button @click="refreshFolders" class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm">
                    <i class="fas fa-sync-alt mr-1"></i> Refresh
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
            
            <!-- Content -->
            <div class="flex-1 p-4">
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
            class="flex items-center p-2 hover:bg-blue-50 rounded cursor-pointer"
            :style="{ paddingLeft: (level * 20) + 'px' }"
        >
            <i v-if="hasChildren" 
               @click.stop="toggleExpand"
               :class="expanded ? 'fa-chevron-down' : 'fa-chevron-right'"
               class="fas text-xs text-gray-400 mr-1 cursor-pointer"></i>
            <i v-else class="w-3 mr-1"></i>
            <i :class="expanded ? 'fa-folder-open' : 'fa-folder'" 
               class="fas text-yellow-500 mr-2"></i>
            <span class="text-sm">@{{ folder.name }}</span>
            <span v-if="folder.documents && folder.documents.length > 0" 
                  class="ml-auto text-xs text-gray-500">
                (@{{ folder.documents.length }})
            </span>
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
                selectedFile: null,
                uploading: false
            };
        },
        mounted() {
            this.loadFolders();
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
                // Create download link
                const link = document.createElement('a');
                link.href = '/api/documents/' + file.id + '/download';
                link.download = file.name;
                link.click();
            },
            
            deleteFile(file) {
                if (confirm('Are you sure you want to delete ' + file.name + '?')) {
                    fetch('/api/file-explorer/project/{{ $project->id }}/documents/' + file.id, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    }).then(() => {
                        this.refreshFolders();
                    });
                }
            },
            
            uploadFile() {
                if (!this.currentFolder) {
                    alert('Please select a folder first');
                    return;
                }
                this.showUploadModal = true;
            },
            
            closeUploadModal() {
                this.showUploadModal = false;
                this.selectedFile = null;
                if (this.$refs.fileInput) {
                    this.$refs.fileInput.value = '';
                }
            },
            
            handleFileSelect(event) {
                const files = event.target.files;
                if (files && files.length > 0) {
                    this.selectedFile = files[0];
                }
            },
            
            async performUpload() {
                if (!this.selectedFile || !this.currentFolder) {
                    console.error('Upload Debug: Missing file or folder');
                    return;
                }
                
                console.log('Upload Debug: Starting upload');
                console.log('Upload Debug: File:', this.selectedFile.name, 'Size:', this.selectedFile.size);
                console.log('Upload Debug: Current folder path:', this.currentFolder.path);
                
                this.uploading = true;
                
                const formData = new FormData();
                formData.append('file', this.selectedFile);
                // Extract folder name from path (e.g., "proyek/xxx/dokumen/teknis" -> "teknis")
                const folderName = this.currentFolder.path.split('/').pop();
                formData.append('folder', folderName);
                
                console.log('Upload Debug: Folder name extracted:', folderName);
                console.log('Upload Debug: Sending to URL:', '/api/file-explorer/project/{{ $project->id }}/documents/upload');
                
                try {
                    const response = await fetch('/api/file-explorer/project/{{ $project->id }}/documents/upload', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: formData
                    });
                    
                    console.log('Upload Debug: Response status:', response.status);
                    console.log('Upload Debug: Response headers:', response.headers);
                    
                    let data;
                    const contentType = response.headers.get("content-type");
                    if (contentType && contentType.indexOf("application/json") !== -1) {
                        data = await response.json();
                        console.log('Upload Debug: Response data:', data);
                    } else {
                        // If response is not JSON, it might be an error page
                        const text = await response.text();
                        console.error('Upload Debug: Non-JSON response:', text);
                        throw new Error('Server returned non-JSON response. Check if you are logged in.');
                    }
                    
                    if (response.ok && data && data.success) {
                        console.log('Upload Debug: Upload successful!');
                        this.closeUploadModal();
                        // Refresh folders to show new file
                        await this.loadFolders();
                        // Re-select current folder to refresh file list
                        const currentPath = this.currentFolder.path;
                        this.selectFolderByPath(currentPath);
                        alert('File uploaded successfully!');
                    } else {
                        console.error('Upload Debug: Upload failed with message:', data.message);
                        throw new Error(data.message || 'Upload failed');
                    }
                } catch (error) {
                    console.error('Upload Debug: Error caught:', error);
                    console.error('Upload Debug: Error stack:', error.stack);
                    alert('Upload failed: ' + error.message);
                } finally {
                    this.uploading = false;
                    console.log('Upload Debug: Upload process completed');
                }
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
                console.log('Folder ' + this.folder.name + ' expanded:', this.expanded);
            },
            selectFolder() {
                this.$root.selectFolder(this.folder);
            }
        }
    });
    
    app.mount('#vue-file-explorer-app');
    console.log('Vue app mounted successfully');
});
</script>