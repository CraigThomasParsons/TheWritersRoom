@extends('layouts.app')

@section('title', 'Personas')

@section('header')
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Personas</h1>
        <a href="{{ route('personas.create') }}" 
           class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            + New Persona
        </a>
    </div>
@endsection

@section('content')
    <!-- Search & Filters -->
    <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <form method="GET" action="{{ route('personas.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search personas..."
                       class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="flex items-center gap-2">
                <label class="flex items-center">
                    <input type="checkbox" name="active_only" value="1" {{ request('active_only') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Active only</span>
                </label>
            </div>
            <button type="submit" 
                    class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">
                Filter
            </button>
            <a href="{{ route('personas.index') }}" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800">
                Clear
            </a>
        </form>
    </div>

    <!-- Personas Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($personas as $persona)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ $persona->name }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 font-mono">
                                {{ $persona->key }}
                            </p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $persona->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $persona->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    
                    @if ($persona->summary)
                        <p class="mt-3 text-gray-600 dark:text-gray-300 text-sm">
                            {{ Str::limit($persona->summary, 100) }}
                        </p>
                    @endif

                    <div class="mt-4 flex items-center text-sm text-gray-500 dark:text-gray-400">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        {{ $persona->stories_count }} {{ Str::plural('story', $persona->stories_count) }}
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
                        <a href="{{ route('personas.show', $persona) }}" 
                           class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">
                            View
                        </a>
                        <a href="{{ route('personas.edit', $persona) }}" 
                           class="text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400">
                            Edit
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No personas</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new persona.</p>
                <div class="mt-6">
                    <a href="{{ route('personas.create') }}" 
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        + New Persona
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $personas->withQueryString()->links() }}
    </div>
@endsection
