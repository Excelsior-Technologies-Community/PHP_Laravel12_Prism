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
