<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectDocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $project = Project::findOrFail($request->project_id);
        
        $documents = $project->documents()
            ->with('uploader')
            ->when($request->document_type, function ($query, $type) {
                return $query->where('document_type', $type);
            })
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('original_name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('documents.index', compact('project', 'documents'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'document' => 'required|file|max:10240', // 10MB max
            'document_type' => 'required|in:contract,technical,financial,report,other',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $project = Project::findOrFail($request->project_id);
        $file = $request->file('document');
        
        // Generate unique filename
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        
        // Store file
        $path = $file->storeAs('documents/projects/' . $project->id, $filename, 'public');
        
        // Create document record
        $document = ProjectDocument::create([
            'project_id' => $project->id,
            'uploaded_by' => auth()->id(),
            'name' => $request->name,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'document_type' => $request->document_type,
            'description' => $request->description,
        ]);

        // Log activity
        $project->activities()->create([
            'user_id' => auth()->id(),
            'description' => "Mengunggah dokumen: {$document->name}",
            'activity_type' => 'document_upload'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil diunggah',
            'document' => $document->load('uploader')
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProjectDocument $document)
    {
        return response()->file(storage_path('app/public/' . $document->file_path));
    }

    /**
     * Download the specified resource.
     */
    public function download(ProjectDocument $document)
    {
        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProjectDocument $document)
    {
        // Delete file from storage
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        // Log activity
        $document->project->activities()->create([
            'user_id' => auth()->id(),
            'description' => "Menghapus dokumen: {$document->name}",
            'activity_type' => 'document_delete'
        ]);

        $document->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil dihapus'
        ]);
    }
}
