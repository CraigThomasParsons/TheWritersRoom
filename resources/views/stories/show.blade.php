@extends('layouts.app')

@section('title', $story->title)

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $story->title }}</h1>
            <div class="flex items-center gap-3 mt-2">
                @php
                    $statusColors = [
                        'draft' => 'bg-gray-100 text-gray-800',
                        'ready' => 'bg-blue-100 text-blue-800',
                        'blocked' => 'bg-red-100 text-red-800',
                        'in_progress' => 'bg-yellow-100 text-yellow-800',
                        'done' => 'bg-green-100 text-green-800',
                        'archived' => 'bg-gray-100 text-gray-600',
                    ];
                @endphp
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$story->status?->key] ?? 'bg-gray-100 text-gray-800' }}">
                    {{ $story->status?->name ?? 'Draft' }}
                </span>
                @if ($story->est_points)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                        {{ $story->est_points }} pts
                    </span>
                @endif
                @if ($story->priority > 0)
                    <span class="text-sm text-orange-600 font-medium">Priority: {{ $story->priority }}</span>
                @endif
            </div>
        </div>
        <div class="flex gap-2">
            @if ($story->status?->key !== 'ready' && $story->isReady())
                <form method="POST" action="{{ route('stories.mark-ready', $story) }}">
                    @csrf
                    <button type="submit" 
                            class="px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                        ✓ Mark Ready
                    </button>
                </form>
            @endif
            <a href="{{ route('stories.edit', $story) }}" 
               class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                Edit
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Story Details Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 space-y-6">
                <!-- Persona -->
                @if ($story->persona)
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Persona</h3>
                        <a href="{{ route('personas.show', $story->persona) }}" 
                           class="flex items-center gap-2 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                            <span class="text-2xl">👤</span>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $story->persona->name }}</p>
                                @if ($story->persona->summary)
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ Str::limit($story->persona->summary, 50) }}</p>
                                @endif
                            </div>
                        </a>
                    </div>
                @endif

                <!-- Epic -->
                @if ($story->epic)
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Epic</h3>
                        <a href="{{ route('epics.show', $story->epic) }}" 
                           class="flex items-center gap-2 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                            <span class="text-2xl">📁</span>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $story->epic->title }}</p>
                            </div>
                        </a>
                    </div>
                @endif

                <!-- Sprints -->
                @if ($story->sprints->count() > 0)
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">In Sprints</h3>
                        <ul class="space-y-2">
                            @foreach ($story->sprints as $sprint)
                                <li class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <p class="font-medium text-gray-900 dark:text-gray-100 text-sm">{{ $sprint->title }}</p>
                                    @if ($sprint->is_frozen)
                                        <span class="text-xs text-blue-600">🔒 Frozen</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Meta -->
                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Created</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $story->created_at->format('M d, Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Updated</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $story->updated_at->diffForHumans() }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Narrative -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">User Story</h2>
                <div class="prose dark:prose-invert max-w-none">
                    <p class="text-gray-700 dark:text-gray-300 text-lg italic">
                        "{{ $story->narrative }}"
                    </p>
                </div>
            </div>

            <!-- Acceptance Criteria -->
            @if ($story->acceptance_criteria)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Acceptance Criteria</h2>
                    <div class="prose prose-sm dark:prose-invert max-w-none bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <pre class="whitespace-pre-wrap text-gray-700 dark:text-gray-300 font-mono text-sm">{{ $story->acceptance_criteria }}</pre>
                    </div>
                </div>
            @endif

            <!-- Ready Checklist -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Ready Checklist</h2>
                <ul class="space-y-2">
                    <li class="flex items-center gap-2">
                        @if ($story->title)
                            <span class="text-green-500">✓</span>
                        @else
                            <span class="text-gray-400">○</span>
                        @endif
                        <span class="text-gray-700 dark:text-gray-300">Has title</span>
                    </li>
                    <li class="flex items-center gap-2">
                        @if ($story->narrative)
                            <span class="text-green-500">✓</span>
                        @else
                            <span class="text-gray-400">○</span>
                        @endif
                        <span class="text-gray-700 dark:text-gray-300">Has narrative</span>
                    </li>
                    <li class="flex items-center gap-2">
                        @if ($story->acceptance_criteria)
                            <span class="text-green-500">✓</span>
                        @else
                            <span class="text-gray-400">○</span>
                        @endif
                        <span class="text-gray-700 dark:text-gray-300">Has acceptance criteria</span>
                    </li>
                </ul>
                
                @if ($story->isReady())
                    <p class="mt-4 text-sm text-green-600 dark:text-green-400">
                        ✓ This story meets all requirements to be marked as Ready.
                    </p>
                @else
                    <p class="mt-4 text-sm text-orange-600 dark:text-orange-400">
                        ⚠ This story is missing required fields to be marked as Ready.
                    </p>
                @endif
            </div>
        </div>
    </div>
@endsection
