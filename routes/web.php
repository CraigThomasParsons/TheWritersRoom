<?php

use App\Http\Controllers\PersonaController;
use App\Http\Controllers\EpicController;
use App\Http\Controllers\LlmModelController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\StoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('stories.index');
});

// Personas
Route::resource('personas', PersonaController::class);

// Projects
Route::resource('projects', ProjectController::class)
    ->only(['index', 'show', 'edit', 'update']);

// Epics
Route::resource('epics', EpicController::class);

// Stories
Route::resource('stories', StoryController::class);
Route::post('/stories/{story}/mark-ready', [StoryController::class, 'markReady'])->name('stories.mark-ready');
Route::post('/stories/{story}/comments', [StoryController::class, 'storeComment'])->name('stories.comments.store');

// LLM Models
Route::get('/llm-models', [LlmModelController::class, 'index'])->name('llm-models.index');
Route::post('/llm-models/priorities', [LlmModelController::class, 'updatePriorities'])->name('llm-models.update-priorities');
