# 🎨 Telegram Bot UI Design Guide - Konsistensi & Responsive

## 📱 Mobile-First Design Principles

### Responsive Breakpoints
```scss
// Konsisten dengan Bootstrap breakpoints yang sudah digunakan
$breakpoints: (
  'xs': 0,      // Mobile portrait
  'sm': 576px,  // Mobile landscape
  'md': 768px,  // Tablet
  'lg': 992px,  // Desktop
  'xl': 1200px, // Large desktop
  'xxl': 1400px // Extra large
);
```

## 🎨 Existing Theme Analysis

### Current Color Palette (dari existing project)
```css
:root {
  /* Primary Colors */
  --primary: #4F46E5;        /* Indigo - main brand color */
  --primary-hover: #4338CA;  /* Darker indigo for hover */
  
  /* Secondary Colors */
  --secondary: #6B7280;      /* Gray */
  --success: #10B981;        /* Green */
  --danger: #EF4444;         /* Red */
  --warning: #F59E0B;        /* Amber */
  --info: #3B82F6;          /* Blue */
  
  /* Background Colors */
  --bg-primary: #FFFFFF;     /* White */
  --bg-secondary: #F9FAFB;   /* Light gray */
  --bg-tertiary: #F3F4F6;    /* Lighter gray */
  
  /* Text Colors */
  --text-primary: #111827;   /* Dark gray */
  --text-secondary: #6B7280; /* Medium gray */
  --text-muted: #9CA3AF;     /* Light gray */
  
  /* Border Colors */
  --border-light: #E5E7EB;   /* Light border */
  --border-medium: #D1D5DB;  /* Medium border */
}

/* Dark Mode Support */
[data-theme="dark"] {
  --bg-primary: #1F2937;
  --bg-secondary: #111827;
  --text-primary: #F9FAFB;
  --text-secondary: #D1D5DB;
}
```

## 📐 Component Design Patterns

### 1. Bot Configuration - Responsive Layout

```vue
<template>
  <!-- Mobile & Desktop Responsive Container -->
  <div class="container-fluid px-3 px-md-4">
    
    <!-- Page Header - Mobile Optimized -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
      <h2 class="h4 mb-2 mb-md-0">Bot Configuration</h2>
      <button class="btn btn-primary btn-sm">
        <i class="fas fa-save me-2"></i>
        <span class="d-none d-sm-inline">Save Configuration</span>
        <span class="d-sm-none">Save</span>
      </button>
    </div>
    
    <!-- Configuration Cards - Stack on Mobile -->
    <div class="row g-3">
      
      <!-- Server Settings Card -->
      <div class="col-12 col-lg-6">
        <div class="card shadow-sm">
          <div class="card-header bg-white border-bottom">
            <h5 class="card-title mb-0 fs-6">
              <i class="fas fa-server text-primary me-2"></i>
              Server Settings
            </h5>
          </div>
          <div class="card-body">
            <!-- Mobile-Friendly Form -->
            <div class="mb-3">
              <label class="form-label small text-muted">Bot Token</label>
              <div class="input-group">
                <input 
                  type="password" 
                  class="form-control form-control-sm"
                  v-model="config.bot_token"
                >
                <button 
                  class="btn btn-outline-secondary btn-sm"
                  @click="toggleTokenVisibility"
                >
                  <i :class="tokenVisible ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                </button>
              </div>
            </div>
            
            <!-- Responsive Grid for Server Info -->
            <div class="row g-2">
              <div class="col-6">
                <label class="form-label small text-muted">Host</label>
                <input 
                  type="text" 
                  class="form-control form-control-sm"
                  v-model="config.server_host"
                  readonly
                >
              </div>
              <div class="col-6">
                <label class="form-label small text-muted">Port</label>
                <input 
                  type="number" 
                  class="form-control form-control-sm"
                  v-model="config.server_port"
                  readonly
                >
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Path Configuration Card -->
      <div class="col-12 col-lg-6">
        <div class="card shadow-sm">
          <div class="card-header bg-white border-bottom">
            <h5 class="card-title mb-0 fs-6">
              <i class="fas fa-folder text-warning me-2"></i>
              Path Configuration
            </h5>
          </div>
          <div class="card-body">
            <!-- Collapsible on Mobile -->
            <div class="accordion accordion-flush" id="pathAccordion">
              <div class="accordion-item border-0">
                <h6 class="accordion-header">
                  <button 
                    class="accordion-button collapsed p-0 bg-white text-dark small"
                    type="button" 
                    data-bs-toggle="collapse" 
                    data-bs-target="#botApiPath"
                  >
                    Bot API Paths
                  </button>
                </h6>
                <div id="botApiPath" class="accordion-collapse collapse show">
                  <div class="pt-2">
                    <input 
                      type="text" 
                      class="form-control form-control-sm mb-2"
                      v-model="config.bot_api_base_path"
                      placeholder="Base path"
                    >
                    <small class="text-muted d-block mb-3">
                      Where telegram-bot-api stores files
                    </small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- User Whitelist - Full Width on Mobile -->
    <div class="card shadow-sm mt-3">
      <div class="card-header bg-white border-bottom">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0 fs-6">
            <i class="fas fa-users text-success me-2"></i>
            Allowed Users
          </h5>
          <button 
            class="btn btn-success btn-sm"
            @click="showAddUserModal"
          >
            <i class="fas fa-plus"></i>
            <span class="d-none d-sm-inline ms-1">Add User</span>
          </button>
        </div>
      </div>
      <div class="card-body p-0">
        <!-- Mobile-Optimized Table -->
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead class="bg-light">
              <tr>
                <th class="border-0 ps-3">Username</th>
                <th class="border-0 d-none d-sm-table-cell">Telegram ID</th>
                <th class="border-0 d-none d-md-table-cell">Added</th>
                <th class="border-0 text-end pe-3">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in allowedUsers" :key="user.id">
                <td class="ps-3">
                  <div>
                    <span class="fw-medium">@{{ user.username }}</span>
                    <div class="d-sm-none small text-muted">
                      ID: {{ user.telegram_id }}
                    </div>
                  </div>
                </td>
                <td class="d-none d-sm-table-cell text-muted">
                  {{ user.telegram_id }}
                </td>
                <td class="d-none d-md-table-cell text-muted small">
                  {{ formatDate(user.created_at) }}
                </td>
                <td class="text-end pe-3">
                  <button 
                    class="btn btn-danger btn-sm"
                    @click="removeUser(user.id)"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
```

