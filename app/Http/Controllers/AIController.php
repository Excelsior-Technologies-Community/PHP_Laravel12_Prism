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

            // ✅ SAVE TO DATABASE
            Chat::create([
                'question' => $request->question,
                'answer'   => $answerText,
            ]);

            // ✅ LOAD HISTORY
            $chats = Chat::latest()->take(10)->get();

            return view('ai', [
                'answer' => $answerText,
                'chats'  => $chats, // ✅ ADDED
            ]);

        } catch (PrismException $e) {

            $chats = Chat::latest()->take(10)->get(); // ✅ still show history

            return view('ai', [
                'answer' => 'AI service temporarily unavailable. Please try again later.',
                'chats'  => $chats,
            ]);
        }
    }
}
