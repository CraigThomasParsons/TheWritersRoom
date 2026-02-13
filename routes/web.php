<?php

use App\Http\Controllers\PersonaController;
use App\Http\Controllers\EpicController;
use App\Http\Controllers\StoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('stories.index');
});

// Personas
Route::resource('personas', PersonaController::class);

// Epics
Route::resource('epics', EpicController::class);

// Stories
Route::resource('stories', StoryController::class);
Route::post('/stories/{story}/mark-ready', [StoryController::class, 'markReady'])->name('stories.mark-ready');

