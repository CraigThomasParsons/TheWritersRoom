<?php

namespace App\Http\Controllers;

use App\Models\Epic;
use App\Models\EpicStatus;
use App\Models\ChatProject;
use Illuminate\Http\Request;

/**
 * EpicController handles CRUD operations for epics.
 *
 * Epics group related stories together and can be associated
 * with a project from the ChatProjects system.
 */
class EpicController extends Controller
{
    /**
     * Display a listing of epics.
     *
     * Supports filtering by search term, status, and project.
     */
    public function index(Request $request)
    {
        // Build query with eager-loaded relationships
        $query = Epic::with(['status', 'chatProject']);

        // Filter by search term across title and summary
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('summary', 'like', "%{$request->search}%");
            });
        }

        // Filter by epic status key
        if ($request->has('status') && $request->status) {
            $query->whereHas('status', fn ($q) => $q->where('key', $request->status));
        }

        // Filter by project from ChatProjects
        if ($request->has('project_id') && $request->project_id) {
            $query->where('chat_project_id', $request->project_id);
        }

        // Paginate results with story count
        $epics = $query->withCount('stories')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        // Load filter options
        $statuses = EpicStatus::all();
        $projects = $this->getProjects();

        return view('epics.index', compact('epics', 'statuses', 'projects'));
    }

    /**
     * Show the form for creating a new epic.
     *
     * Loads statuses and projects for the dropdown selects.
     */
    public function create()
    {
        $statuses = EpicStatus::all();
        $projects = $this->getProjects();

        return view('epics.create', compact('statuses', 'projects'));
    }

    /**
     * Store a newly created epic in the database.
     */
    public function store(Request $request)
    {
        // Validate input including optional project association
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'epic_status_id' => 'required|exists:epic_statuses,id',
            'chat_project_id' => 'nullable|integer',
        ]);

        Epic::create($validated);

        return redirect()->route('epics.index')
            ->with('success', 'Epic created successfully.');
    }

    public function show(Epic $epic)
    {
        // Eager load relationships for display
        $epic->load(['status', 'chatProject', 'stories' => function ($query) {
            $query->with(['status', 'persona'])->orderBy('priority', 'desc');
        }]);

        return view('epics.show', compact('epic'));
    }

    /**
     * Show the form for editing an epic.
     */
    public function edit(Epic $epic)
    {
        $statuses = EpicStatus::all();
        $projects = $this->getProjects();

        return view('epics.edit', compact('epic', 'statuses', 'projects'));
    }

    public function update(Request $request, Epic $epic)
    {
        // Validate input including optional project association
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'epic_status_id' => 'required|exists:epic_statuses,id',
            'chat_project_id' => 'nullable|integer',
        ]);

        $epic->update($validated);

        return redirect()->route('epics.index')
            ->with('success', 'Epic updated successfully.');
    }

    public function destroy(Epic $epic)
    {
        $epic->delete();

        return redirect()->route('epics.index')
            ->with('success', 'Epic deleted successfully.');
    }

    /**
     * Get all projects from the ChatProjects database.
     *
     * Returns an empty collection if the connection fails,
     * allowing the app to work without ChatProjects access.
     */
    protected function getProjects()
    {
        try {
            return ChatProject::orderBy('name')->get();
        } catch (\Exception $exception) {
            // Log the error but don't break the page
            \Log::warning('Could not connect to ChatProjects: ' . $exception->getMessage());
            return collect();
        }
    }
}
