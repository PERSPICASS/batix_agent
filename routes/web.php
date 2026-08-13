<?php

use App\Http\Controllers\GrowthController;
use Illuminate\Support\Facades\Route;

Route::middleware('growth.auth')->group(function () {
    Route::get('/', [GrowthController::class, 'index'])->name('growth.index');
    Route::post('/campaigns', [GrowthController::class, 'storeCampaign'])->name('campaigns.store');
    Route::post('/campaigns/{campaign}/generate', [GrowthController::class, 'generate'])->name('campaigns.generate');
    Route::post('/leads', [GrowthController::class, 'storeLead'])->name('leads.store');
    Route::post('/leads/{lead}/score', [GrowthController::class, 'score'])->name('leads.score');
    Route::patch('/contents/{content}/status', [GrowthController::class, 'updateContentStatus'])->name('contents.status');
});
