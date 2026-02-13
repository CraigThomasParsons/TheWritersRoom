@extends('layouts.app')

@section('title', 'Epics')

@section('header')
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Epics</h1>
        <a href="{{ route('epics.create') }}" 
           class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            + New Epic
        </a>
    </div>
@endsection

@section('content')
    <!-- Search & Filters -->
    <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <form method="GET" action="{{ route('epics.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search epics..."
                       class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                <select name="status" 
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->key }}" {{ request('status') == $status->key ? 'selected' : '' }}>
                            {{ $status->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" 
                    class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">
                Filter
            </button>
            <a href="{{ route('epics.index') }}" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800">
                Clear
            </a>
        </form>
    </div>

    <!-- Epics List -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Epic
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Stories
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Progress
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($epics as $epic)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4">
                            <a href="{{ route('epics.show', $epic) }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800">
                                {{ $epic->title }}
                            </a>
                            @if ($epic->summary)
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    {{ Str::limit($epic->summary, 60) }}
                                </p>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
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
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $epic->stories_count }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ $epic->progress_percent }}%"></div>
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $epic->progress_percent }}%</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('epics.edit', $epic) }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
                                Edit
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No epics</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new epic.</p>
                            <div class="mt-6">
                                <a href="{{ route('epics.create') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                    + New Epic
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $epics->withQueryString()->links() }}
    </div>
@endsection
