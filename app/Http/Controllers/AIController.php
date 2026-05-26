<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\SystemMessage;
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

    public function index()
    {
        $chats = Chat::latest()->get();
        return view('ai', compact('chats'));
    }

    public function ask(Request $request)
    {
        $request->validate([
            'question' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        $imagePath = null;
        $answerText = '';
        $finalText = $request->question ?? '';

        try {
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('chat-images', 'public');
                $fullPath = storage_path('app/public/' . $imagePath);
                $ocrText = (new TesseractOCR($fullPath))
            ->executable('C:\Program Files\Tesseract-OCR\tesseract.exe')
            ->run();
                $finalText .= "\n\nImage Text (OCR): " . $ocrText;
            }

            if (empty(trim($finalText))) {
                return response()->json(['answer' => 'Please enter question or upload image'], 400);
            }

            $history = Chat::latest()->take(10)->get()->reverse();

            $messages = [];
            $messages[] = new SystemMessage("Solve this problem and give ONLY final answer. Always remember the context of the conversation.");

            foreach ($history as $chat) {
                if ($chat->question !== 'Image Input' && $chat->question !== 'Image only') {
                    $messages[] = new UserMessage($chat->question);
                }
                if ($chat->answer) {
                    $messages[] = new AssistantMessage($chat->answer);
                }
            }

            $messages[] = new UserMessage($finalText);

            $response = $this->prism->text()
                ->using('openrouter', 'openai/gpt-4o-mini')
                ->withMessages($messages)
                ->generate();

            $answerText = trim($response->text ?? 'No response');

        } catch (\Exception $e) {
            $answerText = 'AI error: ' . $e->getMessage();
        }

        $chat = Chat::create([
            'question' => $request->question ?? 'Image Input',
            'answer' => $answerText,
            'image' => $imagePath
        ]);

        return response()->json([
            'answer' => $answerText,
            'chat_id' => $chat->id
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        $chats = Chat::where('question', 'like', "%$query%")
            ->latest()
            ->get();

        return view('ai', compact('chats'));
    }

    public function delete($id)
    {
        Chat::findOrFail($id)->delete();
        return back()->with('success', 'Chat deleted!');
    }

    public function clearAll()
    {
        Chat::truncate();
        return back()->with('success', 'All chats cleared!');
    }

    public function exportPdf()
    {
        $chats = Chat::latest()->get();
        $pdf = Pdf::loadView('pdf.chat', compact('chats'));
        return $pdf->download('chat-history.pdf');
    }
}