@extends('layouts.app')

@section('title', $persona->name)

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $persona->name }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 font-mono">{{ $persona->key }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('personas.edit', $persona) }}" 
               class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                Edit
            </a>
            <a href="{{ route('stories.create', ['persona_id' => $persona->id]) }}" 
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                + New Story
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Persona Details -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Details</h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $persona->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $persona->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                @if ($persona->summary)
                    <p class="text-gray-600 dark:text-gray-300 mb-4">{{ $persona->summary }}</p>
                @endif

                @if ($persona->details)
                    <div class="prose prose-sm dark:prose-invert max-w-none">
                        {!! nl2br(e($persona->details)) !!}
                    </div>
                @endif

                <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Total Stories</dt>
                            <dd class="font-semibold text-gray-900 dark:text-gray-100">{{ $persona->stories->count() }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Created</dt>
                            <dd class="font-semibold text-gray-900 dark:text-gray-100">{{ $persona->created_at->diffForHumans() }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Recent Stories -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent Stories</h2>
                </div>

                @if ($persona->stories->count() > 0)
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($persona->stories as $story)
                            <li class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <a href="{{ route('stories.show', $story) }}" 
                                           class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800">
                                            {{ $story->title }}
                                        </a>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            {{ Str::limit($story->narrative, 80) }}
                                        </p>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-600 text-gray-800 dark:text-gray-200">
                                        {{ $story->status?->name ?? 'Draft' }}
                                    </span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="px-6 py-12 text-center">
                        <p class="text-gray-500 dark:text-gray-400">No stories yet for this persona.</p>
                        <a href="{{ route('stories.create', ['persona_id' => $persona->id]) }}" 
                           class="mt-4 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800">
                            Create the first story →
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
