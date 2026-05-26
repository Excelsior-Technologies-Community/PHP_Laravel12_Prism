<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AIController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/ai', [AIController::class, 'index']);
Route::post('/ask-ai', [AIController::class, 'ask']);
Route::get('/chat/search', [AIController::class, 'search']);
Route::delete('/chat/{id}', [AIController::class, 'delete']);
Route::delete('/chat-clear', [AIController::class, 'clearAll']);
Route::get('/chat-export', [AIController::class, 'exportPdf']);