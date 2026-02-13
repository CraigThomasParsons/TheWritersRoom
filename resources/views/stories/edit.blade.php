@extends('layouts.app')

@section('title', 'Edit Story')

@section('header')
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Edit Story: {{ $story->title }}</h1>
@endsection

@section('content')
    <form method="POST" action="{{ route('stories.update', $story) }}" x-data="storyWriter()">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left Panel: Structured Fields -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Story Details</h2>

                <div class="space-y-5">
                    <!-- Persona -->
                    <div>
                        <label for="persona_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Persona
                        </label>
                        <select name="persona_id" id="persona_id" x-model="personaId"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Select Persona --</option>
                            @foreach ($personas as $persona)
                                <option value="{{ $persona->id }}" 
                                        data-name="{{ $persona->name }}"
                                        {{ old('persona_id', $story->persona_id) == $persona->id ? 'selected' : '' }}>
                                    {{ $persona->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Epic -->
                    <div>
                        <label for="epic_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Epic (optional)
                        </label>
                        <select name="epic_id" id="epic_id"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- No Epic --</option>
                            @foreach ($epics as $epic)
                                <option value="{{ $epic->id }}" {{ old('epic_id', $story->epic_id) == $epic->id ? 'selected' : '' }}>
                                    {{ $epic->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="title" id="title" value="{{ old('title', $story->title) }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="story_status_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select name="story_status_id" id="story_status_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($statuses as $status)
                                <option value="{{ $status->id }}" {{ old('story_status_id', $story->story_status_id) == $status->id ? 'selected' : '' }}>
                                    {{ $status->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Priority & Points -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Priority (0-100)
                            </label>
                            <input type="number" name="priority" id="priority" value="{{ old('priority', $story->priority) }}"
                                   min="0" max="100"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="est_points" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Est. Points
                            </label>
                            <input type="number" name="est_points" id="est_points" value="{{ old('est_points', $story->est_points) }}"
                                   min="0" max="100"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Writing Assistant -->
            <div class="space-y-6">
                <!-- Narrative -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">User Story Narrative</h2>
                    </div>

                    <div class="mb-3 p-3 bg-gray-50 dark:bg-gray-700 rounded text-sm text-gray-600 dark:text-gray-300">
                        <strong>Format:</strong> As a <em>[persona]</em>, I want <em>[capability]</em>, so that <em>[outcome]</em>.
                    </div>

                    <textarea name="narrative" id="narrative" rows="4" required x-model="narrative"
                              class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('narrative', $story->narrative) }}</textarea>
                </div>

                <!-- Acceptance Criteria -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Acceptance Criteria</h2>
                        <button type="button" @click="addScenario()" 
                                class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">
                            + Add Scenario
                        </button>
                    </div>

                    <textarea name="acceptance_criteria" id="acceptance_criteria" rows="10" x-model="acceptanceCriteria"
                              class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">{{ old('acceptance_criteria', $story->acceptance_criteria) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex justify-between">
            <form method="POST" action="{{ route('stories.destroy', $story) }}" 
                  onsubmit="return confirm('Are you sure you want to delete this story?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                    Delete Story
                </button>
            </form>

            <div class="flex gap-3">
                <a href="{{ route('stories.index') }}" 
                   class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Update Story
                </button>
            </div>
        </div>
    </form>

    <script>
        function storyWriter() {
            return {
                personaId: '{{ old('persona_id', $story->persona_id) }}',
                narrative: `{{ old('narrative', addslashes($story->narrative)) }}`,
                acceptanceCriteria: `{{ old('acceptance_criteria', addslashes($story->acceptance_criteria ?? '')) }}`,
                scenarioCount: 1,

                addScenario() {
                    this.scenarioCount++;
                    const newScenario = `

**Scenario ${this.scenarioCount}: [Name]**
Given [initial context]
When [action is taken]
Then [expected outcome]`;
                    this.acceptanceCriteria += newScenario;
                }
            }
        }
    </script>
@endsection
