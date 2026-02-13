<?php

namespace App\Http\Controllers;

use App\Models\Story;
use App\Models\StoryStatus;
use App\Models\Epic;
use App\Models\Persona;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Story::with(['status', 'epic', 'persona']);

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('narrative', 'like', "%{$request->search}%");
            });
        }

        if ($request->has('status') && $request->status) {
            $query->whereHas('status', fn ($q) => $q->where('key', $request->status));
        }

        if ($request->has('epic_id') && $request->epic_id) {
            $query->where('epic_id', $request->epic_id);
        }

        if ($request->has('persona_id') && $request->persona_id) {
            $query->where('persona_id', $request->persona_id);
        }

        $stories = $query->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $statuses = StoryStatus::all();
        $epics = Epic::orderBy('title')->get();
        $personas = Persona::active()->orderBy('name')->get();

        return view('stories.index', compact('stories', 'statuses', 'epics', 'personas'));
    }

    public function create(Request $request)
    {
        $statuses = StoryStatus::all();
        $epics = Epic::orderBy('title')->get();
        $personas = Persona::active()->orderBy('name')->get();
        
        $selectedEpicId = $request->get('epic_id');
        $selectedPersonaId = $request->get('persona_id');

        return view('stories.create', compact('statuses', 'epics', 'personas', 'selectedEpicId', 'selectedPersonaId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'narrative' => 'required|string',
            'acceptance_criteria' => 'nullable|string',
            'epic_id' => 'nullable|exists:epics,id',
            'persona_id' => 'nullable|exists:personas,id',
            'story_status_id' => 'required|exists:story_statuses,id',
            'priority' => 'integer|min:0|max:100',
            'est_points' => 'nullable|integer|min:0|max:100',
        ]);

        Story::create($validated);

        return redirect()->route('stories.index')
            ->with('success', 'Story created successfully.');
    }

    public function show(Story $story)
    {
        $story->load(['status', 'epic', 'persona', 'sprints']);
        return view('stories.show', compact('story'));
    }

    public function edit(Story $story)
    {
        $statuses = StoryStatus::all();
        $epics = Epic::orderBy('title')->get();
        $personas = Persona::active()->orderBy('name')->get();

        return view('stories.edit', compact('story', 'statuses', 'epics', 'personas'));
    }

    public function update(Request $request, Story $story)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'narrative' => 'required|string',
            'acceptance_criteria' => 'nullable|string',
            'epic_id' => 'nullable|exists:epics,id',
            'persona_id' => 'nullable|exists:personas,id',
            'story_status_id' => 'required|exists:story_statuses,id',
            'priority' => 'integer|min:0|max:100',
            'est_points' => 'nullable|integer|min:0|max:100',
        ]);

        $story->update($validated);

        return redirect()->route('stories.index')
            ->with('success', 'Story updated successfully.');
    }

    public function destroy(Story $story)
    {
        $story->delete();

        return redirect()->route('stories.index')
            ->with('success', 'Story deleted successfully.');
    }

    public function markReady(Story $story)
    {
        if (!$story->isReady()) {
            return back()->with('error', 'Story must have title, narrative, and acceptance criteria to be marked as ready.');
        }

        $story->markReady();

        return back()->with('success', 'Story marked as ready.');
    }
}
