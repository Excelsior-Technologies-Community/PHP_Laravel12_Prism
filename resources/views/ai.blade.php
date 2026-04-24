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

        .container {
            width: 850px;
            margin: 50px auto;
            background: #1e293b;
            padding: 30px;
            border-radius: 20px;
        }

        h1 { text-align: center; }

        .subtitle {
            text-align: center;
            color: #94a3b8;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 16px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            color: white;
        }

        .red { background: #dc2626; }
        .green { background: #16a34a; }
        .blue { background: #4f46e5; }

        .chat-box {
            max-height: 350px;
            overflow-y: auto;
        }

        .chat-card {
            background: #334155;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .bottom {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }

        .bottom input {
            flex: 1;
            padding: 10px;
            border-radius: 10px;
            border: none;
            background: #334155;
            color: white;
        }

        .success-msg {
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 10px;
            text-align: center;
        }

        .preview-img {
            margin-top: 10px;
            max-width: 150px;
            border-radius: 10px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            justify-content: center;
            align-items: center;
        }

        .modal-box {
            background: #1e293b;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .cancel { background: #3b82f6; }
    </style>
</head>

<body>

<div class="container">

    <h1>✨ AI Workspace</h1>
    <div class="subtitle">Ask anything & manage your conversations</div>

    {{-- ❌ ERROR --}}
    @if ($errors->any())
        <div class="success-msg" style="background:#dc2626;">
            @foreach ($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </div>
    @endif

    {{-- ✅ SUCCESS --}}
    @if(session('success'))
        <div class="success-msg" style="background:#16a34a;">
            {{ session('success') }}
        </div>
    @endif

    <!-- TOP BAR -->
    <div class="top-bar">

        <form method="GET" action="/chat/search">
            <input type="text" name="query" placeholder="🔍 Search...">
        </form>

        <div class="actions">

            <!-- CLEAR -->
            <form method="POST" action="/chat-clear"
                  onsubmit="return confirm('Are you sure?')">
                @csrf
                @method('DELETE')
                <button class="btn red">Clear</button>
            </form>

            <a href="/chat-export" class="btn green">PDF</a>

        </div>
    </div>

    <!-- CHAT LIST -->
    <div class="chat-box">
        @forelse($chats as $chat)

            <div class="chat-card">

                @if($chat->question !== 'Image only')
                    <b>Q:</b> {{ $chat->question }} <br>
                @endif

                <b>A:</b> {{ $chat->answer }}

                @if($chat->image)
                    <br>
                    <img src="{{ asset('storage/'.$chat->image) }}" class="preview-img">
                @endif

                <br><br>

                <button onclick="openModal({{ $chat->id }})" class="btn red">
                    Delete
                </button>

            </div>

        @empty
            <p>No conversations yet</p>
        @endforelse
    </div>

    <!-- LOADING -->
    <div id="loading" style="display:none;">⚡ AI is thinking...</div>

    <!-- INPUT -->
    <form method="POST" action="/ask-ai" enctype="multipart/form-data" class="bottom">
        @csrf
        <input type="text" name="question" placeholder="💬 Ask something...">
        <input type="file" name="image" onchange="previewImage(event)">
        <button class="btn blue">Ask</button>
    </form>

    <img id="preview" class="preview-img" style="display:none;">

</div>

<!-- DELETE MODAL -->
<div id="deleteModal" class="modal">
    <div class="modal-box">
        <h3>Delete Chat?</h3>

        <form id="deleteForm" method="POST">
            @csrf
            @method('DELETE')

            <button class="btn red">Yes</button>
            <button type="button" onclick="closeModal()" class="btn cancel">Cancel</button>
        </form>
    </div>
</div>

<script>

    // LOADING
    document.querySelector(".bottom").addEventListener("submit", function () {
        document.getElementById("loading").style.display = "block";
    });

    // MODAL
    function openModal(id) {
        document.getElementById('deleteModal').style.display = 'flex';
        document.getElementById('deleteForm').action = '/chat/' + id;
    }

    function closeModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }

    window.onload = function () {
        document.getElementById('deleteModal').style.display = 'none';
    };

    // IMAGE PREVIEW
    function previewImage(event) {
        let reader = new FileReader();
        reader.onload = function () {
            let output = document.getElementById('preview');
            output.src = reader.result;
            output.style.display = 'block';
        };
        reader.readAsDataURL(event.target.files[0]);
    }

</script>

</body>
</html>