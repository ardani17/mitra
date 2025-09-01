# Folder Tree Selector Implementation Plan

## Overview
Enhance the Copy and Move modals in the File Explorer to include an interactive folder tree selector, allowing users to visually select destination folders instead of manually typing paths.

## Requirements
1. **Visual Folder Tree**: Display a hierarchical tree structure of all folders
2. **Interactive Selection**: Click to select destination folder
3. **Expandable/Collapsible**: Folders can be expanded to show subfolders
4. **Visual Feedback**: Highlight selected folder
5. **Path Display**: Show the selected path clearly
6. **Create New Folder**: Option to create new folders within the tree

## Technical Architecture

### Backend Components

#### 1. API Endpoint for Folder Tree
```php
// Route: GET /telegram-bot/folder-tree
Route::get('/folder-tree', [TelegramBotController::class, 'getFolderTree'])
    ->name('telegram-bot.folder-tree');
```

**Response Format:**
```json
{
  "folders": [
    {
      "name": "proyek-a",
      "path": "proyek-a",
      "hasChildren": true,
      "children": [
        {
          "name": "dokumen",
          "path": "proyek-a/dokumen",
          "hasChildren": false,
          "children": []
        }
      ]
    }
  ]
}
```

### Frontend Components

#### 1. Folder Tree Component
- **Technology**: Vanilla JavaScript with Alpine.js for reactivity
- **Features**:
  - Recursive rendering of folder structure
  - Click to expand/collapse folders
  - Click to select destination
  - Visual indicators (icons, colors)

#### 2. Enhanced Modal Structure
```html
<!-- Copy/Move Modal with Folder Tree -->
<div class="modal">
  <!-- Current Path Display -->
  <div class="selected-path-display">
    Selected: /proyek-a/dokumen
  </div>
  
  <!-- Folder Tree Container -->
  <div class="folder-tree-container">
    <!-- Tree will be rendered here -->
  </div>
  
  <!-- Action Buttons -->
  <div class="modal-actions">
    <button>Cancel</button>
    <button>Copy/Move</button>
  </div>
</div>
```

## Implementation Steps

### Step 1: Backend - Add Folder Tree Endpoint
1. Add method `getFolderTree()` to TelegramBotController
2. Implement recursive folder scanning
3. Return JSON structure with folder hierarchy
4. Include security checks for path traversal

### Step 2: Frontend - Create Folder Tree Component
1. Build JavaScript class for folder tree management
2. Implement expand/collapse functionality
3. Add selection handling
4. Create visual feedback for selected folder

### Step 3: Update Copy Modal
1. Replace text input with folder tree selector
2. Add selected path display
3. Update copy function to use selected path
4. Add loading state while fetching folder tree

### Step 4: Update Move Modal
1. Apply same changes as Copy modal
2. Ensure source folder is not selectable as destination
3. Add validation to prevent moving folder into itself

### Step 5: Add Create Folder Feature
1. Add "New Folder" button in tree
2. Implement inline folder creation
3. Refresh tree after creation
4. Auto-select newly created folder

## UI/UX Design

### Visual Elements
```
📁 Projects Root
├── 📁 proyek-a
│   ├── 📁 dokumen
│   ├── 📁 gambar
│   └── 📁 video
├── 📁 proyek-b
│   └── 📁 files
└── 📁 proyek-c
    └── [empty]
```

### Interaction Flow
1. User clicks Copy/Move button
2. Modal opens with folder tree
3. User expands folders to navigate
4. User clicks to select destination
5. Selected path is highlighted
6. User confirms action
7. File/folder is copied/moved

## Code Structure

### JavaScript Folder Tree Class
```javascript
class FolderTreeSelector {
  constructor(containerId, options = {}) {
    this.container = document.getElementById(containerId);
    this.selectedPath = '';
    this.onSelect = options.onSelect || function() {};
    this.excludePath = options.excludePath || null;
  }
  
  async loadTree() {
    // Fetch folder structure from API
  }
  
  renderTree(folders, level = 0) {
    // Recursive rendering
  }
  
  selectFolder(path) {
    // Handle folder selection
  }
  
  expandFolder(path) {
    // Toggle folder expansion
  }
}
```

### CSS Styling
```css
.folder-tree-container {
  max-height: 400px;
  overflow-y: auto;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  padding: 0.5rem;
}

.folder-item {
  padding: 0.25rem 0.5rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.folder-item:hover {
  background-color: #f3f4f6;
}

.folder-item.selected {
  background-color: #dbeafe;
  font-weight: 600;
}

.folder-children {
  margin-left: 1.5rem;
}
```

## Security Considerations
1. **Path Validation**: Ensure all paths stay within `/storage/app/proyek`
2. **Access Control**: Verify user has permission to access folders
3. **Input Sanitization**: Clean folder names before creation
4. **CSRF Protection**: Include CSRF token in all requests

## Testing Plan
1. Test folder tree loading and rendering
2. Test selection of various folder depths
3. Test copy/move operations with tree selector
4. Test edge cases (empty folders, deep nesting)
5. Test security (path traversal attempts)
6. Test performance with many folders

## Benefits
1. **User-Friendly**: Visual selection is more intuitive
2. **Error Reduction**: Eliminates typos in path entry
3. **Efficiency**: Faster folder selection
4. **Discovery**: Users can see available folders
5. **Professional**: Modern UI pattern

## Timeline
- Backend API endpoint: 30 minutes
- Frontend folder tree component: 1 hour
- Modal integration: 45 minutes
- Testing and refinement: 45 minutes
- **Total estimated time**: 3 hours