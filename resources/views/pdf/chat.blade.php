<!DOCTYPE html>
<html>

<head>
    <title>Chat History</title>
    <style>
        body {
            font-family: Arial;
        }

        .chat {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e2e8f0;
        }

        .q {
            font-weight: bold;
            color: #1e293b;
        }

        .a {
            margin-top: 8px;
            color: #334155;
            line-height: 1.5;
        }

        .chat-img {
            margin-top: 10px;
            max-width: 250px;
            border-radius: 8px;
        }
    </style>
</head>

<body>

    <h2>AI Chat History</h2>

    @foreach($chats as $chat)
        <div class="chat">
            
            <div class="q">
                Q: {{ $chat->question !== 'Image only' ? $chat->question : '[Image Uploaded]' }}
            </div>
            
            <div class="a">
                A: {{ $chat->answer }}
            </div>

            @if($chat->image)
                <div>
                    <img src="{{ public_path('storage/' . $chat->image) }}" class="chat-img">
                </div>
            @endif

        </div>
    @endforeach

</body>

</html>