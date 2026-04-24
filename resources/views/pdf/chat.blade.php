<!DOCTYPE html>
<html>

<head>
    <title>Chat History</title>
    <style>
        body {
            font-family: Arial;
        }

        .chat {
            margin-bottom: 15px;
        }

        .q {
            font-weight: bold;
        }

        .a {
            margin-left: 10px;
            color: #333;
        }
    </style>
</head>

<body>

    <h2>AI Chat History</h2>

    @foreach($chats as $chat)
        <div class="chat">
            <div class="q">Q: {{ $chat->question }}</div>
            <div class="a">A: {{ $chat->answer }}</div>
        </div>
    @endforeach

</body>

</html>