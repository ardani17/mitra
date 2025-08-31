# System Statistics Implementation Plan

## Overview
Implementation of a real-time System Statistics dashboard as a sub-menu under "Manajemen" (Management) in the navigation bar. This feature will display server resource usage with auto-refresh every 5 seconds.

## Architecture Design

### 1. Components Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── SystemStatisticsController.php
│   └── Middleware/
│       └── (uses existing role middleware)
├── Services/
│   └── SystemStatisticsService.php
└── Helpers/
    └── SystemHelper.php (optional)

resources/
├── views/
│   └── system-statistics/
│       └── index.blade.php
└── js/
    └── system-statistics.js (inline or separate)

routes/
└── web.php (add new routes)
```

### 2. Metrics to Display

#### Real-time Metrics (Auto-refresh every 5 seconds):
1. **CPU Usage**
   - Current usage percentage
   - Number of cores
   - Load average

2. **RAM Usage**
   - Total RAM
   - Used RAM
   - Available RAM
   - Usage percentage

3. **Disk Storage**
   - All drives/partitions
   - Total space per drive
   - Used space per drive
   - Free space per drive
   - Usage percentage per drive

4. **PHP Memory**
   - Memory limit
   - Current usage
   - Peak usage
   - Usage percentage

5. **Database**
   - Database size
   - Number of tables
   - Active connections
   - Max connections

6. **Laravel Cache**
   - Cache driver
   - Cache size (if applicable)
   - Hit/Miss ratio (if available)

7. **System Uptime**
   - Server uptime
   - Application uptime
   - Last restart time

8. **Application Info**
   - PHP version
   - Laravel version
   - Server OS
   - Web server info

### 3. Visual Design - Mobile-First Responsive

Following the existing dashboard pattern with enhanced mobile responsiveness:

#### Mobile-First Grid Layout
```html
<!-- Responsive Grid: 1 column mobile, 2 columns tablet, 4 columns desktop -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 lg:gap-6">
    
    <!-- CPU Usage Card - Mobile Optimized -->
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl p-3 sm:p-4 lg:p-6 shadow-lg hover:shadow-xl transition-shadow duration-300">
        <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
                <!-- Responsive text sizing -->
                <h3 class="text-xs sm:text-sm lg:text-lg font-semibold opacity-90 truncate">CPU Usage</h3>
                <p class="text-xl sm:text-2xl lg:text-3xl font-bold">45%</p>
                
                <!-- Progress bar -->
                <div class="w-full bg-blue-400 rounded-full h-1.5 sm:h-2 mt-2">
                    <div class="bg-white h-1.5 sm:h-2 rounded-full transition-all duration-500" style="width: 45%"></div>
                </div>
                
                <!-- Additional info - hidden on very small screens if needed -->
                <p class="text-xs sm:text-sm opacity-75 mt-1">4 cores</p>
            </div>
            <!-- Icon - smaller on mobile -->
            <div class="text-xl sm:text-2xl lg:text-4xl opacity-75 ml-2">
                💻
            </div>
        </div>
    </div>
    
    <!-- More responsive cards... -->
</div>
```

#### Mobile-Specific Features

1. **Collapsible Sections for Mobile**
```html
<!-- Mobile: Accordion-style sections -->
<div class="lg:hidden">
    <div class="space-y-2">
        <!-- System Resources Section -->
        <div x-data="{ open: true }" class="bg-white rounded-lg shadow">
            <button @click="open = !open" class="w-full px-4 py-3 flex items-center justify-between text-left">
                <span class="font-semibold text-gray-800">System Resources</span>
                <svg :class="{'rotate-180': open}" class="w-5 h-5 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="open" x-transition class="px-4 pb-4">
                <!-- Metrics cards here -->
            </div>
        </div>
        
        <!-- Storage Section -->
        <div x-data="{ open: false }" class="bg-white rounded-lg shadow">
            <!-- Similar structure -->
        </div>
    </div>
