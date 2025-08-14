<script>
// SIMPLE SALARY STATUS IMPLEMENTATION
console.log('Salary Status Script Loading...');

// Wait for DOM
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Ready - Initializing Salary Status');
    
    // Get elements
    const btn = document.getElementById('salary-status-btn');
    const modal = document.getElementById('salary-status-modal');
    const content = document.getElementById('modal-content');
    
    console.log('Found elements:', {
        button: !!btn,
        modal: !!modal,
        content: !!content
    });
    
    // Add click handler to button
    if (btn) {
        btn.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Button clicked!');
            openSalaryModal();
            return false;
        };
        console.log('Click handler attached to button');
    }
    
    // Close modal on outside click
    if (modal) {
        modal.onclick = function(e) {
            if (e.target === modal) {
                closeSalaryModal();
            }
        };
    }
});

// Open modal function
function openSalaryModal() {
    console.log('Opening salary modal...');
    const modal = document.getElementById('salary-status-modal');
    const content = document.getElementById('modal-content');
    
    if (!modal || !content) {
        alert('Modal elements not found!');
        return;
    }
    
    // Show loading
    content.innerHTML = `
        <div class="text-center py-12">
            <i class="fas fa-spinner fa-spin text-5xl text-blue-500 mb-4"></i>
            <p class="text-xl text-gray-700">Loading salary status...</p>
        </div>
    `;
    
    // Show modal
    modal.classList.remove('hidden');
    
    // Fetch data
    fetch('/finance/api/employees/salary-status-summary')
        .then(response => {
            console.log('API Response:', response.status);
            if (!response.ok) throw new Error('API Error: ' + response.status);
            return response.json();
        })
        .then(data => {
            console.log('Data received:', data);
            showSalaryData(data);
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = `
                <div class="text-center py-12">
                    <i class="fas fa-exclamation-circle text-5xl text-red-500 mb-4"></i>
                    <p class="text-xl text-gray-900 mb-2">Error Loading Data</p>
                    <p class="text-gray-600 mb-6">${error.message}</p>
                    <button onclick="closeSalaryModal()" 
                            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Close
                    </button>
                </div>
            `;
        });
}

// Close modal function
function closeSalaryModal() {
    console.log('Closing modal...');
    const modal = document.getElementById('salary-status-modal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

// Display data function
function showSalaryData(data) {
    console.log('Displaying salary data...');
    const content = document.getElementById('modal-content');
    if (!content) return;
    
    // Build employee lists
    const emptyEmployees = data.employees.filter(e => e.status === 'empty');
    const partialEmployees = data.employees.filter(e => e.status === 'partial');
    
    let employeeListHtml = '';
    
    if (emptyEmployees.length > 0) {
        employeeListHtml += `
            <div class="mb-4">
                <h4 class="text-md font-bold text-red-700 mb-2">
                    🔴 Belum Input Gaji (${emptyEmployees.length} orang)
                </h4>
                <div class="space-y-2 max-h-40 overflow-y-auto">
                    ${emptyEmployees.map(emp => `
                        <div class="flex justify-between items-center p-2 bg-red-50 rounded">
                            <span class="text-sm">
                                ${emp.name} (${emp.employee_code}) - 
                                ${emp.input_days}/${emp.working_days} hari
                            </span>
                            <a href="/finance/daily-salaries/create?employee_id=${emp.employee_id}"
                               class="text-xs bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                                Input
                            </a>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }
    
    if (partialEmployees.length > 0) {
        employeeListHtml += `
            <div class="mb-4">
                <h4 class="text-md font-bold text-yellow-700 mb-2">
                    🟡 Perlu Dilengkapi (${partialEmployees.length} orang)
                </h4>
                <div class="space-y-2 max-h-40 overflow-y-auto">
                    ${partialEmployees.map(emp => `
                        <div class="flex justify-between items-center p-2 bg-yellow-50 rounded">
                            <span class="text-sm">
                                ${emp.name} - ${emp.input_days}/${emp.working_days} hari 
                                (${emp.percentage}%)
                            </span>
                            <a href="/finance/daily-salaries/create?employee_id=${emp.employee_id}"
                               class="text-xs bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                                Lengkapi
                            </a>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }
    
    if (emptyEmployees.length === 0 && partialEmployees.length === 0) {
        employeeListHtml = `
            <div class="text-center py-8 bg-green-50 rounded-lg">
                <i class="fas fa-check-circle text-5xl text-green-500 mb-3"></i>
                <p class="text-lg font-bold text-green-700">Semua Sudah Lengkap!</p>
                <p class="text-sm text-gray-600">Tidak ada yang perlu input gaji.</p>
            </div>
        `;
    }
    
    // Build complete modal content
    content.innerHTML = `
        <!-- Header -->
        <div class="flex justify-between items-center mb-6 pb-4 border-b">
            <h2 class="text-2xl font-bold text-gray-900">
                📊 Status Input Gaji
            </h2>
            <button onclick="closeSalaryModal()" 
                    class="text-gray-500 hover:text-gray-700 transition">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        
        <!-- Period Info -->
        <div class="bg-blue-50 rounded-lg p-4 mb-6">
            <h3 class="text-lg font-semibold text-blue-900 mb-1">
                ${data.period.name}
            </h3>
            <p class="text-sm text-blue-700">
                ${formatDate(data.period.start)} - ${formatDate(data.period.end)}
                <span class="font-semibold">(${data.period.working_days} hari kerja)</span>
            </p>
        </div>
        
        <!-- Statistics -->
        <div class="grid grid-cols-4 gap-3 mb-6">
            <div class="text-center p-3 bg-gray-100 rounded-lg">
                <div class="text-2xl font-bold text-gray-900">${data.total}</div>
                <div class="text-xs text-gray-600">Total</div>
            </div>
            <div class="text-center p-3 bg-green-100 rounded-lg">
                <div class="text-2xl font-bold text-green-700">${data.complete}</div>
                <div class="text-xs text-gray-600">Lengkap</div>
                <div class="text-xs font-semibold text-green-600">${data.complete_percentage}%</div>
            </div>
            <div class="text-center p-3 bg-yellow-100 rounded-lg">
                <div class="text-2xl font-bold text-yellow-700">${data.partial}</div>
                <div class="text-xs text-gray-600">Kurang</div>
                <div class="text-xs font-semibold text-yellow-600">${data.partial_percentage}%</div>
            </div>
            <div class="text-center p-3 bg-red-100 rounded-lg">
                <div class="text-2xl font-bold text-red-700">${data.empty}</div>
                <div class="text-xs text-gray-600">Belum</div>
                <div class="text-xs font-semibold text-red-600">${data.empty_percentage}%</div>
            </div>
        </div>
        
        <!-- Employee Lists -->
        ${employeeListHtml}
        
        <!-- Footer Actions -->
        <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
            <a href="/finance/daily-salaries/create" 
               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition">
                <i class="fas fa-plus mr-2"></i>Input Gaji
            </a>
            <button onclick="closeSalaryModal()" 
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition">
                Tutup
            </button>
        </div>
    `;
}

// Format date helper
function formatDate(dateStr) {
    const date = new Date(dateStr);
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 
                    'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return date.getDate() + ' ' + months[date.getMonth()] + ' ' + date.getFullYear();
}

// Auto-submit filters
document.addEventListener('DOMContentLoaded', function() {
    const selects = ['status', 'department', 'employment_type'];
    selects.forEach(id => {
        const elem = document.getElementById(id);
        if (elem) {
            elem.addEventListener('change', function() {
                this.form.submit();
            });
        }
    });
});

console.log('Salary Status Script Loaded Successfully');
</script>