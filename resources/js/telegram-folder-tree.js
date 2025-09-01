import { createApp } from 'vue';
import FolderTreeExplorer from './components/FolderTreeExplorer.vue';
import FolderTreeNode from './components/FolderTreeNode.vue';

// Global variables to store app instances
window.telegramFolderTreeApps = {
    copy: null,
    move: null
};

// Initialize Copy Tree App
window.initializeCopyTreeApp = function() {
    // Destroy existing app if it exists
    if (window.telegramFolderTreeApps.copy) {
        window.telegramFolderTreeApps.copy.unmount();
    }
    
    const app = createApp({
        components: {
            FolderTreeExplorer
        },
        template: `
            <FolderTreeExplorer
                modal-type="copy"
                :exclude-path="''"
                @select="onFolderSelect"
            />
        `,
        methods: {
            onFolderSelect(path) {
                document.getElementById('copyDestInput').value = path;
                const displayElement = document.getElementById('copySelectedPath');
                if (displayElement) {
                    displayElement.textContent = path ? '/' + path : '/';
                }
            }
        }
    });
    
    // Register FolderTreeNode globally for recursive use
    app.component('FolderTreeNode', FolderTreeNode);
    
    // Mount the app
    window.telegramFolderTreeApps.copy = app.mount('#copyFolderTreeApp');
};

// Initialize Move Tree App
window.initializeMoveTreeApp = function(excludePath) {
    // Destroy existing app if it exists
    if (window.telegramFolderTreeApps.move) {
        window.telegramFolderTreeApps.move.unmount();
    }
    
    const app = createApp({
        components: {
            FolderTreeExplorer
        },
        data() {
            return {
                excludePath: excludePath || ''
            };
        },
        template: `
            <FolderTreeExplorer
                modal-type="move"
                :exclude-path="excludePath"
                @select="onFolderSelect"
            />
        `,
        methods: {
            onFolderSelect(path) {
                document.getElementById('moveDestInput').value = path;
                const displayElement = document.getElementById('moveSelectedPath');
                if (displayElement) {
                    displayElement.textContent = path ? '/' + path : '/';
                }
            }
        }
    });
    
    // Register FolderTreeNode globally for recursive use
    app.component('FolderTreeNode', FolderTreeNode);
    
    // Mount the app
    window.telegramFolderTreeApps.move = app.mount('#moveFolderTreeApp');
};