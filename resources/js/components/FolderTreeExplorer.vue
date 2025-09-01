<template>
  <div class="folder-tree-explorer">
    <!-- Selected Path Display -->
    <div class="selected-path-display mb-3 p-2 rounded-md border"
         :class="modalType === 'copy' ? 'bg-blue-50 border-blue-200' : 'bg-yellow-50 border-yellow-200'">
      <span class="text-sm text-gray-600">Selected: </span>
      <span class="text-sm font-medium"
            :class="modalType === 'copy' ? 'text-blue-900' : 'text-yellow-900'">
        {{ displayPath }}
      </span>
    </div>
    
    <!-- Folder Tree Container -->
    <div class="folder-tree-container border border-gray-300 rounded-md p-3 max-h-96 overflow-y-auto bg-gray-50">
      <!-- Root Folder -->
      <div class="folder-item p-2 hover:bg-gray-100 rounded cursor-pointer flex items-center transition-colors duration-150"
           :class="{ 'bg-blue-100': selectedPath === '' }"
           @click="selectFolder('')">
        <svg class="h-5 w-5 text-yellow-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
        </svg>
        <span class="text-sm font-medium">/ (Root)</span>
      </div>
      
      <!-- Folder Tree Nodes -->
      <folder-tree-node
        v-for="folder in rootFolders"
        :key="folder.path"
        :folder="folder"
        :level="0"
        :selected-path="selectedPath"
        :expanded-nodes="expandedNodes"
        :loading-nodes="loadingNodes"
        :exclude-path="excludePath"
        @select="selectFolder"
        @expand="handleNodeExpand"
      />
      
      <!-- Loading State -->
      <div v-if="isLoadingRoot" class="text-center py-4">
        <svg class="animate-spin h-8 w-8 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="mt-2 text-sm text-gray-500">Loading folders...</p>
      </div>
      
      <!-- Empty State -->
      <div v-if="!isLoadingRoot && rootFolders.length === 0 && !hasError" class="text-center py-4">
        <svg class="h-12 w-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
        </svg>
        <p class="text-sm text-gray-500">No folders available</p>
      </div>
      
      <!-- Error State -->
      <div v-if="hasError" class="text-center py-4">
        <svg class="h-12 w-12 mx-auto text-red-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <p class="text-sm text-red-600 mb-2">Failed to load folders</p>
        <button @click="loadRootFolders" 
                class="text-sm text-blue-600 hover:text-blue-800 underline">
          Try again
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import FolderTreeNode from './FolderTreeNode.vue';

export default {
  name: 'FolderTreeExplorer',
  components: {
    FolderTreeNode
  },
  props: {
    modalType: {
      type: String,
      default: 'copy',
      validator: value => ['copy', 'move'].includes(value)
    },
    excludePath: {
      type: String,
      default: ''
    },
    initialPath: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      selectedPath: '',
      rootFolders: [],
      expandedNodes: new Set(),
      loadingNodes: new Set(),
      folderCache: new Map(),
      isLoadingRoot: false,
      hasError: false
    };
  },
  computed: {
    displayPath() {
      return this.selectedPath ? '/' + this.selectedPath : '/';
    }
  },
  mounted() {
    this.selectedPath = this.initialPath;
    this.loadRootFolders();
  },
  methods: {
    async loadRootFolders() {
      this.isLoadingRoot = true;
      this.hasError = false;
      
      try {
        // Get CSRF token from meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        // Use absolute URL to ensure proper routing
        const baseUrl = window.location.origin;
        const response = await fetch(`${baseUrl}/telegram-bot/folder-tree-lazy?` + new URLSearchParams({
          parent: '',
          exclude: this.excludePath
        }), {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken || ''
          },
          credentials: 'same-origin' // Include cookies for session
        });
        
        if (!response.ok) {
          throw new Error('Failed to load folders');
        }
        
        const data = await response.json();
        
        if (data.success) {
          this.rootFolders = data.folders;
          // Cache the root folders
          this.folderCache.set('', data.folders);
        } else {
          throw new Error(data.message || 'Failed to load folders');
        }
      } catch (error) {
        console.error('Error loading root folders:', error);
        this.hasError = true;
        this.rootFolders = [];
      } finally {
        this.isLoadingRoot = false;
      }
    },
    
    selectFolder(path) {
      this.selectedPath = path;
      this.$emit('select', path);
    },
    
    async handleNodeExpand(folderPath) {
      // Check if already loading
      if (this.loadingNodes.has(folderPath)) {
        return;
      }
      
      // Toggle expansion
      if (this.expandedNodes.has(folderPath)) {
        this.expandedNodes.delete(folderPath);
        return;
      }
      
      // Check cache first
      if (this.folderCache.has(folderPath)) {
        this.expandedNodes.add(folderPath);
        const cachedFolder = this.findFolderByPath(folderPath);
        if (cachedFolder && !cachedFolder.children) {
          cachedFolder.children = this.folderCache.get(folderPath);
        }
        return;
      }
      
      // Load children from server with retry logic
      this.loadingNodes.add(folderPath);
      
      let retryCount = 0;
      const maxRetries = 2;
      
      while (retryCount <= maxRetries) {
        try {
          // Get CSRF token from meta tag
          const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
          
          // Use absolute URL to ensure proper routing
          const baseUrl = window.location.origin;
          const url = `${baseUrl}/telegram-bot/folder-tree-lazy?` + new URLSearchParams({
            parent: folderPath,
            exclude: this.excludePath
          });
          
          const response = await fetch(url, {
            method: 'GET',
            headers: {
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': csrfToken || ''
            },
            credentials: 'same-origin' // Include cookies for session
          });
          
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          
          const data = await response.json();
          
          if (data.success) {
            // Cache the results
            this.folderCache.set(folderPath, data.folders);
            
            // Find and update the folder with children
            const folder = this.findFolderByPath(folderPath);
            
            if (folder) {
              // In Vue 3, direct assignment is reactive
              folder.children = data.folders;
            }
            
            // Expand the node
            this.expandedNodes.add(folderPath);
            break; // Success, exit the retry loop
          } else {
            throw new Error(data.message || 'Failed to load folder contents');
          }
        } catch (error) {
          console.error(`Error loading folder contents (attempt ${retryCount + 1}):`, error);
          
          if (retryCount === maxRetries) {
            // Final attempt failed, show error
            alert('Failed to load folder contents after multiple attempts. Please try again.');
          } else {
            // Wait a bit before retrying
            await new Promise(resolve => setTimeout(resolve, 500));
            retryCount++;
          }
        }
      }
      
      this.loadingNodes.delete(folderPath);
    },
    
    findFolderByPath(path, folders = null) {
      if (!folders) {
        folders = this.rootFolders;
      }
      
      for (const folder of folders) {
        if (folder.path === path) {
          return folder;
        }
        if (folder.children) {
          const found = this.findFolderByPath(path, folder.children);
          if (found) {
            return found;
          }
        }
      }
      
      return null;
    },
    
    reset() {
      this.selectedPath = '';
      this.expandedNodes.clear();
      this.loadingNodes.clear();
      this.folderCache.clear();
      this.loadRootFolders();
    }
  }
};
</script>

<style scoped>
.folder-tree-container {
  min-height: 200px;
}

.folder-item {
  user-select: none;
}

/* Smooth transitions */
.folder-item {
  transition: background-color 0.15s ease-in-out;
}

/* Custom scrollbar for the tree container */
.folder-tree-container::-webkit-scrollbar {
  width: 8px;
}

.folder-tree-container::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.folder-tree-container::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

.folder-tree-container::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>