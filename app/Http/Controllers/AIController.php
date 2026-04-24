<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Prism\Prism\Prism;
use App\Models\Chat;
use Barryvdh\DomPDF\Facade\Pdf;
use thiagoalessio\TesseractOCR\TesseractOCR;

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
        $chats = Chat::latest()->get();
        return view('ai', compact('chats'));
    }

    // ASK AI (TEXT + IMAGE FIXED)
    public function ask(Request $request)
    {
        $request->validate([
            'question' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        $imagePath = null;
        $answerText = '';

        try {

            $finalText = $request->question ?? '';

            // ✅ IF IMAGE UPLOADED → USE OCR
            if ($request->hasFile('image')) {

                $imagePath = $request->file('image')->store('chat-images', 'public');

                $fullPath = storage_path('app/public/' . $imagePath);

                // OCR (READ IMAGE TEXT)
                $ocrText = (new TesseractOCR($fullPath))->run();

                $finalText .= "\n\nImage Text (OCR): " . $ocrText;
            }

            // ❗ If nothing provided
            if (empty(trim($finalText))) {
                return back()->with('error', 'Please enter question or upload image');
            }

            // AI PROMPT
            $prompt = "Solve this problem and give ONLY final answer:\n\n" . $finalText;

            // ✅ OPENROUTER CALL (CORRECT MODEL)
            $response = $this->prism->text()
                ->using('openrouter', 'openai/gpt-4o-mini')
                ->withPrompt($prompt)
                ->generate();

            $answerText = trim($response->text ?? 'No response');

        } catch (\Exception $e) {
            $answerText = 'AI error: ' . $e->getMessage();
        }

        // SAVE CHAT
        Chat::create([
            'question' => $request->question ?? 'Image Input',
            'answer' => $answerText,
            'image' => $imagePath
        ]);

        return back()->with('success', '✅ Solution generated!');
    }

    // SEARCH
    public function search(Request $request)
    {
        $query = $request->input('query');

        $chats = Chat::where('question', 'like', "%$query%")
            ->latest()
            ->get();

        return view('ai', compact('chats'));
    }

    // DELETE
    public function delete($id)
    {
        Chat::findOrFail($id)->delete();
        return back()->with('success', 'Chat deleted!');
    }

    // CLEAR ALL
    public function clearAll()
    {
        Chat::truncate();
        return back()->with('success', '🔥 All chats cleared!');
    }

    // EXPORT PDF
    public function exportPdf()
    {
        $chats = Chat::latest()->get();
        $pdf = Pdf::loadView('pdf.chat', compact('chats'));
        return $pdf->download('chat-history.pdf');
    }
}