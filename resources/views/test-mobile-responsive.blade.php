<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile Responsiveness Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-4">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold mb-4">Mobile Responsiveness Test</h1>
        
        <div class="bg-white rounded-lg shadow p-6 mb-4">
            <h2 class="text-lg font-semibold mb-2">Device Detection Results</h2>
            
            @php
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                $isMobile = preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $userAgent);
                $isTablet = preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $userAgent);
                $useMobileComponent = $isMobile && !$isTablet;
            @endphp
            
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="font-medium">User Agent:</span>
                    <span class="text-sm text-gray-600 break-all">{{ substr($userAgent, 0, 50) }}...</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium">Is Mobile:</span>
                    <span class="px-2 py-1 rounded text-sm {{ $isMobile ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $isMobile ? 'Yes' : 'No' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium">Is Tablet:</span>
                    <span class="px-2 py-1 rounded text-sm {{ $isTablet ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $isTablet ? 'Yes' : 'No' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium">Component to Load:</span>
                    <span class="px-2 py-1 rounded text-sm {{ $useMobileComponent ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                        {{ $useMobileComponent ? 'Mobile Component' : 'Desktop Component' }}
                    </span>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 mb-4">
            <h2 class="text-lg font-semibold mb-2">Client-Side Detection</h2>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="font-medium">Screen Width:</span>
                    <span id="screenWidth" class="text-gray-600">-</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium">Screen Height:</span>
                    <span id="screenHeight" class="text-gray-600">-</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium">Device Type:</span>
                    <span id="deviceType" class="px-2 py-1 rounded text-sm">-</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium">Touch Support:</span>
                    <span id="touchSupport" class="px-2 py-1 rounded text-sm">-</span>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 mb-4">
            <h2 class="text-lg font-semibold mb-2">Responsive Breakpoints</h2>
            <div class="space-y-2">
                <div class="hidden sm:block">
                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-sm">SM and above (≥640px)</span>
                </div>
                <div class="hidden md:block">
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm">MD and above (≥768px)</span>
                </div>
                <div class="hidden lg:block">
                    <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-sm">LG and above (≥1024px)</span>
                </div>
                <div class="hidden xl:block">
                    <span class="px-2 py-1 bg-indigo-100 text-indigo-800 rounded text-sm">XL and above (≥1280px)</span>
                </div>
                <div class="block sm:hidden">
                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-sm">Mobile (< 640px)</span>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-2">Test Links</h2>
            <div class="space-y-2">
                <a href="/projects/31#dokumen" class="block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 text-center">
                    Go to Project Document Tab
                </a>
                <button onclick="simulateMobile()" class="w-full px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                    Simulate Mobile View (Resize Window)
                </button>
                <button onclick="simulateDesktop()" class="w-full px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600">
                    Simulate Desktop View (Resize Window)
                </button>
            </div>
        </div>
    </div>
    
    <script>
        function updateClientInfo() {
            document.getElementById('screenWidth').textContent = window.innerWidth + 'px';
            document.getElementById('screenHeight').textContent = window.innerHeight + 'px';
            
            const deviceType = document.getElementById('deviceType');
            if (window.innerWidth < 640) {
                deviceType.textContent = 'Mobile';
                deviceType.className = 'px-2 py-1 rounded text-sm bg-yellow-100 text-yellow-800';
            } else if (window.innerWidth < 768) {
                deviceType.textContent = 'Small Tablet';
                deviceType.className = 'px-2 py-1 rounded text-sm bg-orange-100 text-orange-800';
            } else if (window.innerWidth < 1024) {
                deviceType.textContent = 'Tablet';
                deviceType.className = 'px-2 py-1 rounded text-sm bg-blue-100 text-blue-800';
            } else {
                deviceType.textContent = 'Desktop';
                deviceType.className = 'px-2 py-1 rounded text-sm bg-purple-100 text-purple-800';
            }
            
            const touchSupport = document.getElementById('touchSupport');
            const hasTouch = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
            touchSupport.textContent = hasTouch ? 'Yes' : 'No';
            touchSupport.className = 'px-2 py-1 rounded text-sm ' + (hasTouch ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800');
        }
        
        function simulateMobile() {
            if (window.innerWidth > 375) {
                window.resizeTo(375, 667);
            }
            alert('Window resized to mobile dimensions (375x667). This may not work in all browsers due to security restrictions.');
        }
        
        function simulateDesktop() {
            if (window.innerWidth < 1280) {
                window.resizeTo(1280, 720);
            }
            alert('Window resized to desktop dimensions (1280x720). This may not work in all browsers due to security restrictions.');
        }
        
        // Update on load and resize
        updateClientInfo();
        window.addEventListener('resize', updateClientInfo);
    </script>
</body>
</html>