### 2. Bot Activity Dashboard - Mobile Responsive

```vue
<template>
  <!-- Mobile-First Activity Dashboard -->
  <div class="container-fluid px-3 px-md-4">
    
    <!-- Stats Cards - Responsive Grid -->
    <div class="row g-3 mb-4">
      <!-- Today's Activity -->
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0">
                <div class="avatar-sm bg-primary bg-opacity-10 rounded">
                  <i class="fas fa-chart-line text-primary"></i>
                </div>
              </div>
              <div class="flex-grow-1 ms-3">
                <p class="text-muted small mb-1">Today</p>
                <h5 class="mb-0">{{ stats.today }}</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Files Uploaded -->
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0">
                <div class="avatar-sm bg-success bg-opacity-10 rounded">
                  <i class="fas fa-file-upload text-success"></i>
                </div>
              </div>
              <div class="flex-grow-1 ms-3">
                <p class="text-muted small mb-1">Files</p>
                <h5 class="mb-0">{{ stats.files }}</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Active Users -->
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0">
                <div class="avatar-sm bg-info bg-opacity-10 rounded">
                  <i class="fas fa-users text-info"></i>
                </div>
              </div>
              <div class="flex-grow-1 ms-3">
                <p class="text-muted small mb-1">Users</p>
                <h5 class="mb-0">{{ stats.users }}</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Storage Used -->
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0">
                <div class="avatar-sm bg-warning bg-opacity-10 rounded">
                  <i class="fas fa-database text-warning"></i>
                </div>
              </div>
              <div class="flex-grow-1 ms-3">
                <p class="text-muted small mb-1">Storage</p>
                <h5 class="mb-0">{{ stats.storage }}</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Activity Timeline - Mobile Optimized -->
    <div class="card shadow-sm">
      <div class="card-header bg-white border-bottom">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center">
          <h5 class="card-title mb-2 mb-sm-0 fs-6">
            <i class="fas fa-history text-primary me-2"></i>
            Recent Activity
          </h5>
          <!-- Mobile Filter Dropdown -->
          <div class="dropdown">
            <button 
              class="btn btn-sm btn-outline-secondary dropdown-toggle"
              type="button" 
              data-bs-toggle="dropdown"
            >
              <i class="fas fa-filter me-1"></i>
              Filter
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" @click="filter('all')">All Activity</a></li>
              <li><a class="dropdown-item" @click="filter('uploads')">Uploads Only</a></li>
              <li><a class="dropdown-item" @click="filter('commands')">Commands Only</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" @click="filter('today')">Today</a></li>
              <li><a class="dropdown-item" @click="filter('week')">This Week</a></li>
            </ul>
          </div>
        </div>
      </div>
      <div class="card-body p-0">
        <!-- Mobile-Friendly Activity List -->
        <div class="list-group list-group-flush">
          <div 
            v-for="activity in activities" 
            :key="activity.id"
            class="list-group-item px-3 py-3"
          >
            <div class="d-flex">
              <!-- Activity Icon -->
              <div class="flex-shrink-0">
                <div 
                  class="avatar-sm rounded-circle"
                  :class="getActivityIconClass(activity.type)"
                >
                  <i :class="getActivityIcon(activity.type)"></i>
                </div>
              </div>
              
              <!-- Activity Details -->
              <div class="flex-grow-1 ms-3">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <h6 class="mb-1 small">
                      <span class="fw-medium">@{{ activity.username }}</span>
                      <span class="text-muted ms-1">{{ activity.action }}</span>
                    </h6>
                    <p class="mb-1 small text-muted">
                      {{ activity.details }}
                    </p>
                    <!-- Mobile: Show time below -->
                    <small class="text-muted d-sm-none">
                      <i class="far fa-clock me-1"></i>
                      {{ formatTime(activity.created_at) }}
                    </small>
                  </div>
                  <!-- Desktop: Show time on right -->
                  <small class="text-muted d-none d-sm-block">
                    {{ formatTime(activity.created_at) }}
                  </small>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Load More Button -->
        <div class="p-3 text-center border-top">
          <button 
            class="btn btn-sm btn-outline-primary"
            @click="loadMore"
            v-if="hasMore"
          >
            <i class="fas fa-arrow-down me-1"></i>
            Load More
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
```

