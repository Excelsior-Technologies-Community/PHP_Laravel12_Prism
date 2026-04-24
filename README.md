# PHP_Laravel12_Prism

## Project Introduction

PHP_Laravel12_Prism is a beginner-friendly demonstration project that shows how to integrate the Prism PHP AI SDK with a Laravel 12 application to communicate with modern AI language models (LLMs) such as OpenAI, OpenRouter, Anthropic, and more.

This project provides a clean foundation for building AI-powered Laravel applications like chat assistants, text generators, and smart productivity tools.

------------------------------------------------------------------------

## Project Overview

This project shows how to install, configure, and use Prism in Laravel 12 to generate AI-based responses and store chat history in a database.

It includes:

- Prism installation and configuration in Laravel 12

- Integration with a free OpenRouter AI provider using environment variables

- A structured AIController for text generation, chat responses, and user questions

- A clean Tailwind CSS Blade interface for real-time AI interaction

- Database storage of questions and AI answers with recent chat history display

- Basic error handling for stable AI communication

------------------------------------------------------------------------

## Key Features 

-   Laravel 12 setup
-   Prism installation
-   OpenRouter integration
-   Text generation
-   Chat conversations
-   Controller‑based architecture
-   Blade UI interaction

------------------------------------------------------------------------

## Requirements

- PHP 8.2+

- Composer

- Laravel 12

- MySQL

- OpenRouter free API key

------------------------------------------------------------------------

## Step 1 --- Create Laravel 12 Project

Run the official Laravel 12 installation command:

``` bash
composer create-project laravel/laravel PHP_Laravel12_Prism "12.*"
```

Move into the project:

``` bash
cd PHP_Laravel12_Prism
```

Run the development server:

``` bash
php artisan serve
```

------------------------------------------------------------------------

## Step 2 --- Install Prism Package

Install Prism via Composer:

``` bash
composer require prism-php/prism
```

------------------------------------------------------------------------

## Step 3 --- Publish Configuration

Publish the Prism config file:

``` bash
php artisan vendor:publish --tag=prism-config
```

This creates:

    config/prism.php

update this line:

```php
'request_timeout' => env('PRISM_REQUEST_TIMEOUT', 120), // 120 seconds instead of 30.
```

------------------------------------------------------------------------

## Step 4 — Configure Database

Update .env:

```.env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel12_prism
DB_USERNAME=root
DB_PASSWORD=
```
Create database using below command:

```bash
php artisan migrate
```

------------------------------------------------------------------------

## Step 5 --- Environment Setup

###  5.1) Get FREE API key

Go here:

```bash
https://openrouter.ai/keys
```

- Login with Google/GitHub

- Click Create Key

- Copy key like:

```bash
sk-or-v1-xxxxxxxx
```

### 5.2) Add your **OpenRouter API Key** in `.env`:

```.env
PRISM_PROVIDER=openrouter
OPENROUTER_API_KEY=sk-or-v1-xxxxxxxxxxxxxxxx
OPENROUTER_URL=https://openrouter.ai/api/v1
OPENROUTER_SITE_HTTP_REFERER=http://127.0.0.1:8000
OPENROUTER_SITE_X_TITLE="Laravel Prism AI"

PRISM_REQUEST_TIMEOUT=120
```
------------------------------------------------------------------------

## Step 6 — Create Chat Model & Migration

```bash
php artisan make:model Chat -m
```

### 6.1) Migration Table

File: database/migrations/2026_02_12_063029_create_chats_table.php

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->longText('answer')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
```

### 6.2) Model

File: app/Models/Chat.php

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = [
        'question',
        'answer',
    ];
}
```

------------------------------------------------------------------------


## Step 7 --- Controller Implementation

Create controller:

``` bash
php artisan make:controller AIController
```

### app/Http/Controllers/AIController.php

