<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ClickUpController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\WebhookNotifyController;
use App\Http\Controllers\WebhookVoiceController;

Route::get('/', fn () => Inertia::render('Welcome'))->name('home');

Route::get('dashboard', fn () => Inertia::render('Dashboard'))->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';

Route::post('/subscribe', [MainController::class, 'subscribe'])->name('subscribe');
Route::post('/subscribe/sergeyem', [MainController::class, 'subscribeSergey'])->name('subscribe.sergeyem');
Route::post('/meeting', [MainController::class, 'meeting'])->name('meeting');
Route::post('/webhooks/clickup', [ClickUpController::class, 'webhook'])->name('webhooks.clickup');
Route::post('/webhooks/notify', WebhookNotifyController::class)->name('webhooks.notify');
Route::post('/webhooks/voice', WebhookVoiceController::class)->name('webhooks.voice');
