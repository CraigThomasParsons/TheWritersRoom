<?php

use App\Http\Controllers\Api\EpicController;
use App\Http\Controllers\Api\LlmModelController;
use App\Http\Controllers\Api\PiperController;
use App\Http\Controllers\Api\ProjectProjectionSyncController;
use App\Http\Controllers\Api\SprintController;
use App\Http\Controllers\Api\StoryController;
use Illuminate\Support\Facades\Route;

Route::apiResource('epics', EpicController::class);
Route::apiResource('stories', StoryController::class);
Route::apiResource('sprints', SprintController::class);

// Piper machine-to-machine endpoints for project analysis and draft generation.
// Uses {projectId} instead of {project} to avoid 404 on missing projects —
// Piper auto-creates the project record on first write.
Route::prefix('piper')->group(function () {
    Route::get('/projects/{projectId}/input', [PiperController::class, 'projectInput']);
    Route::post('/projects/{projectId}/analysis', [PiperController::class, 'storeAnalysis']);
    Route::post('/projects/{projectId}/epics-stories', [PiperController::class, 'buildEpicsAndStories']);
    Route::post('/stories/{storyId}/comments', [PiperController::class, 'storeStoryComment']);
    Route::get('/llm-models', [LlmModelController::class, 'index']);
});

// Canonical Projects registry webhook for upsert/delete projection events.
Route::post('/projects/projection-sync', [ProjectProjectionSyncController::class, 'store']);
