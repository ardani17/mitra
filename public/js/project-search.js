/**
 * Enhanced Project Search Module
 * Supports both desktop and mobile with improved search functionality
 */

class ProjectSearch {
    constructor(config) {
        this.searchInput = document.getElementById(config.searchInputId || 'project_search');
        this.hiddenInput = document.getElementById(config.hiddenInputId || 'project_id');
        this.suggestionsDiv = document.getElementById(config.suggestionsDivId || 'project_suggestions');
        this.apiSearchUrl = config.apiSearchUrl || '/api/projects/search';
        this.apiPopularUrl = config.apiPopularUrl || '/api/projects/popular';
        this.onSelect = config.onSelect || null;
        this.debounceTimer = null;
        this.selectedProjectData = null;
        
        // Mobile detection
        this.isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        this.isIOS = /iPhone|iPad|iPod/i.test(navigator.userAgent);
        
        this.init();
    }
    
    init() {
        if (!this.searchInput || !this.hiddenInput || !this.suggestionsDiv) {
            console.error('ProjectSearch: Required elements not found');
            return;
        }
        
        // Add mobile search button if on mobile
        if (this.isMobile) {
            this.addMobileSearchButton();
        }
        
        // Setup event listeners
        this.setupEventListeners();
        
        // Load initial data if input has value
        if (this.hiddenInput.value && this.searchInput.value) {
            this.searchInput.dataset.selectedDisplay = this.searchInput.value;
        }
    }
    