### 3. File Explorer Integration - Mobile Touch Optimized

```vue
<template>
  <!-- Mobile-Optimized File Explorer -->
  <div class="file-explorer-mobile">
    <!-- Sticky Header on Mobile -->
    <div class="sticky-top bg-white border-bottom">
      <div class="px-3 py-2">
        <!-- Breadcrumb - Scrollable on Mobile -->
        <div class="breadcrumb-scroll">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 flex-nowrap">
              <li class="breadcrumb-item">
                <a @click="navigateToRoot">
                  <i class="fas fa-home"></i>
                </a>
              </li>
              <li 
                v-for="(crumb, index) in breadcrumbs" 
                :key="index"
                class="breadcrumb-item"
                :class="{ active: index === breadcrumbs.length - 1 }"
              >
                <a 
                  v-if="index < breadcrumbs.length - 1"
                  @click="navigateTo(crumb.path)"
                >
                  {{ crumb.name }}
                </a>
                <span v-else>{{ crumb.name }}</span>
              </li>
            </ol>
          </nav>
        </div>
        
        <!-- Mobile Action Bar -->
        <div class="d-flex justify-content-between align-items-center mt-2">
          <div class="btn-group btn-group-sm">
            <button 
              class="btn btn-outline-secondary"
              @click="toggleView"
            >
              <i :class="viewMode === 'grid' ? 'fas fa-list' : 'fas fa-th'"></i>
            </button>
            <button 
              class="btn btn-outline-secondary"
              @click="showUploadModal"
            >
              <i class="fas fa-upload"></i>
            </button>
          </div>
          
          <!-- Sort Dropdown -->
          <div class="dropdown">
            <button 
              class="btn btn-sm btn-outline-secondary dropdown-toggle"
              data-bs-toggle="dropdown"
            >
              <i class="fas fa-sort"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" @click="sortBy('name')">Name</a></li>
              <li><a class="dropdown-item" @click="sortBy('size')">Size</a></li>
              <li><a class="dropdown-item" @click="sortBy('date')">Date</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    
    <!-- File List - Touch Optimized -->
    <div class="file-list p-3">
      <!-- Grid View for Mobile -->
      <div v-if="viewMode === 'grid'" class="row g-2">
        <div 
          v-for="item in items" 
          :key="item.id"
          class="col-6 col-sm-4 col-md-3"
        >
          <div 
            class="file-item-card p-3 text-center"
            @click="handleItemClick(item)"
            @touchstart="handleTouchStart(item)"
            @touchend="handleTouchEnd(item)"
          >
            <i 
              :class="getFileIcon(item)"
              class="fa-2x mb-2"
              :style="{ color: getFileColor(item) }"
            ></i>
            <p class="mb-0 small text-truncate">{{ item.name }}</p>
            <small class="text-muted">{{ formatSize(item.size) }}</small>
            
            <!-- Telegram Badge if from Bot -->
            <span 
              v-if="item.source === 'telegram'"
              class="badge bg-info position-absolute top-0 end-0 m-1"
            >
              <i class="fab fa-telegram"></i>
            </span>
          </div>
        </div>
      </div>
      
      <!-- List View for Mobile -->
      <div v-else class="list-group">
        <div 
          v-for="item in items" 
          :key="item.id"
          class="list-group-item list-group-item-action"
          @click="handleItemClick(item)"
        >
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <i 
                :class="getFileIcon(item)"
                class="fa-lg"
                :style="{ color: getFileColor(item) }"
              ></i>
            </div>
            <div class="flex-grow-1 ms-3 min-width-0">
              <h6 class="mb-0 text-truncate">{{ item.name }}</h6>
              <small class="text-muted">
                {{ formatSize(item.size) }} • {{ formatDate(item.modified) }}
              </small>
            </div>
            <div class="flex-shrink-0">
              <span 
                v-if="item.source === 'telegram'"
                class="badge bg-info"
              >
                <i class="fab fa-telegram"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Floating Action Button for Mobile -->
    <div class="fab-container d-md-none">
      <button 
        class="btn btn-primary btn-fab shadow"
        @click="showActionMenu"
      >
        <i class="fas fa-plus"></i>
      </button>
    </div>
  </div>
</template>

<style scoped>
/* Mobile-Specific Styles */
.breadcrumb-scroll {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

.breadcrumb-scroll::-webkit-scrollbar {
  height: 4px;
}

.file-item-card {
  background: var(--bg-secondary);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
}

.file-item-card:active {
  transform: scale(0.95);
  background: var(--bg-tertiary);
}

/* Floating Action Button */
.fab-container {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 1000;
}

.btn-fab {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Touch Feedback */
@media (hover: none) {
  .file-item-card:active {
    background: var(--primary);
    color: white;
  }
  
  .list-group-item:active {
    background: var(--bg-tertiary);
  }
}

/* Responsive Adjustments */
@media (max-width: 576px) {
  .file-explorer-mobile {
    margin: -15px;
  }
  
  .sticky-top {
    top: 56px; /* Account for navbar */
  }
}
</style>
```

