<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Prism\Prism\Prism;
use Prism\Prism\Exceptions\PrismException;
use App\Models\Chat;
use Barryvdh\DomPDF\Facade\Pdf;

class AIController extends Controller
{
    protected Prism $prism;

    public function __construct(Prism $prism)
    {
        $this->prism = $prism;
    }

    // SHOW PAGE
    public function index()
    {
        $chats = Chat::latest()->get(); // remove limit for testing
        return view('ai', compact('chats'));
    }

    // ASK AI
    public function ask(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:500',
        ]);

        try {
            $response = $this->prism->text()
                ->using('openrouter', 'openrouter/auto')
                ->withPrompt("Answer in only 2-3 lines: " . $request->question)
                ->generate();
            $answerText = $response->text ?? 'No response';

            Chat::create([
                'question' => $request->question,
                'answer' => $answerText,
            ]);

        } catch (PrismException $e) {
            Chat::create([
                'question' => $request->question,
                'answer' => 'AI service error. Try again.',
            ]);
        }

        return redirect()->back()->with('success', '✅ Chat added successfully!'); // ✅ IMPORTANT FIX
    }

    // SEARCH
    public function search(Request $request)
    {
        $query = $request->input('query'); // ✅ FIXED

        $chats = Chat::where('question', 'like', "%$query%")
            ->latest()
            ->get();

        return view('ai', compact('chats'));
    }

    // DELETE SINGLE
    public function delete($id)
    {
        Chat::findOrFail($id)->delete();

        return redirect('/ai')->with('success', 'Chat deleted successfully!');
    }

    // CLEAR ALL
    public function clearAll()
    {
        Chat::truncate();

        return back()->with('success', '🔥 All chats cleared successfully!');
    }

    // EXPORT PDF
    public function exportPdf()
    {
        $chats = Chat::latest()->get();

        $pdf = Pdf::loadView('pdf.chat', compact('chats'));

        return $pdf->download('chat-history.pdf');
    }
}