</div>
```

2. **Mobile-Optimized Card Layouts**
```html
<!-- Compact card for mobile -->
<div class="bg-white rounded-lg shadow p-3">
    <div class="flex items-center space-x-3">
        <!-- Icon -->
        <div class="flex-shrink-0">
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <!-- Icon SVG -->
                </svg>
            </div>
        </div>
        <!-- Content -->
        <div class="flex-1 min-w-0">
            <p class="text-xs text-gray-500">RAM Usage</p>
            <p class="text-lg font-semibold text-gray-900">8.5 GB / 16 GB</p>
            <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                <div class="bg-blue-600 h-1.5 rounded-full" style="width: 53%"></div>
            </div>
        </div>
        <!-- Percentage -->
        <div class="text-right">
            <p class="text-xl font-bold text-blue-600">53%</p>
        </div>
    </div>
</div>
```

3. **Responsive Text Hierarchy**
```css
/* Mobile-first text sizing */
.metric-title { @apply text-xs sm:text-sm lg:text-base; }
.metric-value { @apply text-lg sm:text-xl lg:text-2xl; }
.metric-subtitle { @apply text-xs sm:text-sm; }
.metric-icon { @apply text-xl sm:text-2xl lg:text-3xl; }
```

4. **Touch-Friendly Interactions**
- Minimum touch target size: 44x44px
- Adequate spacing between interactive elements
- Swipe gestures for navigation (optional)
- Larger buttons and clickable areas

5. **Performance Optimizations for Mobile**
- Lazy loading for non-critical metrics
- Reduced animation complexity on mobile
- Optional: Longer refresh interval on mobile (10 seconds instead of 5)
- Simplified charts/graphs for mobile

### 4. Implementation Steps

#### Step 1: Create SystemStatisticsService
```php
namespace App\Services;

class SystemStatisticsService
{
    public function getCpuUsage() { }
    public function getMemoryUsage() { }
    public function getDiskUsage() { }
    public function getPhpMemoryUsage() { }
    public function getDatabaseStats() { }
    public function getCacheStats() { }
    public function getSystemUptime() { }
    public function getApplicationInfo() { }
    public function getAllMetrics() { }
}
```

#### Step 2: Create SystemStatisticsController
```php
namespace App\Http\Controllers;

class SystemStatisticsController extends Controller
{
    public function index() { }
    public function metrics() { } // API endpoint for real-time data
}
```

#### Step 3: Add Routes
```php
// In routes/web.php
Route::middleware(['auth', 'role:direktur'])->group(function () {
    Route::get('/system-statistics', [SystemStatisticsController::class, 'index'])
        ->name('system-statistics.index');
    Route::get('/api/system-statistics/metrics', [SystemStatisticsController::class, 'metrics'])
        ->name('api.system-statistics.metrics');
});
```

#### Step 4: Update Navigation
Add to navigation.blade.php under Manajemen dropdown (line ~175):
```php
<a href="{{ route('system-statistics.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700">
    <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
    </svg>
    Statistik Sistem
</a>
```

### 5. JavaScript for Real-time Updates (Mobile-Optimized)

```javascript
// Mobile-aware auto-refresh
let metricsInterval;
let refreshRate = 5000; // Default 5 seconds

// Detect if mobile and adjust refresh rate
function isMobile() {
    return window.innerWidth < 768;
}

// Adjust refresh rate based on device and connection
function getOptimalRefreshRate() {
    if (isMobile()) {
        // Check connection type if available
        const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        if (connection) {
            if (connection.effectiveType === '4g') {
                return 5000; // 5 seconds for good connection
            } else if (connection.effectiveType === '3g') {
                return 10000; // 10 seconds for moderate connection
            } else {
                return 15000; // 15 seconds for slow connection
            }
        }
        return 10000; // Default 10 seconds for mobile
    }
    return 5000; // 5 seconds for desktop
}

function loadSystemMetrics() {
    // Show loading state on mobile
    if (isMobile()) {
        showMobileLoadingIndicator();
    }
    
    fetch('/api/system-statistics/metrics')
        .then(response => response.json())
        .then(data => {
            updateMetricsDisplay(data);
            hideMobileLoadingIndicator();
        })
        .catch(error => {
            console.error('Error loading metrics:', error);
            showErrorState();
        });
}

function updateMetricsDisplay(data) {
    // Update with smooth transitions
    Object.keys(data).forEach(metric => {
        const element = document.getElementById(`metric-${metric}`);
        if (element) {
            // Animate value changes
            animateValue(element, element.textContent, data[metric].value);
            
            // Update progress bars
            const progressBar = document.getElementById(`progress-${metric}`);
            if (progressBar) {
                progressBar.style.width = `${data[metric].percentage}%`;
            }
        }
    });
}

