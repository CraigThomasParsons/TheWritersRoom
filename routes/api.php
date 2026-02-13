<?php

use App\Http\Controllers\Api\EpicController;
use App\Http\Controllers\Api\SprintController;
use App\Http\Controllers\Api\StoryController;
use Illuminate\Support\Facades\Route;

Route::apiResource('epics', EpicController::class);
Route::apiResource('stories', StoryController::class);
Route::apiResource('sprints', SprintController::class);
