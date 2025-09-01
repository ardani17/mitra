export default {
  name: 'FolderTreeNode',
  template: `
    <div class="folder-node-wrapper">
      <div class="folder-item p-2 hover:bg-gray-100 rounded cursor-pointer flex items-center transition-colors duration-150"
           :style="{ paddingLeft: (level + 1) * 20 + 'px' }"
           :class="{ 'bg-blue-100': isSelected }"
           @click="handleClick">
        
        <!-- Expand/Collapse Toggle -->
        <button v-if="folder.hasChildren"
                @click.stop="toggleExpand"
                class="mr-1 p-0.5 hover:bg-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
                :aria-expanded="isExpanded"
                :aria-label="isExpanded ? 'Collapse folder' : 'Expand folder'">
          <svg v-if="isLoading" class="animate-spin h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <svg v-else class="h-4 w-4 text-gray-400 transition-transform duration-200"
               :class="{ 'rotate-90': isExpanded }"
               fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
          </svg>
        </button>
        <span v-else class="w-5 mr-1"></span>
        
        <!-- Folder Icon -->
        <svg class="h-5 w-5 text-yellow-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path v-if="isExpanded && folder.hasChildren" 
                d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
          <path v-else 
                d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-5.586a1 1 0 01-.707-.293l-1.121-1.121A2 2 0 007.172 2H4z"></path>
        </svg>
        
        <!-- Folder Name -->
        <span class="text-sm select-none flex-1">{{ folder.name }}</span>
        
        <!-- Child Count Badge -->
        <span v-if="folder.childCount > 0 && !isExpanded && !isLoading" 
              class="ml-2 px-1.5 py-0.5 text-xs text-gray-500 bg-gray-200 rounded-full">
          {{ folder.childCount }}
        </span>
        
        <!-- Loading Indicator for Children -->
        <span v-if="isLoading" class="ml-2 text-xs text-gray-500">
          Loading...
        </span>
      </div>
      
      <!-- Children (Recursive) -->
      <transition name="folder-expand">
        <div v-if="isExpanded && folder.children" class="folder-children">
          <folder-tree-node
            v-for="child in sortedChildren"
            :key="child.path"
            :folder="child"
            :level="level + 1"
            :selected-path="selectedPath"
            :expanded-nodes="expandedNodes"
            :loading-nodes="loadingNodes"
            :exclude-path="excludePath"
            @select="$emit('select', $event)"
            @expand="$emit('expand', $event)"
          />
          
          <!-- Empty children state -->
          <div v-if="folder.children.length === 0" 
               class="text-xs text-gray-400 italic"
               :style="{ paddingLeft: (level + 2) * 20 + 'px' }">
            No subfolders
          </div>
        </div>
      </transition>
    </div>
  `,
  props: {
    folder: {
      type: Object,
      required: true
    },
    level: {
      type: Number,
      default: 0
    },
    selectedPath: {
      type: String,
      default: ''
    },
    expandedNodes: {
      type: Set,
      default: () => new Set()
    },
    loadingNodes: {
      type: Set,
      default: () => new Set()
    },
    excludePath: {
      type: String,
      default: ''
    }
  },
  computed: {
    isSelected() {
      return this.selectedPath === this.folder.path;
    },
    
    isExpanded() {
      return this.expandedNodes.has(this.folder.path);
    },
    
    isLoading() {
      return this.loadingNodes.has(this.folder.path);
    },
    
    sortedChildren() {
      if (!this.folder.children) return [];
      
      // Sort children alphabetically, case-insensitive
      return [...this.folder.children].sort((a, b) => {
        return a.name.toLowerCase().localeCompare(b.name.toLowerCase());
      });
    },
    
    isExcluded() {
      // Check if this folder or its parents are excluded (for move operations)
      if (!this.excludePath) return false;
      
      // Can't move a folder into itself or its children
      return this.excludePath === this.folder.path || 
             this.excludePath.startsWith(this.folder.path + '/');
    }
  },
  methods: {
    handleClick() {
      if (!this.isExcluded) {
        this.$emit('select', this.folder.path);
      }
    },
    
    toggleExpand() {
      if (!this.isLoading && this.folder.hasChildren) {
        this.$emit('expand', this.folder.path);
      }
    }
  }
};