<?php

use App\Http\Controllers\Admin\Projects\ProjectWizardController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'projects'], function () {
    // Wizard Step 1: Basics
    Route::get('create', [ProjectWizardController::class, 'showBasicsStep'])->name('projects.create');
    Route::get('{project}/edit', [ProjectWizardController::class, 'showBasicsStep'])->name('projects.edit');

    Route::group(['prefix' => 'steps'], function () {
        Route::get('basics/{project?}', [ProjectWizardController::class, 'showBasicsStep'])->name('projects.steps.basics');
        Route::post('basics/{project?}', [ProjectWizardController::class, 'storeBasicsStep'])->name('projects.steps.basics.store');
    });
});
