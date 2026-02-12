<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AIController;
use App\Models\Chat; 

Route::get('/ai-text', [AIController::class, 'text']);
Route::get('/ai-chat', [AIController::class, 'chat']);

Route::get('/ai', function () {
    $chats = Chat::latest()->take(10)->get(); 
    return view('ai', compact('chats'));
});

Route::post('/ask-ai', [AIController::class, 'ask']);


Route::get('/', function () {
    return view('welcome');
});
