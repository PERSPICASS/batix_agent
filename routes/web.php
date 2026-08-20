<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\FacebookPostController;
use App\Http\Controllers\GrowthController;
use App\Http\Controllers\GrowthLoginController;
use App\Http\Controllers\WhatsAppMessageController;
use App\Http\Controllers\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [GrowthLoginController::class, 'create'])->name('growth.login');
Route::post('/login', [GrowthLoginController::class, 'store'])->name('growth.login.store');
Route::get('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'verify'])->name('whatsapp.webhook.verify');
Route::post('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'receive'])->name('whatsapp.webhook.receive');

Route::middleware('growth.auth')->group(function () {
    Route::get('/', [GrowthController::class, 'index'])->name('growth.index');
    Route::get('/campaigns', [GrowthController::class, 'campaigns'])->name('campaigns.index');
    Route::get('/contents', [GrowthController::class, 'contents'])->name('contents.index');
    Route::get('/leads', [GrowthController::class, 'leads'])->name('leads.index');
    Route::get('/admins', [AdminUserController::class, 'index'])->name('admins.index');

    Route::post('/campaigns', [GrowthController::class, 'storeCampaign'])->name('campaigns.store');
    Route::post('/campaigns/{campaign}/generate', [GrowthController::class, 'generate'])->name('campaigns.generate');
    Route::patch('/campaigns/{campaign}', [GrowthController::class, 'updateCampaign'])->name('campaigns.update');
    Route::post('/leads', [GrowthController::class, 'storeLead'])->name('leads.store');
    Route::post('/leads/{lead}/score', [GrowthController::class, 'score'])->name('leads.score');
    Route::patch('/leads/{lead}/status', [GrowthController::class, 'updateLeadStatus'])->name('leads.status');
    Route::post('/leads/{lead}/interactions', [GrowthController::class, 'storeLeadInteraction'])->name('leads.interactions.store');
    Route::post('/leads/{lead}/whatsapp', [WhatsAppMessageController::class, 'store'])->name('leads.whatsapp.store');
    Route::patch('/contents/{content}/status', [GrowthController::class, 'updateContentStatus'])->name('contents.status');
    Route::post('/facebook-posts', [FacebookPostController::class, 'store'])->name('facebook-posts.store');
    Route::post('/contents/{content}/facebook-publish', [FacebookPostController::class, 'publish'])->name('contents.facebook.publish');
    Route::post('/admins', [AdminUserController::class, 'store'])->name('admins.store');
    Route::patch('/admins/{admin}', [AdminUserController::class, 'update'])->name('admins.update');
    Route::post('/logout', [GrowthLoginController::class, 'destroy'])->name('growth.logout');
});