    addMobileSearchButton() {
        // Create mobile search button
        const wrapper = this.searchInput.parentElement;
        if (!wrapper.querySelector('.mobile-search-btn')) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'mobile-search-btn absolute right-2 top-1/2 transform -translate-y-1/2 bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm font-medium hidden';
            button.textContent = 'Cari';
            button.style.zIndex = '10';
            
            button.addEventListener('click', () => {
                const query = this.searchInput.value.trim();
                if (query) {
                    this.searchProjects(query);
                }
            });
            
            wrapper.style.position = 'relative';
            wrapper.appendChild(button);
            this.mobileSearchBtn = button;
        }
    }
    
    setupEventListeners() {
        // Focus event - load popular projects
        this.searchInput.addEventListener('focus', () => {
            if (this.searchInput.value.trim() === '') {
                this.loadPopularProjects();
            }
        });
        
        // Input event - search with debounce
        if (!this.isMobile) {
            // Desktop: real-time search
            this.searchInput.addEventListener('input', (e) => {
                this.handleInput(e.target.value);
            });
        } else {
            // Mobile: different strategy
            this.setupMobileEvents();
        }
        
        // Click outside to hide suggestions
        document.addEventListener('click', (e) => {
            if (!this.searchInput.contains(e.target) && !this.suggestionsDiv.contains(e.target)) {
                this.hideSuggestions();
            }
        });
    }
    
    setupMobileEvents() {
        // Multiple event types for better mobile compatibility
        
        // Input event
        this.searchInput.addEventListener('input', (e) => {
            const query = e.target.value.trim();
            
            // Clear selection if typing
            if (!this.searchInput.dataset.selectedDisplay || this.searchInput.value !== this.searchInput.dataset.selectedDisplay) {
                this.hiddenInput.value = '';
                delete this.searchInput.dataset.selectedDisplay;
                this.selectedProjectData = null;
            }
            
            // Show/hide mobile button
            if (this.mobileSearchBtn) {
                if (query) {
                    this.mobileSearchBtn.classList.remove('hidden');
                } else {
                    this.mobileSearchBtn.classList.add('hidden');
                }
            }
            
            // Don't auto-search on every keystroke on mobile
            // User can tap the search button or press Enter
        });
        
        // Keyup event for Enter key
        this.searchInput.addEventListener('keyup', (e) => {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
                const query = this.searchInput.value.trim();
                if (query) {
                    this.searchProjects(query);
                }
            }
        });
        
        // Change event
        this.searchInput.addEventListener('change', (e) => {
            const query = e.target.value.trim();
            if (query && !this.hiddenInput.value) {
                this.searchProjects(query);
            }
        });
        
        // Search event (for search-type inputs)
        this.searchInput.addEventListener('search', (e) => {
            const query = e.target.value.trim();
            if (query) {
                this.searchProjects(query);
            }
        });
        
        // iOS specific handling
        if (this.isIOS) {
            this.setupIOSEvents();
        }
    }
    
    setupIOSEvents() {
        let composing = false;
        
        this.searchInput.addEventListener('compositionstart', () => {
            composing = true;
        });
        
        this.searchInput.addEventListener('compositionend', (e) => {
            composing = false;
            if (this.mobileSearchBtn) {
                const query = e.target.value.trim();
                this.mobileSearchBtn.classList.toggle('hidden', !query);
            }
        });
        
        // Touchend fallback for iOS
        this.searchInput.addEventListener('touchend', () => {
            setTimeout(() => {
                if (this.mobileSearchBtn) {
                    const query = this.searchInput.value.trim();
                    this.mobileSearchBtn.classList.toggle('hidden', !query);
                }
            }, 100);
        });
    }
    
    handleInput(value) {
        const query = value.trim();
        
        // Clear selection if typing
        if (!this.searchInput.dataset.selectedDisplay || this.searchInput.value !== this.searchInput.dataset.selectedDisplay) {
            this.hiddenInput.value = '';
            delete this.searchInput.dataset.selectedDisplay;
            this.selectedProjectData = null;
        }
        
        // Debounce search
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => {
            if (query.length >= 1) {
                this.searchProjects(query);
            } else if (query.length === 0) {
                this.loadPopularProjects();
            } else {
                this.hideSuggestions();
            }
        }, this.isMobile ? 500 : 300);
    }
    
    loadPopularProjects() {
        fetch(this.apiPopularUrl)
            .then(response => response.json())
            .then(projects => {
                this.displaySuggestions(projects, 'Proyek Populer');
            })
            .catch(error => {
                console.error('Error loading popular projects:', error);
                this.showError('Gagal memuat proyek populer');
            });
    }
    
    searchProjects(query) {
        // Show loading state
        this.suggestionsDiv.innerHTML = `
            <div class="px-3 py-2 text-sm text-gray-500">
                <span class="inline-block animate-pulse">Mencari...</span>
            </div>
        `;
        this.suggestionsDiv.classList.remove('hidden');
        
        fetch(`${this.apiSearchUrl}?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(projects => {
                this.displaySuggestions(projects, 'Hasil Pencarian');
            })
            .catch(error => {
                console.error('Error searching projects:', error);
                this.showError('Gagal mencari proyek');
            });
    }
    
    displaySuggestions(projects, title) {
        if (projects.length === 0) {
            this.suggestionsDiv.innerHTML = `
                <div class="px-3 py-2 text-sm text-gray-500">
                    Tidak ada proyek ditemukan
                </div>
            `;
            this.suggestionsDiv.classList.remove('hidden');
            return;
        }
        
        let html = '';
        if (title && projects.length > 0) {
            html += `<div class="px-3 py-2 text-xs font-medium text-gray-500 bg-gray-50 border-b">${title}</div>`;
        }
        
        projects.forEach(project => {
            html += `
                <div class="px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 project-suggestion"
                     data-project-id="${project.id}"
                     data-project-display="${project.display}"
                     data-project-name="${project.name || ''}"
                     data-project-code="${project.code || ''}">
                    <div class="text-sm text-gray-900">${this.escapeHtml(project.display)}</div>
                    ${project.code ? `<div class="text-xs text-gray-500">${this.escapeHtml(project.name)}</div>` : ''}
                </div>
            `;
        });
        
        this.suggestionsDiv.innerHTML = html;
        this.suggestionsDiv.classList.remove('hidden');
        
        // Add click event listeners
        this.suggestionsDiv.querySelectorAll('.project-suggestion').forEach(suggestion => {
            suggestion.addEventListener('click', () => {
                this.selectProject(suggestion);
            });
        });
    }
    
    selectProject(element) {
        const id = element.getAttribute('data-project-id');
        const display = element.getAttribute('data-project-display');
        const name = element.getAttribute('data-project-name');
        const code = element.getAttribute('data-project-code');
        
        this.hiddenInput.value = id;
        this.searchInput.value = display;
        this.searchInput.dataset.selectedDisplay = display;
        
        this.selectedProjectData = {
            id: id,
            display: display,
            name: name,
            code: code
        };
        
        // Hide mobile button after selection
        if (this.mobileSearchBtn) {
            this.mobileSearchBtn.classList.add('hidden');
        }
        
        this.hideSuggestions();
        
        // Trigger change event
        const event = new Event('change', { bubbles: true });
        this.hiddenInput.dispatchEvent(event);
        
        // Call custom onSelect callback if provided
        if (this.onSelect && typeof this.onSelect === 'function') {
            this.onSelect(this.selectedProjectData);
        }
    }
    
    hideSuggestions() {
        this.suggestionsDiv.classList.add('hidden');
        this.suggestionsDiv.innerHTML = '';
    }
    
    showError(message) {
        this.suggestionsDiv.innerHTML = `
            <div class="px-3 py-2 text-sm text-red-600">
                ${message}
            </div>
        `;
        this.suggestionsDiv.classList.remove('hidden');
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Public methods
    reset() {
        this.searchInput.value = '';
        this.hiddenInput.value = '';
        delete this.searchInput.dataset.selectedDisplay;
        this.selectedProjectData = null;
        this.hideSuggestions();
        
        if (this.mobileSearchBtn) {
            this.mobileSearchBtn.classList.add('hidden');
        }
    }
    
    getValue() {
        return this.hiddenInput.value;
    }
    
    getSelectedData() {
        return this.selectedProjectData;
    }
}

// Export for use in other scripts
window.ProjectSearch = ProjectSearch;