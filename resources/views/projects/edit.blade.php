@extends('layouts.app')

@section('title', 'Edit Project')

@section('header')
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Edit Project</h1>
@endsection

@section('content')
    <div class="max-w-3xl bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <form method="POST" action="{{ route('projects.update', $project) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="rounded-md border border-indigo-200 bg-indigo-50 p-4 text-sm text-indigo-900">
                Canonical project metadata is managed in Projects.elasticgun.com.
                This screen only updates per-repository Git URLs used by WritersRoom tooling.
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                <input id="name"
                       type="text"
                       value="{{ $project->name }}"
                       readonly
                       class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <textarea id="description"
                          rows="4"
                          readonly
                          class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $project->description }}</textarea>
            </div>

            <div>
                <label for="local_location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Local Location</label>
                <input id="local_location"
                       type="text"
                       value="{{ $project->local_location }}"
                       readonly
                       class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label for="github_repo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Github Repo</label>
                <input id="github_repo"
                       type="text"
                       value="{{ $project->github_repo }}"
                       readonly
                       class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label for="gitea_location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Git-teaLocation</label>
                <input id="gitea_location"
                       type="text"
                       value="{{ $project->gitea_location }}"
                       readonly
                       class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>


            <div>
                <label for="languages" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Languages</label>
                <input id="languages"
                       type="text"
                       value="{{ $project->languages }}"
                       readonly
                       class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Repository Git Links</h2>

                @forelse($project->repositories as $repository)
                    <div class="mb-4 rounded-md border border-gray-200 dark:border-gray-700 p-4">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $repository->name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ $repository->path }}</div>

                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Git Repository URL
                        </label>
                        <input
                            type="text"
                            name="repositories[{{ $repository->id }}][git_repo_url]"
                            value="{{ old('repositories.' . $repository->id . '.git_repo_url', $repository->git_repo_url) }}"
                            placeholder="https://github.com/org/repo or https://gitea.example.com/org/repo"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >

                        @error('repositories.' . $repository->id . '.git_repo_url')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">No repositories available for this project.</p>
                @endforelse
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('projects.show', $project) }}"
                   class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 text-sm font-medium">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    Save Git Links
                </button>
            </div>
        </form>
    </div>
@endsection
