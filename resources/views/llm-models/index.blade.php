@extends('layouts.app')

@section('title', 'LLM Models')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        LLM Models
    </h2>
@endsection

@section('content')
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
            Prioritized model order used by Piper. Lower priority number runs first.
            If a provider fails (quota/rate/network), Piper falls through to the next enabled model.
        </p>

        <form method="POST" action="{{ route('llm-models.update-priorities') }}" class="space-y-4">
            @csrf

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enabled</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Display</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Provider</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Model Key</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Health</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($models as $index => $model)
                            <tr>
                                <td class="px-3 py-2">
                                    <input type="hidden" name="models[{{ $index }}][id]" value="{{ $model->id }}">
                                    <input type="checkbox" name="models[{{ $index }}][is_enabled]" value="1" {{ $model->is_enabled ? 'checked' : '' }}>
                                </td>
                                <td class="px-3 py-2">
                                    <input
                                        type="number"
                                        min="1"
                                        max="999"
                                        name="models[{{ $index }}][priority]"
                                        value="{{ $model->priority }}"
                                        class="w-24 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                    >
                                </td>
                                <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $model->display_name }}</td>
                                <td class="px-3 py-2 text-sm text-gray-700 dark:text-gray-300">{{ $model->provider }}</td>
                                <td class="px-3 py-2 text-sm text-gray-700 dark:text-gray-300">{{ $model->model_key }}</td>
                                <td class="px-3 py-2 text-sm">
                                    <span class="px-2 py-1 rounded text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                                        {{ $model->health_status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div>
                <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                >
                    Save Priorities
                </button>
            </div>
        </form>
    </div>
@endsection
