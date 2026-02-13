@extends('layouts.app')

@section('title', 'Create Persona')

@section('header')
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Create Persona</h1>
@endsection

@section('content')
    <div class="max-w-2xl">
        <form method="POST" action="{{ route('personas.store') }}" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            @csrf

            <div class="space-y-6">
                <!-- Key -->
                <div>
                    <label for="key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Key <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="key" id="key" value="{{ old('key') }}" required
                           placeholder="e.g., junior_dev, admin_user"
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Unique identifier (snake_case recommended)</p>
                    @error('key')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           placeholder="e.g., Junior Developer"
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Summary -->
                <div>
                    <label for="summary" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Summary
                    </label>
                    <input type="text" name="summary" id="summary" value="{{ old('summary') }}"
                           placeholder="Brief description of this persona"
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('summary')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Details -->
                <div>
                    <label for="details" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Details
                    </label>
                    <textarea name="details" id="details" rows="6"
                              placeholder="Goals, frustrations, environment... (Markdown supported)"
                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('details') }}</textarea>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Markdown formatting supported</p>
                    @error('details')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Is Active -->
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" 
                           {{ old('is_active', true) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <label for="is_active" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                        Active
                    </label>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('personas.index') }}" 
                   class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Create Persona
                </button>
            </div>
        </form>
    </div>
@endsection