// Smooth number animation
function animateValue(element, start, end, duration = 500) {
    const startNum = parseFloat(start) || 0;
    const endNum = parseFloat(end) || 0;
    const startTime = performance.now();
    
    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const current = startNum + (endNum - startNum) * progress;
        element.textContent = current.toFixed(1);
        
        if (progress < 1) {
            requestAnimationFrame(update);
        }
    }
    
    requestAnimationFrame(update);
}

function startMetricsRefresh() {
    refreshRate = getOptimalRefreshRate();
    loadSystemMetrics(); // Initial load
    metricsInterval = setInterval(loadSystemMetrics, refreshRate);
    
    // Adjust refresh rate when screen size changes
    window.addEventListener('resize', () => {
        const newRate = getOptimalRefreshRate();
        if (newRate !== refreshRate) {
            refreshRate = newRate;
            stopMetricsRefresh();
            startMetricsRefresh();
        }
    });
}

function stopMetricsRefresh() {
    if (metricsInterval) {
        clearInterval(metricsInterval);
    }
}

// Visibility API to pause updates when tab is hidden
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        stopMetricsRefresh();
    } else {
        startMetricsRefresh();
    }
});

// Start on page load
document.addEventListener('DOMContentLoaded', startMetricsRefresh);

// Stop when leaving page
window.addEventListener('beforeunload', stopMetricsRefresh);
```

### 6. Caching Strategy

Implement 5-second cache to reduce server load:
```php
public function getAllMetrics()
{
    return Cache::remember('system_metrics', 5, function () {
        return [
            'cpu' => $this->getCpuUsage(),
            'memory' => $this->getMemoryUsage(),
            'disk' => $this->getDiskUsage(),
            // ... other metrics
        ];
    });
}
```

### 7. Windows-Specific Considerations

Since the application runs on Windows (Laragon):
- Use Windows-specific commands for system metrics
- Consider using WMI (Windows Management Instrumentation) for accurate metrics
- Handle Windows drive letters (C:, D:, etc.)
- Use appropriate PHP functions that work on Windows

### 8. Error Handling

- Graceful fallback when metrics cannot be retrieved
- Display "N/A" or appropriate message for unavailable metrics
- Log errors for debugging
- Ensure the dashboard remains functional even if some metrics fail

### 9. Security Considerations

- Restrict access to direktur role only
- Sanitize any system command outputs
- Don't expose sensitive system information
- Rate limit API requests if necessary

### 10. Performance Optimization

- Use efficient system commands
- Implement caching (5-second TTL)
- Minimize database queries
- Use asynchronous JavaScript for updates
- Consider using websockets for real-time updates (future enhancement)

## Testing Checklist

### Desktop Testing
- [ ] Test on Windows environment (Laragon)
- [ ] Verify all metrics display correctly
- [ ] Test auto-refresh functionality (5 seconds)
- [ ] Test on different desktop screen sizes
- [ ] Verify role-based access control
- [ ] Test error handling scenarios
- [ ] Check performance impact
- [ ] Validate cache functionality

### Mobile Testing
- [ ] Test on iPhone (Safari)
- [ ] Test on Android (Chrome)
- [ ] Test on tablet devices (iPad, Android tablets)
- [ ] Verify responsive grid layout (1 column on mobile)
- [ ] Test touch interactions
- [ ] Verify text readability on small screens
- [ ] Test collapsible sections functionality
- [ ] Verify progress bars scale properly
- [ ] Test auto-refresh on mobile (10 seconds)
- [ ] Test on slow mobile connections
- [ ] Verify no horizontal scrolling
- [ ] Test landscape and portrait orientations
- [ ] Verify loading indicators work properly
- [ ] Test with mobile data saver mode

### Responsive Breakpoints to Test
- [ ] 320px - 480px (Mobile phones)
- [ ] 481px - 768px (Tablets)
- [ ] 769px - 1024px (Small desktops)
- [ ] 1025px - 1200px (Desktops)
- [ ] 1201px and above (Large screens)

## Future Enhancements

1. Historical data storage and trending
2. Alert system for critical thresholds
3. Export functionality for reports
4. Customizable refresh intervals
5. More detailed application-specific metrics
6. WebSocket implementation for real-time updates
7. Dark mode support