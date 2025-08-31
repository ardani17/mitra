/**
 * Alpine.js File Explorer Component
 * For Laravel Project Document Management System
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('fileExplorer', (projectId) => ({
        // State
        projectId: projectId,
        loading: false,
        uploading: false,
        syncing: false,
        
        // Data
        folders: null,
        currentPath: '',
        currentFolder: null,
        selectedItems: [],
        syncStatus: null,
        
        // UI State
        showUploadModal: false,
        showCreateFolderModal: false,
        showRenameModal: false,
        showMoveModal: false,
        showDocumentActionsModal: false, // Mobile actions modal
        selectedDocument: null, // For mobile actions
        dragOver: false,
        viewMode: 'grid', // 'grid' or 'list'
        sortBy: 'name', // 'name', 'size', 'date'
        sortOrder: 'asc',
        
        // Forms
        uploadForm: {
            files: [],
            folder: '',
            description: ''
        },
        
        newFolderForm: {
            name: '',
            parentPath: ''
        },
        
        renameForm: {
            documentId: null,
            currentName: '',
            newName: ''
        },
        
        moveForm: {
            documentId: null,
            destination: ''
        },
        
        // Search
        searchQuery: '',
        
        // Initialize
        async init() {
            // Initialize syncStatus with default values
            this.syncStatus = {
                stats: {
                    total: 0,
                    synced: 0,
                    pending: 0,
                    failed: 0,
                    syncing: 0,
                    out_of_sync: 0,
                    percentage: 0
                },
                last_sync: null,
                last_sync_status: null,
                last_sync_duration: null,
                is_syncing: false,
                needs_sync: false
            };
            
            await this.loadFolderStructure();
            await this.loadSyncStatus();
            
            // Setup drag and drop
            this.setupDragAndDrop();
            
            // Auto-refresh sync status
            setInterval(() => {
                if (this.syncing) {
                    this.loadSyncStatus();
                }
            }, 5000);
        },
        
        // Load folder structure
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
                
                // Debug: Log folder structure
                console.log('Folder structure received:', this.folders);
                if (this.folders && this.folders.children) {
                    this.folders.children.forEach(child => {
                        console.log('Level 1 - Child folder:', child.name, 'Has children:', child.children ? child.children.length : 0);
                        if (child.name === 'dokumen' && child.children) {
                            child.children.forEach(subchild => {
                                console.log('  Level 2 - Subchild:', subchild.name, 'Has children:', subchild.children ? subchild.children.length : 0);
                                if (subchild.name === 'teknis' && subchild.children) {
                                    console.log('  TEKNIS FOLDER HAS CHILDREN:', subchild.children);
                                    subchild.children.forEach(subsubchild => {
                                        console.log('    Level 3 - Sub-subchild:', subsubchild.name);
                                    });
                                }
                            });
                        }
                    });
                }
                
                // Safely set sync status with defaults
                if (data.data.sync_status) {
                    this.syncStatus = {
                        ...this.syncStatus,
                        ...data.data.sync_status,
                        stats: {
                            ...this.syncStatus.stats,
                            ...(data.data.sync_status.stats || {})
                        }
                    };
                }
                
                // Set initial folder
                if (!this.currentFolder && this.folders) {
                    this.selectFolder(this.folders);
                }
            } catch (error) {
                console.error('Error loading folders:', error);
                this.showNotification('Failed to load folder structure', 'error');
            } finally {
                this.loading = false;
            }
        },
        
        // Load sync status
        async loadSyncStatus() {
            try {
                const response = await fetch(`/api/file-explorer/project/${this.projectId}/sync/status`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (!response.ok) return;
                
                const data = await response.json();
                
                // Safely update sync status
                if (data.data) {
                    this.syncStatus = {
                        ...this.syncStatus,
                        ...data.data,
                        stats: {
                            ...this.syncStatus.stats,
                            ...(data.data.stats || {})
                        }
                    };
                    
                    // Update syncing state
                    this.syncing = data.data.is_syncing || false;
                }
            } catch (error) {
                console.error('Error loading sync status:', error);
            }
        },
        
        // Select folder
        selectFolder(folder) {
            this.currentFolder = folder;
            this.currentPath = folder.path;
            this.selectedItems = [];
        },
        
        // Navigate to parent folder
        navigateUp() {
            if (!this.currentFolder || !this.currentFolder.path) return;
            
            const pathParts = this.currentFolder.path.split('/');
            pathParts.pop();
            const parentPath = pathParts.join('/');
            
            // Find parent folder
            const findFolder = (folder, targetPath) => {
                if (folder.path === targetPath) return folder;
                
                if (folder.children) {
                    for (let child of folder.children) {
                        const found = findFolder(child, targetPath);
                        if (found) return found;
                    }
                }
                return null;
            };
            
            const parentFolder = findFolder(this.folders, parentPath);
            if (parentFolder) {
                this.selectFolder(parentFolder);
            }
        },
        
        // Get breadcrumb path
        getBreadcrumbs() {
            if (!this.currentPath) return [];
            
            const parts = this.currentPath.split('/');
            const breadcrumbs = [];
            let path = '';
            
            parts.forEach((part, index) => {
                if (index > 0) path += '/';
                path += part;
                
                breadcrumbs.push({
                    name: part,
                    path: path
                });
            });
            
            return breadcrumbs;
        },
        
        // Navigate to breadcrumb
        navigateToBreadcrumb(breadcrumb) {
            const findFolder = (folder, targetPath) => {
                if (folder.path === targetPath) return folder;
                
                if (folder.children) {
                    for (let child of folder.children) {
                        const found = findFolder(child, targetPath);
                        if (found) return found;
                    }
                }
                return null;
            };
            
            const targetFolder = findFolder(this.folders, breadcrumb.path);
            if (targetFolder) {
                this.selectFolder(targetFolder);
            }
        },
        
        
        // Toggle item selection
        toggleSelection(item) {
            const index = this.selectedItems.findIndex(i => i.id === item.id);
            if (index > -1) {
                this.selectedItems.splice(index, 1);
            } else {
                this.selectedItems.push(item);
            }
        },
        
        // Select all items
        selectAll() {
            if (this.currentFolder && this.currentFolder.documents) {
                this.selectedItems = [...this.currentFolder.documents];
            }
        },
        
        // Clear selection
        clearSelection() {
            this.selectedItems = [];
        },
        
        // Sort documents
        sortDocuments(documents) {
            if (!documents) return [];
            
            return [...documents].sort((a, b) => {
                let compareValue = 0;
                
                switch (this.sortBy) {
                    case 'name':
                        compareValue = a.name.localeCompare(b.name);
                        break;
                    case 'size':
                        compareValue = a.size - b.size;
                        break;
                    case 'date':
                        compareValue = new Date(a.created_at) - new Date(b.created_at);
                        break;
                }
                
                return this.sortOrder === 'asc' ? compareValue : -compareValue;
            });
        },
        
        // Filter documents by search
        filterDocuments(documents) {
            if (!this.searchQuery || !documents) return documents;
            
            const query = this.searchQuery.toLowerCase();
            return documents.filter(doc =>
                doc.name.toLowerCase().includes(query)
            );
        },
        
        // Get display documents
        getDisplayDocuments() {
            let documents = this.currentFolder?.documents || [];
            documents = this.filterDocuments(documents);
            documents = this.sortDocuments(documents);
            return documents;
        },
        
        
        // Upload files
        async uploadFiles(event) {
            const files = event.target.files || event.dataTransfer.files;
            if (!files.length) return;
            
            this.uploading = true;
            const uploadPromises = [];
            
            for (let file of files) {
                const formData = new FormData();
                formData.append('file', file);
                
                // Get folder path correctly
                let folderPath = '';
                if (this.currentPath && this.folders) {
                    // Remove project base path to get relative folder
                    const basePath = this.folders.path + '/';
                    folderPath = this.currentPath.replace(basePath, '');
                }
                
                // If no folder selected, use root or first category
                if (!folderPath) {
                    folderPath = 'dokumen'; // Default to dokumen folder
                }
                
                formData.append('folder', folderPath);
                formData.append('description', this.uploadForm.description || '');
                
                const promise = fetch(`/api/file-explorer/project/${this.projectId}/documents/upload`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });
                
                uploadPromises.push(promise);
            }
            
            try {
                const responses = await Promise.allSettled(uploadPromises);
                
                let successCount = 0;
                let failCount = 0;
                
                for (let response of responses) {
                    if (response.status === 'fulfilled' && response.value.ok) {
                        successCount++;
                    } else {
                        failCount++;
                    }
                }
                
                if (successCount > 0) {
                    this.showNotification(`${successCount} file(s) uploaded successfully`, 'success');
                    await this.loadFolderStructure();
                }
                
                if (failCount > 0) {
                    this.showNotification(`${failCount} file(s) failed to upload`, 'error');
                }
                
            } catch (error) {
                console.error('Upload error:', error);
                this.showNotification('Failed to upload files', 'error');
            } finally {
                this.uploading = false;
                this.showUploadModal = false;
                this.uploadForm = { files: [], folder: '', description: '' };
            }
        },
        
        // Create folder
        async createFolder() {
            if (!this.newFolderForm.name) return;
            
            try {
                const response = await fetch(`/api/file-explorer/project/${this.projectId}/folders/create`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        name: this.newFolderForm.name,
                        parent_path: this.currentPath.replace('proyek/' + this.folders.name + '/', '')
                    })
                });
                
                if (!response.ok) throw new Error('Failed to create folder');
                
                this.showNotification('Folder created successfully', 'success');
                await this.loadFolderStructure();
                
            } catch (error) {
                console.error('Error creating folder:', error);
                this.showNotification('Failed to create folder', 'error');
            } finally {
                this.showCreateFolderModal = false;
                this.newFolderForm = { name: '', parentPath: '' };
            }
        },
        
        // Delete document
        async deleteDocument(document) {
            if (!confirm(`Are you sure you want to delete "${document.name}"?`)) return;
            
            try {
                const response = await fetch(`/api/file-explorer/project/${this.projectId}/documents/${document.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (!response.ok) throw new Error('Failed to delete document');
                
                this.showNotification('Document deleted successfully', 'success');
                await this.loadFolderStructure();
                
            } catch (error) {
                console.error('Error deleting document:', error);
                this.showNotification('Failed to delete document', 'error');
            }
        },
        
        // Delete selected documents
        async deleteSelected() {
            if (!this.selectedItems.length) return;
            
            const count = this.selectedItems.length;
            if (!confirm(`Are you sure you want to delete ${count} item(s)?`)) return;
            
            const deletePromises = this.selectedItems.map(item =>
                fetch(`/api/file-explorer/project/${this.projectId}/documents/${item.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
            );
            
            try {
                await Promise.all(deletePromises);
                this.showNotification(`${count} item(s) deleted successfully`, 'success');
                await this.loadFolderStructure();
                this.clearSelection();
            } catch (error) {
                console.error('Error deleting documents:', error);
                this.showNotification('Failed to delete some documents', 'error');
            }
        },
        
        // Rename document
        async renameDocument() {
            if (!this.renameForm.newName) return;
            
            try {
                const response = await fetch(`/api/file-explorer/project/${this.projectId}/documents/${this.renameForm.documentId}/rename`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        name: this.renameForm.newName
                    })
                });
                
                if (!response.ok) throw new Error('Failed to rename document');
                
                this.showNotification('Document renamed successfully', 'success');
                await this.loadFolderStructure();
                
            } catch (error) {
                console.error('Error renaming document:', error);
                this.showNotification('Failed to rename document', 'error');
            } finally {
                this.showRenameModal = false;
                this.renameForm = { documentId: null, currentName: '', newName: '' };
            }
        },
        
        // Show rename modal
        showRename(document) {
            this.renameForm = {
                documentId: document.id,
                currentName: document.name,
                newName: document.name.replace(/\.[^/.]+$/, '') // Remove extension
            };
            this.showRenameModal = true;
        },
        
        // Move document
        async moveDocument() {
            if (!this.moveForm.destination) return;
            
            try {
                const response = await fetch(`/api/file-explorer/project/${this.projectId}/documents/${this.moveForm.documentId}/move`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        destination: this.moveForm.destination
                    })
                });
                
                if (!response.ok) throw new Error('Failed to move document');
                
                this.showNotification('Document moved successfully', 'success');
                await this.loadFolderStructure();
                
            } catch (error) {
                console.error('Error moving document:', error);
                this.showNotification('Failed to move document', 'error');
            } finally {
                this.showMoveModal = false;
                this.moveForm = { documentId: null, destination: '' };
            }
        },
        
        // Show move modal
        showMove(document) {
            this.moveForm = {
                documentId: document.id,
                destination: ''
            };
            this.showMoveModal = true;
        },
        
        // Download document
        downloadDocument(document) {
            window.open(`/api/documents/${document.id}/download`, '_blank');
        },
        
        // Preview document
        previewDocument(document) {
            if (document.can_preview) {
                window.open(`/api/documents/${document.id}/preview`, '_blank');
            } else {
                this.downloadDocument(document);
            }
        },
        
        // Sync project
        async syncProject() {
            if (this.syncing) return;
            
            this.syncing = true;
            
            try {
                const response = await fetch(`/api/file-explorer/project/${this.projectId}/sync`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (!response.ok) throw new Error('Failed to start sync');
                
                const data = await response.json();
                
                if (data.success) {
                    this.showNotification('Sync started successfully', 'success');
                    
                    // Poll for sync status
                    const pollInterval = setInterval(async () => {
                        await this.loadSyncStatus();
                        
                        if (!this.syncing) {
                            clearInterval(pollInterval);
                            await this.loadFolderStructure();
                            this.showNotification('Sync completed', 'success');
                        }
                    }, 3000);
                } else {
                    throw new Error(data.message);
                }
                
            } catch (error) {
                console.error('Error syncing project:', error);
                this.showNotification('Failed to sync project', 'error');
                this.syncing = false;
            }
        },
        
        // Setup drag and drop
        setupDragAndDrop() {
            // Delay setup to ensure DOM is ready
            setTimeout(() => {
                try {
                    // Prevent default drag behaviors
                    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                        document.addEventListener(eventName, (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                        }, false);
                    });
                    
                    // Check if component element exists
                    if (!this.$el) {
                        return; // Silently return if not ready
                    }
                    
                    // Handle drag enter/leave
                    const dropZone = this.$el.querySelector('.file-drop-zone');
                    if (dropZone) {
                        dropZone.addEventListener('dragenter', () => {
                            this.dragOver = true;
                        });
                        
                        dropZone.addEventListener('dragleave', (e) => {
                            if (e.target === dropZone) {
                                this.dragOver = false;
                            }
                        });
                        
                        dropZone.addEventListener('drop', (e) => {
                            this.dragOver = false;
                            this.uploadFiles(e);
                        });
                    }
                } catch (error) {
                    // Silently catch any errors during drag-drop setup
                    console.debug('Drag and drop setup skipped:', error.message);
                }
            }, 100); // Small delay to ensure DOM is ready
        },
        
        // Show notification
        showNotification(message, type = 'info') {
            // This would integrate with your notification system
            // For now, using simple alert
            if (window.Alpine && window.Alpine.store('notifications')) {
                window.Alpine.store('notifications').add(message, type);
            } else {
                console.log(`[${type.toUpperCase()}] ${message}`);
            }
        },
        
        // Format file size
        formatFileSize(bytes) {
            if (!bytes) return '0 B';
            
            const units = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(1024));
            
            return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + units[i];
        },
        
        // Format date
        formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        },
        
        
        
        
        // Get file icon
        getFileIcon(document) {
            const ext = document.type?.toLowerCase() || document.name?.split('.').pop()?.toLowerCase();
            
            const iconMap = {
                'pdf': 'fa-file-pdf',
                'doc': 'fa-file-word',
                'docx': 'fa-file-word',
                'xls': 'fa-file-excel',
                'xlsx': 'fa-file-excel',
                'ppt': 'fa-file-powerpoint',
                'pptx': 'fa-file-powerpoint',
                'jpg': 'fa-file-image',
                'jpeg': 'fa-file-image',
                'png': 'fa-file-image',
                'gif': 'fa-file-image',
                'zip': 'fa-file-archive',
                'rar': 'fa-file-archive',
                'txt': 'fa-file-alt',
                'csv': 'fa-file-csv'
            };
            
            return iconMap[ext] || 'fa-file';
        },
        
        getFileIconColor(document) {
            const ext = document.type?.toLowerCase() || document.name?.split('.').pop()?.toLowerCase();
            
            const colorMap = {
                'pdf': 'text-red-500',
                'doc': 'text-blue-500',
                'docx': 'text-blue-500',
                'xls': 'text-green-500',
                'xlsx': 'text-green-500',
                'ppt': 'text-orange-500',
                'pptx': 'text-orange-500',
                'jpg': 'text-purple-500',
                'jpeg': 'text-purple-500',
                'png': 'text-purple-500',
                'gif': 'text-purple-500',
                'zip': 'text-yellow-600',
                'rar': 'text-yellow-600',
                'txt': 'text-gray-500',
                'csv': 'text-green-600'
            };
            
            return colorMap[ext] || 'text-gray-400';
        },
        
        // Mobile specific methods
        showDocumentActions(document) {
            this.selectedDocument = document;
            this.showDocumentActionsModal = true;
        },
        
        // Render folder tree HTML
        renderFolderTree(folder, level = 0) {
            if (!folder) return '';
            
            let html = `<div class="folder-item" style="padding-left: ${level * 20}px;">`;
            
            // Create clickable folder button
            html += `<div class="flex items-center w-full p-2 hover:bg-gray-100 rounded text-left cursor-pointer" `;
            html += `@click="selectFolder($refs.folder_${folder.id ? folder.id : 'root'})" `;
            html += `x-ref="folder_${folder.id ? folder.id : 'root'}" `;
            html += `:data-folder='${JSON.stringify(folder).replace(/'/g, "&#39;")}'`;
            html += `>`;
            html += `<i class="fas fa-folder mr-2 text-yellow-500"></i>`;
            html += `<span>${folder.name}</span>`;
            
            // Show document count if available
            if (folder.documents && folder.documents.length > 0) {
                html += ` <span class="ml-auto text-xs text-gray-500">(${folder.documents.length})</span>`;
            }
            
            html += `</div>`;
            
            // Render children recursively
            if (folder.children && folder.children.length > 0) {
                html += '<div class="ml-4">';
                folder.children.forEach(child => {
                    html += this.renderFolderTree(child, level + 1);
                });
                html += '</div>';
            }
            
            html += '</div>';
            return html;
        },
        
        getAllFoldersFlat() {
            const folders = [];
            const addFolder = (folder, prefix = '') => {
                if (!folder || !folder.children) return;
                
                folder.children.forEach(child => {
                    const displayName = prefix ? `${prefix} / ${child.name}` : child.name;
                    folders.push({
                        id: child.id,
                        path: child.path,
                        name: child.name,
                        displayName: displayName
                    });
                    
                    if (child.children && child.children.length > 0) {
                        addFolder(child, displayName);
                    }
                });
            };
            
            if (this.folders) {
                addFolder(this.folders);
            }
            
            return folders;
        },
        
        navigateToFolderByPath(path) {
            if (!path) {
                this.selectFolder(this.folders);
                return;
            }
            
            const findFolder = (folder, targetPath) => {
                if (folder.path === targetPath) {
                    return folder;
                }
                
                if (folder.children) {
                    for (let child of folder.children) {
                        const found = findFolder(child, targetPath);
                        if (found) return found;
                    }
                }
                
                return null;
            };
            
            const targetFolder = findFolder(this.folders, path);
            if (targetFolder) {
                this.selectFolder(targetFolder);
            }
        },
        
        // Get sync status color
        getSyncStatusColor(status) {
            const colors = {
                'synced': 'text-green-600',
                'pending': 'text-yellow-600',
                'syncing': 'text-blue-600',
                'failed': 'text-red-600',
                'out_of_sync': 'text-orange-600'
            };
            
            return colors[status] || 'text-gray-600';
        },
        
        // Get sync status icon
        getSyncStatusIcon(status) {
            const icons = {
                'synced': 'fa-check-circle',
                'pending': 'fa-clock',
                'syncing': 'fa-sync fa-spin',
                'failed': 'fa-exclamation-circle',
                'out_of_sync': 'fa-exclamation-triangle'
            };
            
            return icons[status] || 'fa-question-circle';
        },
        
        // Render folder item recursively
        renderFolderItem(folder, level = 0) {
            if (!folder) return '';
            
            let html = `<div class="folder-item">`;
            html += `<button @click="selectFolder(${JSON.stringify(folder).replace(/"/g, '&quot;')})"`;
            html += ` class="flex items-center w-full p-2 hover:bg-gray-100 rounded ${this.currentPath === folder.path ? 'bg-blue-50' : ''}"`;
            html += ` style="padding-left: ${level * 1}rem;">`;
            html += `<i class="fas fa-folder mr-2 text-yellow-500"></i>`;
            html += `<span>${folder.name}</span>`;
            
            if (folder.documents && folder.documents.length > 0) {
                html += `<span class="ml-auto text-xs text-gray-500">(${folder.documents.length})</span>`;
            }
            
            html += `</button>`;
            
            // Render children recursively
            if (folder.children && folder.children.length > 0) {
                html += '<div class="ml-4">';
                folder.children.forEach(child => {
                    html += this.renderFolderItem(child, level + 1);
                });
                html += '</div>';
            }
            
            html += '</div>';
            return html;
        },
        
        // Get all folders as flat list for debugging
        getFlatFolderList() {
            const folders = [];
            
            const addFolder = (folder, level = 0) => {
                folders.push({
                    name: folder.name,
                    path: folder.path,
                    level: level,
                    hasChildren: folder.children && folder.children.length > 0,
                    childrenCount: folder.children ? folder.children.length : 0
                });
                
                if (folder.children && folder.children.length > 0) {
                    folder.children.forEach(child => {
                        addFolder(child, level + 1);
                    });
                }
            };
            
            if (this.folders) {
                addFolder(this.folders);
            }
            
            return folders;
        },
        
        // Force refresh folder structure
        async forceRefresh() {
            console.log('Force refreshing folder structure...');
            this.folders = null;
            await this.loadFolderStructure();
            
            // Log flat folder list for debugging
            const flatList = this.getFlatFolderList();
            console.log('Flat folder list:', flatList);
            
            // Check if "tes" folder exists
            const tesFolder = flatList.find(f => f.name === 'tes');
            if (tesFolder) {
                console.log('TES FOLDER FOUND:', tesFolder);
            } else {
                console.log('TES FOLDER NOT FOUND IN FLAT LIST');
            }
        }
    }));
});