## 🎯 Implementation Checklist

### Mobile Responsiveness
- [ ] Test on iPhone SE (375px) to iPhone 14 Pro Max (430px)
- [ ] Test on Android devices (360px - 412px)
- [ ] Test on tablets (768px - 1024px)
- [ ] Test landscape orientation
- [ ] Ensure touch targets are minimum 44x44px

### Theme Consistency
- [ ] Use existing color variables
- [ ] Match border radius (usually 4px or 8px)
- [ ] Use consistent spacing (Bootstrap utilities)
- [ ] Match font sizes and weights
- [ ] Implement dark mode support

### Performance
- [ ] Lazy load images and heavy components
- [ ] Use virtual scrolling for long lists
- [ ] Optimize for 3G connections
- [ ] Minimize JavaScript bundle size
- [ ] Use CSS transforms for animations

### Accessibility
- [ ] ARIA labels for all interactive elements
- [ ] Keyboard navigation support
- [ ] Screen reader compatibility
- [ ] Sufficient color contrast (WCAG AA)
- [ ] Focus indicators visible

## 📱 Mobile-Specific Features

### Touch Gestures
```javascript
// Swipe to delete
const swipeHandler = {
  touchStart: null,
  touchEnd: null,
  
  handleTouchStart(e) {
    this.touchStart = e.touches[0].clientX;
  },
  
  handleTouchEnd(e, item) {
    this.touchEnd = e.changedTouches[0].clientX;
    const diff = this.touchStart - this.touchEnd;
    
    if (diff > 100) { // Swipe left
      this.showDeleteConfirm(item);
    }
  }
};
```

### Progressive Web App Features
```json
// manifest.json additions
{
  "name": "Mitra Bot Manager",
  "short_name": "MitraBot",
  "theme_color": "#4F46E5",
  "background_color": "#FFFFFF",
  "display": "standalone",
  "orientation": "portrait"
}
```

### Offline Support
```javascript
// Service Worker for offline file viewing
self.addEventListener('fetch', event => {
  if (event.request.url.includes('/api/telegram/files/')) {
    event.respondWith(
      caches.match(event.request)
        .then(response => response || fetch(event.request))
    );
  }
});
```

## ✅ Final UI Checklist

1. **Navigation**: Tools menu accessible and usable on mobile
2. **Forms**: Input fields large enough for touch
3. **Tables**: Horizontal scroll or card view on mobile
4. **Modals**: Full-screen on mobile devices
5. **Buttons**: Minimum 44px touch targets
6. **Loading**: Skeleton screens for better perceived performance
7. **Errors**: Clear, actionable error messages
8. **Success**: Visual feedback for all actions
9. **Empty States**: Helpful messages when no data
10. **Offline**: Graceful degradation when offline