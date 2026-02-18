<?php

namespace App\Http\Controllers;

use App\Models\LlmModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LlmModelController extends Controller
{
    public function index(): View
    {
        $models = LlmModel::query()
            ->orderBy('priority')
            ->orderBy('id')
            ->get();

        return view('llm-models.index', compact('models'));
    }

    public function updatePriorities(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'models' => ['required', 'array', 'min:1'],
            'models.*.id' => ['required', 'integer', 'exists:llm_models,id'],
            'models.*.priority' => ['required', 'integer', 'min:1', 'max:999'],
            'models.*.is_enabled' => ['nullable', 'boolean'],
        ]);

        foreach ($validated['models'] as $modelInput) {
            LlmModel::query()->whereKey($modelInput['id'])->update([
                'priority' => $modelInput['priority'],
                'is_enabled' => (bool) ($modelInput['is_enabled'] ?? false),
            ]);
        }

        return redirect()
            ->route('llm-models.index')
            ->with('success', 'LLM model priorities updated.');
    }
}
