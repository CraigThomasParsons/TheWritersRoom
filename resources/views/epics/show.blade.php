@extends('layouts.app')

@section('title', $epic->title)

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $epic->title }}</h1>
            @php
                $statusColors = [
                    'backlog' => 'bg-gray-100 text-gray-800',
                    'active' => 'bg-blue-100 text-blue-800',
                    'done' => 'bg-green-100 text-green-800',
                    'archived' => 'bg-yellow-100 text-yellow-800',
                ];
            @endphp
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$epic->status?->key] ?? 'bg-gray-100 text-gray-800' }}">
                {{ $epic->status?->name ?? 'Unknown' }}
            </span>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('epics.edit', $epic) }}" 
               class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                Edit
            </a>
            <a href="{{ route('stories.create', ['epic_id' => $epic->id]) }}" 
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                + Add Story
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Epic Details Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Epic Details</h2>
                
                @if ($epic->summary)
                    <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">{{ $epic->summary }}</p>
                @endif

                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Total Stories</dt>
                        <dd class="font-semibold text-gray-900 dark:text-gray-100 text-lg">{{ $epic->stories->count() }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Done</dt>
                        <dd class="font-semibold text-green-600">{{ $epic->done_story_count }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Progress</dt>
                        <dd>
                            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mt-1">
                                <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ $epic->progress_percent }}%"></div>
                            </div>
                            <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ $epic->progress_percent }}%</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Created</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $epic->created_at->format('M d, Y') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Stories List -->
        <div class="lg:col-span-3">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Stories</h2>
                </div>

                @if ($epic->stories->count() > 0)
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($epic->stories as $story)
                            @php
                                $storyStatusColors = [
                                    'draft' => 'bg-gray-100 text-gray-800',
                                    'ready' => 'bg-blue-100 text-blue-800',
                                    'blocked' => 'bg-red-100 text-red-800',
                                    'in_progress' => 'bg-yellow-100 text-yellow-800',
                                    'done' => 'bg-green-100 text-green-800',
                                    'archived' => 'bg-gray-100 text-gray-600',
                                ];
                            @endphp
                            <li class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('stories.show', $story) }}" 
                                               class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 truncate">
                                                {{ $story->title }}
                                            </a>
                                            @if ($story->est_points)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                                    {{ $story->est_points }} pts
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">
                                            {{ Str::limit($story->narrative, 120) }}
                                        </p>
                                        @if ($story->persona)
                                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                                👤 {{ $story->persona->name }}
                                            </p>
                                        @endif
                                    </div>
                                    <span class="ml-4 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $storyStatusColors[$story->status?->key] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ $story->status?->name ?? 'Draft' }}
                                    </span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="px-6 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No stories in this epic</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by adding a story.</p>
                        <div class="mt-6">
                            <a href="{{ route('stories.create', ['epic_id' => $epic->id]) }}" 
                               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                + Add Story
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
