@extends('layouts.app')

@section('title', $project->name)

@section('header')
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $project->name }}</h1>
        <a href="{{ route('projects.edit', $project) }}"
           class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
            Edit Project
        </a>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Project Details</h2>

            <dl class="space-y-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $project->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $project->description ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Local Location</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $project->local_location ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Github Repo</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $project->github_repo ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Git-teaLocation</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $project->gitea_location ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Languages</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $project->languages ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Sync</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $project->synced_at?->toDayDateTimeString() ?? 'Never' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Project UUID</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $project->project_uuid ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Source Updated At</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $project->source_updated_at?->toDayDateTimeString() ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Projection Last Synced</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $project->last_synced_at?->toDayDateTimeString() ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Repositories</h2>
            <ul class="space-y-3">
                @forelse ($project->repositories as $repository)
                    <li class="border border-gray-200 dark:border-gray-700 rounded-md p-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $repository->name }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $repository->role ?: 'repo' }}</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $repository->path }}</p>
                        @if ($repository->git_repo_url)
                            <p class="text-xs mt-1">
                                <a href="{{ $repository->git_repo_url }}" target="_blank" rel="noopener noreferrer" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800">
                                    {{ $repository->git_repo_url }}
                                </a>
                            </p>
                        @endif
                    </li>
                @empty
                    <li class="text-sm text-gray-500 dark:text-gray-400">No repositories linked.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent Epics</h2>
        </div>
        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($project->epics as $epic)
                <li class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <a href="{{ route('epics.show', $epic) }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800">
                            {{ $epic->title }}
                        </a>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $epic->status?->name ?? 'Unknown' }}</p>
                    </div>
                    <a href="{{ route('epics.edit', $epic) }}" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">Edit</a>
                </li>
            @empty
                <li class="px-6 py-6 text-sm text-gray-500 dark:text-gray-400">No epics for this project yet.</li>
            @endforelse
        </ul>
    </div>
@endsection
