<?php

namespace App\Http\Controllers\Api;

use App\Events\SprintCreated;
use App\Events\SprintReady;
use App\Http\Controllers\Controller;
use App\Models\Sprint;
use Illuminate\Http\Request;

class SprintController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Sprint::with('stories')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'goal' => 'required|string',
            'status' => 'sometimes|in:draft,ready,closed',
        ]);

        $sprint = Sprint::create($validated);

        event(new SprintCreated($sprint));

        return response()->json($sprint, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Sprint $sprint)
    {
        return $sprint->load('stories');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sprint $sprint)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'goal' => 'sometimes|required|string',
            'status' => 'sometimes|in:draft,ready,closed',
        ]);

        $oldStatus = $sprint->status;

        $sprint->update($validated);

        if ($oldStatus !== 'ready' && $sprint->status === 'ready') {
            event(new SprintReady($sprint));
        }

        return response()->json($sprint);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sprint $sprint)
    {
        $sprint->delete();

        return response()->json(null, 204);
    }
}
