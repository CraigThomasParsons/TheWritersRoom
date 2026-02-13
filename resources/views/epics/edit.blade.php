@extends('layouts.app')

@section('title', 'Edit Epic')

@section('header')
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Edit Epic: {{ $epic->title }}</h1>
@endsection

@section('content')
    <div class="max-w-2xl">
        <form method="POST" action="{{ route('epics.update', $epic) }}" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" id="title" value="{{ old('title', $epic->title) }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Summary -->
                <div>
                    <label for="summary" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Summary
                    </label>
                    <textarea name="summary" id="summary" rows="4"
                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('summary', $epic->summary) }}</textarea>
                    @error('summary')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="epic_status_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="epic_status_id" id="epic_status_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach ($statuses as $status)
                            <option value="{{ $status->id }}" {{ old('epic_status_id', $epic->epic_status_id) == $status->id ? 'selected' : '' }}>
                                {{ $status->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('epic_status_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-between">
                <form method="POST" action="{{ route('epics.destroy', $epic) }}" 
                      onsubmit="return confirm('Are you sure? This will also affect stories in this epic.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                        Delete Epic
                    </button>
                </form>

                <div class="flex gap-3">
                    <a href="{{ route('epics.index') }}" 
                       class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Update Epic
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