``` php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Prism\Prism\Prism;
use Prism\Prism\Exceptions\PrismException;
use App\Models\Chat; 

class AIController extends Controller
{
    protected Prism $prism;

    public function __construct(Prism $prism)
    {
        $this->prism = $prism;
    }

    /**
     * Simple text generation test
     */
    public function text()
    {
        try {
            $response = $this->prism->text()
                ->using('openrouter', 'openrouter/auto')
                ->withPrompt('Give a short productivity tip.')
                ->generate();

            return $response->text ?? 'No response received.';
        } catch (PrismException $e) {
            return 'AI Error: ' . $e->getMessage();
        }
    }

    /**
     * Example question
     */
    public function chat()
    {
        try {
            $response = $this->prism->text()
                ->using('openrouter', 'openrouter/auto')
                ->withPrompt('What is MVC in Laravel?')
                ->generate();

            return $response->text ?? 'No response received.';
        } catch (PrismException $e) {
            return 'AI Error: ' . $e->getMessage();
        }
    }

    /**
     * Ask from Blade form
     */
    public function ask(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:500',
        ]);

        try {
            $response = $this->prism->text()
                ->using('openrouter', 'openrouter/auto')
                ->withPrompt($request->question)
                ->generate();

            $answerText = $response->text ?? 'No response received.';

            //  SAVE TO DATABASE
            Chat::create([
                'question' => $request->question,
                'answer'   => $answerText,
            ]);

            //  LOAD HISTORY
            $chats = Chat::latest()->take(10)->get();

            return view('ai', [
                'answer' => $answerText,
                'chats'  => $chats, //  ADDED
            ]);

        } catch (PrismException $e) {

            $chats = Chat::latest()->take(10)->get(); //  still show history

            return view('ai', [
                'answer' => 'AI service temporarily unavailable. Please try again later.',
                'chats'  => $chats,
            ]);
        }
    }
}
```

------------------------------------------------------------------------

## Step 8 --- Routes for Controller

### routes/web.php

``` php
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
```

------------------------------------------------------------------------

## Step 9 --- Blade UI Example

### resources/views/ai.blade.php

``` blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ask AI - Laravel Prism</title>

    <!-- Tailwind CSS CDN (latest 2026 design) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: #f8fafc;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">

    <div class="w-full max-w-xl bg-white rounded-lg shadow-lg p-8 space-y-6">

        <h1 class="text-3xl font-bold text-center text-indigo-600">Ask AI Anything</h1>

        <p class="text-center text-gray-500">Powered by Laravel Prism + OpenRouter</p>

        <!-- Ask Question Form -->
        <form method="POST" action="/ask-ai" class="space-y-4">
            @csrf

            <input type="text" name="question"
                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:border-indigo-500 focus:ring-indigo-200 transition"
                placeholder="Type your question here..."
                required>

            <button type="submit"
                class="w-full bg-indigo-600 text-white font-semibold px-4 py-3 rounded-lg hover:bg-indigo-700 transition">
                Ask AI
            </button>
        </form>

        <!-- Latest AI Answer -->
        @isset($answer)
            <div class="mt-6 bg-gray-50 border-l-4 border-indigo-500 p-4 rounded">
                <h2 class="text-xl font-semibold text-indigo-600">AI Answer:</h2>
                <p class="mt-2 text-gray-800 whitespace-pre-line">{{ $answer }}</p>
            </div>
        @endisset

        <!-- Chat History from Database -->
        @isset($chats)
            <div class="mt-6">
                <h2 class="text-lg font-semibold text-gray-700 mb-3">Recent Questions</h2>

                <div class="space-y-3 max-h-64 overflow-y-auto pr-2">
                    @forelse($chats as $chat)
                        <div class="bg-gray-50 border rounded-lg p-3">
                            <p class="text-sm text-gray-500">Q:</p>
                            <p class="font-medium text-gray-800">{{ $chat->question }}</p>

                            @if($chat->answer)
                                <p class="text-sm text-gray-500 mt-2">AI:</p>
                                <p class="text-gray-700 whitespace-pre-line">{{ $chat->answer }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">No chat history yet.</p>
                    @endforelse
                </div>
            </div>
        @endisset

        <footer class="text-center text-sm text-gray-400 mt-4">
            Made with 💡 Laravel Prism • © 2026
        </footer>

    </div>

</body>
</html>
```

------------------------------------------------------------------------

## Step 10: Run Development Server

Run:

```bash
php artisan serve
```
Then open:

```bash
http://127.0.0.1:8000/ai
```
------------------------------------------------------------------------

## Output

<img width="1919" height="1029" alt="Screenshot 2026-02-12 123656" src="https://github.com/user-attachments/assets/67ec0c1f-4a81-4d48-ac2d-74897b111f49" />

------------------------------------------------------------------------

## Project Folder Structure

```
PHP_Laravel12_Prism
│
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── AIController.php
│   │
│   └── Models/
│       └── Chat.php              
│
├── config/
│   └── prism.php
│
├── database/
│   └── migrations/
│       └── xxxx_xx_xx_create_chats_table.php  
│
├── resources/
│   └── views/
│       └── ai.blade.php
│
├── routes/
│   └── web.php
│
├── .env
└── composer.json
```

------------------------------------------------------------------------


You have successfully built a **PHP_Laravel12_Prism** project.
<<<<<<< HEAD


=======
>>>>>>> development
