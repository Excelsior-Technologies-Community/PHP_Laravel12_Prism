<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>AI Workspace</title>

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #020617, #0f172a);
            color: white;
        }

        /* CONTAINER */
        .container {
            width: 850px;
            margin: 50px auto;
            background: #1e293b;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6);
        }

        /* HEADER */
        h1 {
            text-align: center;
            margin-bottom: 5px;
            font-size: 28px;
        }

        .subtitle {
            text-align: center;
            color: #94a3b8;
            margin-bottom: 30px;
        }

        /* SEARCH + ACTION */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .search-box {
            width: 55%;
        }

        .search-box input {
            width: 100%;
            padding: 10px 12px;
            border-radius: 12px;
            border: none;
            background: #334155;
            color: white;
            outline: none;
        }

        .search-box input:focus {
            box-shadow: 0 0 0 2px #6366f1;
        }

        /* BUTTON GROUP */
        .actions {
            display: flex;
            gap: 10px;
        }

        /* BUTTONS */
        .btn {
            padding: 10px 16px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            color: white;
            font-size: 14px;
            font-weight: 500;
        }

        .red {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .green {
            background: linear-gradient(135deg, #22c55e, #16a34a);
        }

        .blue {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
        }

        .btn:hover {
            transform: translateY(-1px);
            opacity: 0.95;
        }

        /* CHAT AREA */
        .chat-box {
            max-height: 350px;
            overflow-y: auto;
            padding-right: 5px;
        }

        /* CHAT CARD */
        .chat-card {
            background: linear-gradient(145deg, #334155, #1e293b);
            padding: 15px;
            border-radius: 14px;
            margin-bottom: 15px;
            border-left: 4px solid #6366f1;
            transition: 0.3s;
        }

        .chat-card:hover {
            transform: scale(1.01);
        }

        /* TEXT */
        .label {
            font-size: 12px;
            color: #94a3b8;
            margin-bottom: 2px;
        }

        .question {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 8px;
        }

        .answer {
            color: #e2e8f0;
            line-height: 1.4;
        }

        /* DELETE */
        .delete {
            color: #f87171;
            font-size: 12px;
            margin-top: 10px;
            background: none;
            border: none;
            cursor: pointer;
        }

        .delete:hover {
            text-decoration: underline;
        }

        /* INPUT */
        .bottom {
            margin-top: 25px;
            display: flex;
            gap: 10px;
        }

        .bottom input {
            flex: 1;
            padding: 12px;
            border-radius: 12px;
            border: none;
            background: #334155;
            color: white;
            outline: none;
        }

        .bottom input:focus {
            box-shadow: 0 0 0 2px #6366f1;
        }

        /* LOADING */
        #loading {
            display: none;
            text-align: center;
            color: #818cf8;
            margin-top: 15px;
        }

        /* EMPTY */
        .empty {
            text-align: center;
            color: #94a3b8;
            padding: 20px;
        }

        /* MODAL */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            justify-content: center;
            align-items: center;
        }

        .modal-box {
            background: #1e293b;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            width: 300px;
        }

        .cancel {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }

        .success-msg {
            background: #16a34a;
            color: white;
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 15px;
            text-align: center;
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-box h3 {
            margin-bottom: 10px;
        }

        .modal-box p {
            color: #94a3b8;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <div class="container">

        <h1>✨ AI Workspace</h1>
        <div class="subtitle">Ask anything & manage your conversations</div>

        @if(session('success'))
            <div class="success-msg">
                {{ session('success') }}
            </div>
        @endif
        <!-- TOP BAR -->
        <div class="top-bar">

            <form method="GET" action="/chat/search" class="search-box">
                <input type="text" name="query" placeholder="🔍 Search conversations...">
            </form>

            <div class="actions">
                <form method="POST" action="/chat-clear"
                    onsubmit="return confirm('Are you sure you want to clear all chats?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn red">Clear</button>
                </form>
                <a href="/chat-export" class="btn green">PDF</a>
            </div>

        </div>

        <!-- CHAT -->
        <div class="chat-box">

            @forelse($chats as $chat)
                <div class="chat-card">

                    <div class="label">Question</div>
                    <div class="question">{{ $chat->question }}</div>

                    <div class="label">Answer</div>
                    <div class="answer">{{ $chat->answer }}</div>

                    <!-- DELETE BUTTON -->
                    <button class="delete" onclick="openModal({{ $chat->id }})">
                        🗑 Delete
                    </button>

                </div>
            @empty
                <div class="empty">No conversations yet</div>
            @endforelse

        </div>

        <!-- LOADING -->
        <div id="loading">⚡ AI is thinking...</div>

        <!-- INPUT -->
        <form method="POST" action="/ask-ai" class="bottom">
            @csrf
            <input type="text" name="question" placeholder="💬 Ask something..." required>
            <button class="btn blue">Ask</button>
        </form>

    </div>

    <!-- DELETE MODAL -->
    <div id="deleteModal" class="modal">
        <div class="modal-box">
            <h3>Delete Chat?</h3>
            <p>Are you sure you want to delete this conversation?</p>

            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')

                <button type="submit" class="btn red">Yes, Delete</button>
                <button type="button" class="btn cancel" onclick="closeModal()">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        // LOADING
        document.querySelector(".bottom").addEventListener("submit", function () {
            document.getElementById("loading").style.display = "block";
        });

        // MODAL FUNCTIONS
        function openModal(id) {
            document.getElementById('deleteModal').style.display = 'flex';
            document.getElementById('deleteForm').action = '/chat/' + id;
        }

        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
    </script>

</body>

</html>