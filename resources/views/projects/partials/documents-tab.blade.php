{{-- Documents Tab Content --}}
<div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">
    {{-- Vue File Explorer Component --}}
    <x-vue-file-explorer :project="$project" />
</div>

{{-- Alternative: If using as a standalone section --}}
{{-- 
<div class="card">
    <div class="card-header">
        <h4 class="card-title">
            <i class="fas fa-folder-open mr-2"></i>
            Project Documents
        </h4>
    </div>
    <div class="card-body p-0">
        @include('components.file-explorer', ['project' => $project])
    </div>
</div>
--}}