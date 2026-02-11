<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Epic;
use Illuminate\Http\Request;

class EpicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Epic::with('stories')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $epic = Epic::create($validated);

        return response()->json($epic, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Epic $epic)
    {
        return $epic->load('stories');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Epic $epic)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $epic->update($validated);

        return response()->json($epic);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Epic $epic)
    {
        $epic->delete();

        return response()->json(null, 204);
    }
}
