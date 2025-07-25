<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProjectTimeline;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TimelineController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ProjectTimeline::with(['project']);
        
        // Filter berdasarkan project
        if ($request->has('project_id') && $request->project_id) {
            $query->where('project_id', $request->project_id);
        }
        
        // Filter berdasarkan status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        $timelines = $query->latest()->paginate(10);
        $projects = Project::all();
        
        return view('timelines.index', compact('timelines', 'projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $project = null;
        if ($request->has('project')) {
            $project = Project::findOrFail($request->project);
        }
        
        $projects = Project::all();
        
        return view('timelines.create', compact('project', 'projects'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'milestone' => 'required|string|max:255',
            'description' => 'nullable|string',
            'planned_date' => 'required|date',
            'actual_date' => 'nullable|date|after_or_equal:planned_date',
            'status' => 'required|in:planned,in_progress,completed,delayed',
            'progress_percentage' => 'required|integer|min:0|max:100',
        ]);
        
        $timeline = ProjectTimeline::create([
            'project_id' => $request->project_id,
            'milestone' => $request->milestone,
            'description' => $request->description,
            'planned_date' => $request->planned_date,
            'actual_date' => $request->actual_date,
            'status' => $request->status,
            'progress_percentage' => $request->progress_percentage,
        ]);
        
        return redirect()->route('projects.show', $request->project_id)->with('success', 'Timeline milestone created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $timeline = ProjectTimeline::with(['project'])->findOrFail($id);
        
        return view('timelines.show', compact('timeline'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $timeline = ProjectTimeline::findOrFail($id);
        $projects = Project::all();
        
        // Get project from request parameter if provided
        $project = null;
        if ($request->has('project')) {
            $project = Project::findOrFail($request->project);
        } else {
            $project = $timeline->project;
        }
        
        return view('timelines.edit', compact('timeline', 'projects', 'project'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $timeline = ProjectTimeline::findOrFail($id);
        
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'milestone' => 'required|string|max:255',
            'description' => 'nullable|string',
            'planned_date' => 'required|date',
            'actual_date' => 'nullable|date|after_or_equal:planned_date',
            'status' => 'required|in:planned,in_progress,completed,delayed',
            'progress_percentage' => 'required|integer|min:0|max:100',
        ]);
        
        $oldStatus = $timeline->status;
        $timeline->update($request->all());
        
        // Log activity jika status berubah
        if ($oldStatus !== $request->status) {
            $timeline->project->activities()->create([
                'user_id' => Auth::id(),
                'activity_type' => 'timeline_status_changed',
                'description' => "Timeline milestone '{$timeline->milestone}' status changed from {$oldStatus} to {$request->status}",
                'changes' => [
                    'old_status' => $oldStatus,
                    'new_status' => $request->status
                ]
            ]);
        }
        
        return redirect()->route('projects.show', $timeline->project_id)->with('success', 'Timeline milestone updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $timeline = ProjectTimeline::findOrFail($id);
        $projectId = $timeline->project_id;
        $timeline->delete();
        
        return redirect()->route('projects.show', $projectId)->with('success', 'Timeline milestone deleted successfully.');
    }
    
    /**
     * Update timeline milestone status
     */
    public function updateStatus(Request $request, string $id)
    {
        $timeline = ProjectTimeline::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:planned,in_progress,completed,delayed',
            'progress_percentage' => 'required|integer|min:0|max:100',
        ]);
        
        $oldStatus = $timeline->status;
        $oldProgress = $timeline->progress_percentage;
        
        $timeline->update([
            'status' => $request->status,
            'progress_percentage' => $request->progress_percentage,
            'actual_date' => $request->status == 'completed' ? now() : $timeline->actual_date
        ]);
        
        // Log activity
        $timeline->project->activities()->create([
            'user_id' => Auth::id(),
            'activity_type' => 'timeline_updated',
            'description' => "Timeline milestone '{$timeline->milestone}' updated - Status: {$oldStatus} → {$request->status}, Progress: {$oldProgress}% → {$request->progress_percentage}%",
            'changes' => [
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'old_progress' => $oldProgress,
                'new_progress' => $request->progress_percentage
            ]
        ]);
        
        return redirect()->back()->with('success', 'Timeline milestone updated successfully.');
    }
}
