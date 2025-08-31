import { createApp } from 'vue';

const FileExplorer = {
    template: `
        <div class="file-explorer">
            <!-- Header -->
            <div class="bg-white border-b px-4 py-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-folder-open mr-2"></i>
                            Document Manager
                        </h3>
                        
                        <div v-if="syncStatus && syncStatus.stats" class="flex items-center space-x-2 text-sm">
                            <span class="text-gray-500">Sync:</span>
                            <span :class="getSyncStatusColor(syncStatus.last_sync_status || 'pending')">
                                {{ syncStatus.stats.synced || 0 }}/{{ syncStatus.stats.total || 0 }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <button @click="syncProject" :disabled="syncing" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-sync" :class="syncing ? 'fa-spin' : ''"></i>
                            {{ syncing ? 'Syncing...' : 'Sync' }}
                        </button>
                        
                        <button @click="showUploadModal = true" class="btn btn-sm btn-primary">
                            <i class="fas fa-upload mr-1"></i>
                            Upload Files
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="flex flex-1 overflow-hidden">
                <!-- Sidebar - Folder Tree -->
                <div class="w-64 bg-gray-50 border-r overflow-y-auto">
                    <div class="p-3">
                        <folder-tree 
                            v-if="folders"
                            :folder="folders" 
                            :current-path="currentPath"
                            @select="selectFolder"
                        />
                    </div>
                </div>
                
                <!-- File Display Area -->
                <div class="flex-1 overflow-y-auto p-4">
                    <!-- Loading State -->
                    <div v-if="loading" class="flex items-center justify-center h-64">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-spin text-4xl text-gray-400 mb-3"></i>
                            <p class="text-gray-500">Loading files...</p>
                        </div>
                    </div>
                    
                    <!-- Empty State -->
                    <div v-else-if="!currentFolder || !currentFolder.documents || currentFolder.documents.length === 0" 
                         class="flex items-center justify-center h-64">
                        <div class="text-center">
                            <i class="fas fa-folder-open text-6xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500 mb-3">No files in this folder</p>
                            <button @click="showUploadModal = true" class="btn btn-sm btn-primary">
                                <i class="fas fa-upload mr-1"></i>
                                Upload Files
                            </button>
                        </div>
                    </div>
                    
                    <!-- Grid View -->
                    <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                        <div v-for="doc in currentFolder.documents" 
                             :key="doc.id"
                             @click="selectDocument(doc)"
                             class="relative group cursor-pointer p-3 rounded-lg hover:bg-gray-100 transition"
                             :class="selectedItems.includes(doc.id) ? 'bg-blue-50 ring-2 ring-blue-500' : ''">
                            
                            <div class="text-center mb-2">
                                <i :class="getFileIcon(doc)" class="fas text-4xl text-gray-400"></i>
                            </div>
                            
                            <p class="text-xs text-gray-700 text-center truncate" :title="doc.name">
                                {{ doc.name }}
                            </p>
                            
                            <p class="text-xs text-gray-500 text-center">
                                {{ formatFileSize(doc.size) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Upload Modal -->
            <div v-if="showUploadModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showUploadModal = false"></div>
                    
                    <div class="relative bg-white rounded-lg max-w-md w-full p-6">
                        <h3 class="text-lg font-semibold mb-4">Upload Files</h3>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Select Files
                            </label>
                            <input type="file" 
                                   multiple
                                   @change="handleFileSelect"
                                   class="form-control">
                        </div>
                        
                        <div class="flex justify-end space-x-2">
                            <button @click="showUploadModal = false" class="btn btn-secondary">
                                Cancel
                            </button>
                            <button @click="uploadFiles" 
                                    :disabled="!selectedFiles || uploading"
                                    class="btn btn-primary">
                                {{ uploading ? 'Uploading...' : 'Upload' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `,
    
    props: {
        projectId: {
            type: Number,
            required: true
        }
    },
    
    data() {
        return {
            loading: false,
            uploading: false,
            syncing: false,
            folders: null,
            currentPath: '',
            currentFolder: null,
            selectedItems: [],
            syncStatus: null,
            showUploadModal: false,
            selectedFiles: null
        };
    },
    
    mounted() {
        this.loadFolderStructure();
    },
    
    methods: {
        async loadFolderStructure() {
            this.loading = true;
            try {
                const response = await fetch(`/api/file-explorer/project/${this.projectId}/folders`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (!response.ok) throw new Error('Failed to load folders');
                
                const data = await response.json();
                this.folders = data.data.folders;
                this.syncStatus = data.data.sync_status;
                
                if (!this.currentFolder && this.folders) {
                    this.selectFolder(this.folders);
                }
            } catch (error) {
                console.error('Error loading folders:', error);
            } finally {
                this.loading = false;
            }
        },
        
        selectFolder(folder) {
            this.currentFolder = folder;
            this.currentPath = folder.path;
            this.selectedItems = [];
        },
        
        selectDocument(doc) {
            const index = this.selectedItems.indexOf(doc.id);
            if (index > -1) {
                this.selectedItems.splice(index, 1);
            } else {
                this.selectedItems.push(doc.id);
            }
        },
        
        handleFileSelect(event) {
            this.selectedFiles = event.target.files;
        },
        
        async uploadFiles() {
            if (!this.selectedFiles || this.selectedFiles.length === 0) return;
            
            this.uploading = true;
            
            for (let file of this.selectedFiles) {
                const formData = new FormData();
                formData.append('file', file);
                
                // Get folder path
                let folderPath = '';
                if (this.currentPath && this.folders) {
                    const basePath = this.folders.path + '/';
                    folderPath = this.currentPath.replace(basePath, '');
                }
                
                if (!folderPath) {
                    folderPath = 'dokumen';
                }
                
                formData.append('folder', folderPath);
                formData.append('description', '');
                
                try {
                    await fetch(`/api/file-explorer/project/${this.projectId}/documents/upload`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: formData
                    });
                } catch (error) {
                    console.error('Upload error:', error);
                }
            }
            
            this.uploading = false;
            this.showUploadModal = false;
            this.selectedFiles = null;
            await this.loadFolderStructure();
        },
        
        async syncProject() {
            this.syncing = true;
            try {
                const response = await fetch(`/api/file-explorer/project/${this.projectId}/sync`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (!response.ok) throw new Error('Failed to sync');
                
                await this.loadFolderStructure();
            } catch (error) {
                console.error('Sync error:', error);
            } finally {
                this.syncing = false;
            }
        },
        
        formatFileSize(bytes) {
            if (!bytes) return '0 B';
            const units = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(1024));
            return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + units[i];
        },
        
        getFileIcon(doc) {
            const ext = doc.type?.toLowerCase();
            const iconMap = {
                'pdf': 'fa-file-pdf',
                'doc': 'fa-file-word',
                'docx': 'fa-file-word',
                'xls': 'fa-file-excel',
                'xlsx': 'fa-file-excel',
                'jpg': 'fa-file-image',
                'jpeg': 'fa-file-image',
                'png': 'fa-file-image',
                'zip': 'fa-file-archive',
                'txt': 'fa-file-alt'
            };
            return iconMap[ext] || 'fa-file';
        },
        
        getSyncStatusColor(status) {
            const colors = {
                'synced': 'text-green-600',
                'pending': 'text-yellow-600',
                'failed': 'text-red-600'
            };
            return colors[status] || 'text-gray-600';
        }
    }
};

