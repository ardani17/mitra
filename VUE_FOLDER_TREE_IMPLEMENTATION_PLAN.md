# Vue.js Folder Tree Implementation Plan

## Problem Analysis
The current Alpine.js implementation has these limitations:
1. **Depth Limitation**: The `buildFolderTree` method has a hardcoded `maxDepth` of 5 levels
2. **No Dynamic Loading**: All folders are loaded at once, causing performance issues
3. **Limited Interactivity**: Subfolders cannot be expanded beyond the initial load
4. **No Lazy Loading**: The entire tree structure is fetched even if not needed

## Solution Architecture

### 1. Vue.js Component Structure

```
FolderTreeExplorer (Main Component)
├── FolderTreeNode (Recursive Component)
│   ├── Folder Icon
│   ├── Folder Name
│   ├── Expand/Collapse Toggle
│   └── Children (recursive FolderTreeNode)
└── SelectedPathDisplay
```

### 2. Component Features

#### FolderTreeExplorer Component
- **Props**:
  - `modalType`: 'copy' | 'move'
  - `excludePath`: string (for move operations)
  - `onSelect`: callback function
  
- **Data**:
  - `selectedPath`: Currently selected folder path
  - `expandedNodes`: Set of expanded folder paths
  - `loadingNodes`: Set of currently loading folder paths
  - `folderCache`: Map of loaded folder structures

- **Methods**:
  - `loadRootFolders()`: Load initial root level folders
  - `selectFolder(path)`: Handle folder selection
  - `handleNodeExpand(path)`: Load children when expanding

#### FolderTreeNode Component (Recursive)
- **Props**:
  - `folder`: Folder object
  - `level`: Current depth level
  - `selectedPath`: Currently selected path
  - `expandedNodes`: Set of expanded paths
  - `loadingNodes`: Set of loading paths
  
- **Methods**:
  - `toggleExpand()`: Expand/collapse folder
  - `loadChildren()`: Fetch children from API
  - `isExpanded()`: Check if node is expanded
  - `isSelected()`: Check if node is selected
  - `hasChildren()`: Check if folder has children

### 3. API Endpoints

#### New Endpoint: `/telegram-bot/folder-tree-lazy`
```php
// GET request with parameters:
// - parent: Parent folder path (empty for root)
// - exclude: Path to exclude (for move operations)

Response:
{
  "success": true,
  "folders": [
    {
      "name": "folder1",
      "path": "folder1",
      "hasChildren": true,
      "childCount": 5
    },
    {
      "name": "folder2", 
      "path": "folder2",
      "hasChildren": false,
      "childCount": 0
    }
  ]
}
```

### 4. Vue Component Implementation

#### Main Component Template
```vue
<template>
  <div class="folder-tree-explorer">
    <!-- Selected Path Display -->
    <div class="selected-path-display mb-3 p-2 rounded-md"
         :class="modalType === 'copy' ? 'bg-blue-50 border-blue-200' : 'bg-yellow-50 border-yellow-200'">
      <span class="text-sm text-gray-600">Selected: </span>
      <span class="text-sm font-medium"
            :class="modalType === 'copy' ? 'text-blue-900' : 'text-yellow-900'">
        {{ selectedPath || '/' }}
      </span>
    </div>
    
    <!-- Folder Tree Container -->
    <div class="folder-tree-container border border-gray-300 rounded-md p-3 max-h-96 overflow-y-auto bg-gray-50">
      <!-- Root Folder -->
      <div class="folder-item p-2 hover:bg-gray-100 rounded cursor-pointer flex items-center"
           :class="{ 'bg-blue-100': selectedPath === '' }"
           @click="selectFolder('')">
        <svg class="h-5 w-5 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
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
      <div v-if="!isLoadingRoot && rootFolders.length === 0" class="text-center py-4">
        <p class="text-sm text-gray-500">No folders available</p>
      </div>
    </div>
  </div>
</template>
```

#### Recursive Node Component Template
```vue
<template>
  <div class="folder-node-wrapper">
    <div class="folder-item p-2 hover:bg-gray-100 rounded cursor-pointer flex items-center"
         :style="{ paddingLeft: (level + 1) * 20 + 'px' }"
         :class="{ 'bg-blue-100': isSelected }"
         @click="selectFolder">
      
      <!-- Expand/Collapse Toggle -->
      <button v-if="folder.hasChildren"
              @click.stop="toggleExpand"
              class="mr-1 focus:outline-none">
        <svg v-if="isLoading" class="animate-spin h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <svg v-else class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                :d="isExpanded ? 'M19 9l-7 7-7-7' : 'M9 5l7 7-7 7'"></path>
        </svg>
      </button>
      <span v-else class="w-5 mr-1"></span>
      
      <!-- Folder Icon -->
      <svg class="h-5 w-5 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
        <path v-if="isExpanded" d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
        <path v-else d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-5.586a1 1 0 01-.707-.293l-1.121-1.121A2 2 0 007.172 2H4z"></path>
      </svg>
      
      <!-- Folder Name -->
      <span class="text-sm">{{ folder.name }}</span>
      
      <!-- Child Count Badge -->
      <span v-if="folder.childCount > 0 && !isExpanded" 
            class="ml-2 text-xs text-gray-500">
        ({{ folder.childCount }})
      </span>
    </div>
    
    <!-- Children (Recursive) -->
    <div v-if="isExpanded && folder.children" class="folder-children">
      <folder-tree-node
        v-for="child in folder.children"
        :key="child.path"
        :folder="child"
        :level="level + 1"
        :selected-path="selectedPath"
        :expanded-nodes="expandedNodes"
        :loading-nodes="loadingNodes"
        @select="$emit('select', $event)"
        @expand="$emit('expand', $event)"
      />
    </div>
  </div>
</template>
```

### 5. Integration with Existing Blade Template

The Vue component will be integrated into the existing modals:
1. Replace the Alpine.js `FolderTreeSelector` class
2. Mount Vue components in copy and move modals
3. Maintain the same modal structure and styling
4. Use the same CSRF token and API endpoints

### 6. Styling Consistency

- Use existing Tailwind CSS classes
- Match the current color scheme:
  - Copy modal: Blue theme (bg-blue-50, border-blue-200, text-blue-900)
  - Move modal: Yellow theme (bg-yellow-50, border-yellow-200, text-yellow-900)
  - Hover states: bg-gray-100
  - Selected state: bg-blue-100
- Maintain the same spacing and sizing

### 7. Performance Optimizations

1. **Lazy Loading**: Only load folders when expanded
2. **Caching**: Cache loaded folder structures
3. **Debouncing**: Debounce rapid expand/collapse actions
4. **Virtual Scrolling**: For very large folder lists (future enhancement)
5. **Loading States**: Show loading indicators during API calls

### 8. Error Handling

- Network error recovery
- Retry mechanism for failed loads
- User-friendly error messages
- Fallback to empty state on errors

### 9. Testing Strategy

1. Test folder expansion at multiple levels (10+ levels deep)
2. Test with large folder structures (1000+ folders)
3. Test move operation exclusion logic
4. Test concurrent expand operations
5. Test error scenarios (network failures, permissions)

## Implementation Steps

1. Create Vue component files
2. Add lazy-loading API endpoint to controller
3. Update Blade template to include Vue
4. Test with sample data
5. Optimize performance
6. Add error handling
7. Final testing and refinement