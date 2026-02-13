@extends('layouts.app')

@section('title', 'Create Story')

@section('header')
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Create Story</h1>
@endsection

@section('content')
    <form method="POST" action="{{ route('stories.store') }}" x-data="storyWriter()">
        @csrf

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
                        <div class="relative">
                            <select name="persona_id" id="persona_id" x-model="personaId" @change="updateNarrative()"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Select Persona --</option>
                                @foreach ($personas as $persona)
                                    <option value="{{ $persona->id }}" 
                                            data-name="{{ $persona->name }}"
                                            {{ old('persona_id', $selectedPersonaId) == $persona->id ? 'selected' : '' }}>
                                        {{ $persona->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('persona_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
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
                                <option value="{{ $epic->id }}" {{ old('epic_id', $selectedEpicId) == $epic->id ? 'selected' : '' }}>
                                    {{ $epic->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('epic_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required
                               placeholder="Brief story title"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="story_status_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select name="story_status_id" id="story_status_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($statuses as $status)
                                <option value="{{ $status->id }}" {{ old('story_status_id', $statuses->firstWhere('key', 'draft')?->id) == $status->id ? 'selected' : '' }}>
                                    {{ $status->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('story_status_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Priority & Points -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Priority (0-100)
                            </label>
                            <input type="number" name="priority" id="priority" value="{{ old('priority', 0) }}"
                                   min="0" max="100"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="est_points" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Est. Points
                            </label>
                            <input type="number" name="est_points" id="est_points" value="{{ old('est_points') }}"
                                   min="0" max="100" placeholder="1, 2, 3, 5, 8..."
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
                        <button type="button" @click="generateNarrativeTemplate()" 
                                class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">
                            Generate Template
                        </button>
                    </div>

                    <div class="mb-3 p-3 bg-gray-50 dark:bg-gray-700 rounded text-sm text-gray-600 dark:text-gray-300">
                        <strong>Format:</strong> As a <em>[persona]</em>, I want <em>[capability]</em>, so that <em>[outcome]</em>.
                    </div>

                    <textarea name="narrative" id="narrative" rows="4" required x-model="narrative"
                              placeholder="As a [persona], I want [capability], so that [outcome]."
                              class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('narrative') }}</textarea>
                    @error('narrative')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
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

                    <div class="mb-3 p-3 bg-gray-50 dark:bg-gray-700 rounded text-sm text-gray-600 dark:text-gray-300">
                        <strong>Format:</strong> Given <em>[context]</em>, When <em>[action]</em>, Then <em>[outcome]</em>.
                    </div>

                    <textarea name="acceptance_criteria" id="acceptance_criteria" rows="10" x-model="acceptanceCriteria"
                              placeholder="**Scenario 1: [Name]**
Given [initial context]
When [action is taken]
Then [expected outcome]

**Scenario 2: [Name]**
Given [initial context]
When [action is taken]
Then [expected outcome]"
                              class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">{{ old('acceptance_criteria') }}</textarea>
                    @error('acceptance_criteria')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('stories.index') }}" 
               class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                Cancel
            </a>
            <button type="submit" 
                    class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Create Story
            </button>
        </div>
    </form>

    <script>
        function storyWriter() {
            return {
                personaId: '{{ old('persona_id', $selectedPersonaId) }}',
                narrative: `{{ old('narrative') }}`,
                acceptanceCriteria: `{{ old('acceptance_criteria') }}`,
                scenarioCount: 1,

                getPersonaName() {
                    const select = document.getElementById('persona_id');
                    if (select.selectedIndex > 0) {
                        return select.options[select.selectedIndex].dataset.name;
                    }
                    return '[persona]';
                },

                generateNarrativeTemplate() {
                    const personaName = this.getPersonaName();
                    this.narrative = `As a ${personaName}, I want [capability], so that [outcome].`;
                },

                updateNarrative() {
                    if (!this.narrative || this.narrative.includes('[persona]')) {
                        this.generateNarrativeTemplate();
                    }
                },

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
