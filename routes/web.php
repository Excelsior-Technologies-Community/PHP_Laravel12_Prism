<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AIController;

/*
|--------------------------------------------------------------------------
| AI Routes
|--------------------------------------------------------------------------
*/

// Optional test routes (you can keep or remove)
Route::get('/ai-text', [AIController::class, 'text']);
Route::get('/ai-chat', [AIController::class, 'chat']);

// MAIN AI PAGE (UPDATED → now using controller instead of closure)
Route::get('/ai', [AIController::class, 'index']);

// ASK AI
Route::post('/ask-ai', [AIController::class, 'ask']);

// SEARCH CHAT
Route::get('/chat/search', [AIController::class, 'search']);

// DELETE SINGLE CHAT
Route::delete('/chat/{id}', [AIController::class, 'delete']);

// CLEAR ALL CHAT
Route::delete('/chat-clear', [AIController::class, 'clearAll']);

// EXPORT PDF
Route::get('/chat-export', [AIController::class, 'exportPdf']);


/*
|--------------------------------------------------------------------------
| Default Route
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});