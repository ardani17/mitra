<!-- Sync Status Modal -->
<div id="syncModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Sync Status</h3>
                <button onclick="closeSyncModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div id="syncContent" class="space-y-4">
                <!-- Content will be populated by JavaScript -->
                <div class="text-center py-8">
                    <svg class="animate-spin h-8 w-8 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">Checking sync status...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Sync Modal Functions
function closeSyncModal() {
    document.getElementById('syncModal').classList.add('hidden');
}

// Check sync status
function checkSyncStatus() {
    fetch('{{ route('telegram-bot.check-sync') }}', {
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = document.getElementById('syncModal');
            const content = document.getElementById('syncContent');
            
            let statusHtml = '<div class="space-y-4">';
            
            // Show sync status
            const syncStatus = data.is_synced ? '✅ Synced' : '⚠️ Not Synced';
            statusHtml += `
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-semibold mb-2">Sync Status: ${syncStatus}</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Local Files:</span> ${data.stats.local.total_files}
                        </div>
                        <div>
                            <span class="text-gray-600">Local Size:</span> ${data.stats.local.total_size_formatted}
                        </div>
                        <div>
                            <span class="text-gray-600">Total Issues:</span> ${data.stats.total_issues || 0}
                        </div>
                        <div>
                            <span class="text-gray-600">Bot API Files:</span> ${data.stats.bot_api.file_count || 0}
                        </div>
                    </div>
                    ${data.stats.last_sync ? `<div class="mt-2 text-sm text-gray-600">Last sync: ${data.stats.last_sync}</div>` : ''}
                </div>
            `;
            
            // Show issues if any
            if (!data.is_synced && data.issues) {
                statusHtml += '<div class="bg-yellow-50 p-4 rounded-lg">';
                statusHtml += '<h4 class="font-semibold mb-2">Sync Issues:</h4>';
                statusHtml += '<ul class="text-sm space-y-1">';
                
                if (data.stats.not_uploaded > 0) {
                    statusHtml += `<li>📤 ${data.stats.not_uploaded} file(s) not uploaded to Telegram</li>`;
                }
                if (data.stats.upload_failed > 0) {
                    statusHtml += `<li>❌ ${data.stats.upload_failed} file(s) failed to upload</li>`;
                }
                if (data.stats.upload_pending > 0) {
                    statusHtml += `<li>⏳ ${data.stats.upload_pending} file(s) pending upload</li>`;
                }
                if (data.stats.recently_modified > 0) {
                    statusHtml += `<li>📝 ${data.stats.recently_modified} file(s) modified after upload</li>`;
                }
                if (data.stats.orphaned_uploads > 0) {
                    statusHtml += `<li>👻 ${data.stats.orphaned_uploads} orphaned upload record(s)</li>`;
                }
                
                statusHtml += '</ul>';
                
                // Show detailed issues if available
                if (data.issues.not_uploaded && data.issues.not_uploaded.length > 0) {
                    statusHtml += '<details class="mt-3">';
                    statusHtml += '<summary class="cursor-pointer text-sm font-medium">Files not uploaded (' + data.issues.not_uploaded.length + ')</summary>';
                    statusHtml += '<ul class="mt-2 text-xs space-y-1 pl-4">';
                    data.issues.not_uploaded.slice(0, 10).forEach(file => {
                        statusHtml += `<li>• ${file.name} (${file.size_formatted})</li>`;
                    });
                    if (data.issues.not_uploaded.length > 10) {
                        statusHtml += `<li class="text-gray-500">... and ${data.issues.not_uploaded.length - 10} more</li>`;
                    }
                    statusHtml += '</ul>';
                    statusHtml += '</details>';
                }
                
                if (data.issues.upload_failed && data.issues.upload_failed.length > 0) {
                    statusHtml += '<details class="mt-3">';
                    statusHtml += '<summary class="cursor-pointer text-sm font-medium">Failed uploads (' + data.issues.upload_failed.length + ')</summary>';
                    statusHtml += '<ul class="mt-2 text-xs space-y-1 pl-4">';
                    data.issues.upload_failed.slice(0, 5).forEach(file => {
                        statusHtml += `<li>• ${file.name}: ${file.error || 'Unknown error'}</li>`;
                    });
                    if (data.issues.upload_failed.length > 5) {
                        statusHtml += `<li class="text-gray-500">... and ${data.issues.upload_failed.length - 5} more</li>`;
                    }
                    statusHtml += '</ul>';
                    statusHtml += '</details>';
                }
                
                statusHtml += '</div>';
            }
            
            // Show sync button if not synced
            if (!data.is_synced) {
                statusHtml += `
                    <button onclick="performSync()" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Synchronize Now
                    </button>
                `;
            } else {
                statusHtml += `
                    <div class="text-center text-green-600">
                        <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        All files are synchronized
                    </div>
                `;
            }
            
            statusHtml += '</div>';
            content.innerHTML = statusHtml;
            modal.classList.remove('hidden');
        } else {
            alert('Failed to check sync status: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to check sync status');
    });
}

// Perform sync
function performSync() {
    const syncButton = event.target;
    syncButton.disabled = true;
    syncButton.textContent = 'Synchronizing...';
    
    fetch('{{ route('telegram-bot.sync-storage') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            clean_orphaned: false,
            process_queue: true
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let message = 'Synchronization completed!\n\n';
            if (data.results) {
                if (data.results.queued_for_upload > 0) {
                    message += `✅ ${data.results.queued_for_upload} file(s) queued for upload\n`;
                }
                if (data.results.retried_failed > 0) {
                    message += `🔄 ${data.results.retried_failed} failed upload(s) retried\n`;
                }
                if (data.results.cleaned_orphaned > 0) {
                    message += `🧹 ${data.results.cleaned_orphaned} orphaned record(s) cleaned\n`;
                }
                if (data.results.processed_immediately) {
                    message += `📤 ${data.results.processed_immediately} file(s) processed immediately\n`;
                }
                if (data.results.errors && data.results.errors.length > 0) {
                    message += `\n⚠️ ${data.results.errors.length} error(s) occurred`;
                }
            }
            alert(message);
            closeSyncModal();
            location.reload();
        } else {
            alert('Synchronization failed: ' + (data.message || 'Unknown error'));
            syncButton.disabled = false;
            syncButton.textContent = 'Synchronize Now';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to perform synchronization');
        syncButton.disabled = false;
        syncButton.textContent = 'Synchronize Now';
    });
}

// Update the handleSyncButton function to show the modal
function handleSyncButton() {
    checkSyncStatus();
}
</script>