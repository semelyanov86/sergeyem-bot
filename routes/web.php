<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Welcome'))->name('home');

Route::get('dashboard', fn () => Inertia::render('Dashboard'))->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';

Route::post('/subscribe', [\App\Http\Controllers\MainController::class, 'subscribe'])->name('subscribe');
Route::post('/subscribe/sergeyem', [\App\Http\Controllers\MainController::class, 'subscribeSergey'])->name('subscribe.sergeyem');
Route::post('/meeting', [\App\Http\Controllers\MainController::class, 'meeting'])->name('meeting');
Route::post('/webhooks/clickup', [\App\Http\Controllers\ClickUpController::class, 'webhook'])->name('webhooks.clickup');
