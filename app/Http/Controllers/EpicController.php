<?php

namespace App\Http\Controllers;

use App\Models\Epic;
use App\Models\EpicStatus;
use Illuminate\Http\Request;

class EpicController extends Controller
{
    public function index(Request $request)
    {
        $query = Epic::with('status');

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('summary', 'like', "%{$request->search}%");
            });
        }

        if ($request->has('status') && $request->status) {
            $query->whereHas('status', fn ($q) => $q->where('key', $request->status));
        }

        $epics = $query->withCount('stories')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        $statuses = EpicStatus::all();

        return view('epics.index', compact('epics', 'statuses'));
    }

    public function create()
    {
        $statuses = EpicStatus::all();
        return view('epics.create', compact('statuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'epic_status_id' => 'required|exists:epic_statuses,id',
        ]);

        Epic::create($validated);

        return redirect()->route('epics.index')
            ->with('success', 'Epic created successfully.');
    }

    public function show(Epic $epic)
    {
        $epic->load(['status', 'stories' => function ($query) {
            $query->with(['status', 'persona'])->orderBy('priority', 'desc');
        }]);

        return view('epics.show', compact('epic'));
    }

    public function edit(Epic $epic)
    {
        $statuses = EpicStatus::all();
        return view('epics.edit', compact('epic', 'statuses'));
    }

    public function update(Request $request, Epic $epic)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'epic_status_id' => 'required|exists:epic_statuses,id',
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
}