// Recursive Folder Tree Component
const FolderTree = {
    name: 'FolderTree',
    template: `
        <div class="folder-item">
            <button @click="$emit('select', folder)"
                    class="flex items-center w-full p-2 hover:bg-gray-100 rounded"
                    :class="currentPath === folder.path ? 'bg-blue-50' : ''"
                    :style="{ paddingLeft: (level * 1) + 'rem' }">
                <i v-if="hasChildren" 
                   @click.stop="expanded = !expanded"
                   :class="expanded ? 'fa-chevron-down' : 'fa-chevron-right'"
                   class="fas mr-1 text-gray-400 text-xs cursor-pointer"></i>
                <i class="fas fa-folder mr-2 text-yellow-500"></i>
                <span>{{ folder.name }}</span>
                <span v-if="folder.documents && folder.documents.length > 0" 
                      class="ml-auto text-xs text-gray-500">
                    ({{ folder.documents.length }})
                </span>
            </button>
            
            <div v-if="hasChildren && expanded" class="ml-4">
                <folder-tree v-for="child in folder.children"
                             :key="child.path"
                             :folder="child"
                             :current-path="currentPath"
                             :level="level + 1"
                             @select="$emit('select', $event)" />
            </div>
        </div>
    `,
    
    props: {
        folder: Object,
        currentPath: String,
        level: {
            type: Number,
            default: 0
        }
    },
    
    data() {
        return {
            expanded: this.level === 0 || this.folder.name === 'dokumen'
        };
    },
    
    computed: {
        hasChildren() {
            return this.folder.children && this.folder.children.length > 0;
        }
    }
};

// Initialize Vue app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const fileExplorerEl = document.getElementById('vue-file-explorer');
    if (fileExplorerEl) {
        const projectId = fileExplorerEl.dataset.projectId;
        
        const app = createApp(FileExplorer, {
            projectId: parseInt(projectId)
        });
        
        app.component('folder-tree', FolderTree);
        app.mount('#vue-file-explorer');
    }
});