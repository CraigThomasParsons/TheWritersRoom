<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use Illuminate\Http\Request;

class PersonaController extends Controller
{
    public function index(Request $request)
    {
        $query = Persona::query();

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('key', 'like', "%{$request->search}%")
                  ->orWhere('summary', 'like', "%{$request->search}%");
            });
        }

        if ($request->has('active_only') && $request->active_only) {
            $query->active();
        }

        $personas = $query->withCount('stories')
            ->orderBy('name')
            ->paginate(12);

        return view('personas.index', compact('personas'));
    }

    public function create()
    {
        return view('personas.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255|unique:personas,key',
            'name' => 'required|string|max:255',
            'summary' => 'nullable|string|max:500',
            'details' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        Persona::create($validated);

        return redirect()->route('personas.index')
            ->with('success', 'Persona created successfully.');
    }

    public function show(Persona $persona)
    {
        $persona->load(['stories' => function ($query) {
            $query->with('status')->orderBy('priority', 'desc')->limit(10);
        }]);

        return view('personas.show', compact('persona'));
    }

    public function edit(Persona $persona)
    {
        return view('personas.edit', compact('persona'));
    }

    public function update(Request $request, Persona $persona)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255|unique:personas,key,' . $persona->id,
            'name' => 'required|string|max:255',
            'summary' => 'nullable|string|max:500',
            'details' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $persona->update($validated);

        return redirect()->route('personas.index')
            ->with('success', 'Persona updated successfully.');
    }

    public function destroy(Persona $persona)
    {
        $persona->delete();

        return redirect()->route('personas.index')
            ->with('success', 'Persona deleted successfully.');
    }
}
