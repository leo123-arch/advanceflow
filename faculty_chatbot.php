<?php
session_start();
if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Faculty AI Chatbot</title>
    <link rel="stylesheet" href="./css/faculty_chatbot.css">
</head>
<body>

<div class="chatbox">
    <div class="chat-header">🎓 Faculty AI Assistant</div>

    <div class="chat-body" id="chatBody">
        <div class="msg bot">Hello! I am your AI assistant. Ask me anything about promotion, API score, or research.</div>
    </div>

    <div class="input-area">
        <input type="text" id="question" placeholder="Ask your question..." />
        <button onclick="sendMessage()">Send</button>
    </div>
</div>

<script>
function sendMessage(){
    let q = document.getElementById("question").value;
    if(q=="") return;

    let chat = document.getElementById("chatBody");
    chat.innerHTML += `<div class="msg user">${q}</div>`;

    fetch("chatbot_api.php",{
        method:"POST",
        headers:{ "Content-Type":"application/json" },
        body: JSON.stringify({ question:q })
    })
    .then(res=>res.json())
    .then(data=>{
        chat.innerHTML += `<div class="msg bot">${data.reply}</div>`;
        chat.scrollTop = chat.scrollHeight;
    });

    document.getElementById("question").value="";
}
</script>

</body>
</html>
