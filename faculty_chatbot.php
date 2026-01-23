<?php
session_start();
<<<<<<< HEAD
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
=======
include "config.php";

if(!isset($_SESSION['faculty_id'])){
    header("Location: login.php");
    exit();
}

$faculty_id = $_SESSION['faculty_id'];

// Fetch faculty details
$query = mysqli_query($conn, "SELECT name FROM faculty WHERE id='$faculty_id'");
$user = mysqli_fetch_assoc($query);
$faculty_name = $user['name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chatbot Assistant | Career Advancement System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary-color: #6a11cb;
            --secondary-color: #2575fc;
            --bot-bg: #f0f4ff;
            --user-bg: linear-gradient(135deg, #6a11cb, #2575fc);
            --text-dark: #333;
            --text-light: #666;
            --border-color: #e1e5ee;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 900px;
            height: 90vh;
            display: flex;
            flex-direction: column;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            animation: fadeIn 0.6s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Header */
        .chat-header {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .bot-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .header-text h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .header-text p {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .user-info {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Chat Body */
        .chat-body {
            flex: 1;
            padding: 25px;
            overflow-y: auto;
            background: #f8f9fa;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Message Styles */
        .message {
            max-width: 75%;
            padding: 15px 20px;
            border-radius: 18px;
            line-height: 1.5;
            position: relative;
            animation: slideIn 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .bot-message {
            align-self: flex-start;
            background: white;
            border: 1px solid var(--border-color);
            color: var(--text-dark);
            border-bottom-left-radius: 5px;
        }

        .user-message {
            align-self: flex-end;
            background: var(--user-bg);
            color: white;
            border-bottom-right-radius: 5px;
        }

        .message-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .message-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
        }

        .bot-message .message-avatar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .user-message .message-avatar {
            background: white;
            color: var(--primary-color);
        }

        .sender-name {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .bot-message .sender-name {
            color: var(--primary-color);
        }

        .user-message .sender-name {
            color: white;
        }

        .message-time {
            font-size: 0.75rem;
            opacity: 0.7;
            margin-top: 8px;
            text-align: right;
        }

        /* Quick Actions */
        .quick-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 20px 0;
        }

        .quick-btn {
            background: white;
            border: 1px solid var(--border-color);
            color: var(--primary-color);
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .quick-btn:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(106, 17, 203, 0.2);
        }

        /* Input Area */
        .input-area {
            padding: 20px 30px;
            background: white;
            border-top: 1px solid var(--border-color);
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .input-wrapper {
            flex: 1;
            position: relative;
        }

        #question {
            width: 100%;
            padding: 15px 20px 15px 50px;
            border: 2px solid var(--border-color);
            border-radius: 50px;
            font-size: 1rem;
            transition: all 0.3s;
            outline: none;
            background: #f8f9fa;
        }

        #question:focus {
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 3px rgba(106, 17, 203, 0.1);
        }

        .input-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-color);
            font-size: 1.2rem;
        }

        .send-btn {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            box-shadow: 0 5px 15px rgba(106, 17, 203, 0.3);
        }

        .send-btn:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 8px 20px rgba(106, 17, 203, 0.4);
        }

        .send-btn:active {
            transform: translateY(0) scale(1);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 10px;
            padding: 0 30px 20px;
            background: white;
        }

        .action-btn {
            flex: 1;
            padding: 12px;
            border: 1px solid var(--border-color);
            background: white;
            color: var(--text-dark);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        .action-btn:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
        }

        .action-btn.clear {
            color: #ff4757;
            border-color: #ff4757;
        }

        .action-btn.clear:hover {
            background: #ffeaea;
        }

        /* Welcome Message */
        .welcome-message {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 20px;
            animation: slideIn 0.5s ease;
        }

        .welcome-message h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .welcome-message p {
            margin-bottom: 10px;
            opacity: 0.9;
        }

        .welcome-message ul {
            padding-left: 20px;
            margin-top: 15px;
        }

        .welcome-message li {
            margin-bottom: 8px;
            opacity: 0.9;
        }

        /* Typing Indicator */
        .typing-indicator {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 15px 20px;
            background: white;
            border-radius: 18px;
            border: 1px solid var(--border-color);
            align-self: flex-start;
            width: 100px;
        }

        .typing-dot {
            width: 8px;
            height: 8px;
            background: var(--primary-color);
            border-radius: 50%;
            animation: typing 1.4s infinite;
        }

        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }

        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-10px); }
        }

        /* Scrollbar Styling */
        .chat-body::-webkit-scrollbar {
            width: 6px;
        }

        .chat-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .chat-body::-webkit-scrollbar-thumb {
            background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
            border-radius: 10px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                height: 95vh;
                border-radius: 15px;
            }
            
            .chat-header {
                padding: 15px 20px;
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }
            
            .user-info {
                align-self: flex-start;
            }
            
            .message {
                max-width: 85%;
            }
            
            .quick-actions {
                justify-content: center;
            }
            
            .quick-btn {
                font-size: 0.8rem;
                padding: 8px 15px;
            }
            
            .input-area {
                padding: 15px 20px;
            }
            
            .action-buttons {
                padding: 0 20px 15px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .container {
                height: 100vh;
                border-radius: 10px;
            }
            
            .message {
                max-width: 90%;
                padding: 12px 16px;
            }
            
            .chat-body {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="chat-header">
            <div class="header-left">
                <div class="bot-icon">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="header-text">
                    <h2>AI Chatbot Assistant</h2>
                    <p>Career Advancement & Promotion Guidance</p>
                </div>
            </div>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span>Faculty: <?php echo htmlspecialchars($faculty_name); ?></span>
            </div>
        </div>

        <!-- Chat Body -->
        <div class="chat-body" id="chatBody">
            <!-- Welcome Message -->
            <div class="welcome-message">
                <h3><i class="fas fa-graduation-cap"></i> Welcome to AI Assistant!</h3>
                <p>Hello <strong><?php echo htmlspecialchars($faculty_name); ?></strong>! I'm your AI assistant for career advancement guidance.</p>
                <p>You can ask me about:</p>
                <ul>
                    <li><strong>Promotion Guidelines:</strong> Criteria, eligibility, application process</li>
                    <li><strong>API Score:</strong> Calculation, categories, improvement tips</li>
                    <li><strong>Research & Publications:</strong> Points system, requirements</li>
                    <li><strong>Teaching Activities:</strong> Evaluation methods, documentation</li>
                </ul>
                <p style="margin-top: 15px; font-style: italic;">Type your question below or click on a quick question!</p>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <button class="quick-btn" onclick="askQuickQuestion('What is the minimum API score for promotion?')">
                    <i class="fas fa-chart-line"></i> API Score Requirements
                </button>
                <button class="quick-btn" onclick="askQuickQuestion('How to improve my teaching score?')">
                    <i class="fas fa-chalkboard-teacher"></i> Teaching Tips
                </button>
                <button class="quick-btn" onclick="askQuickQuestion('What documents are needed for promotion?')">
                    <i class="fas fa-file-alt"></i> Required Documents
                </button>
                <button class="quick-btn" onclick="askQuickQuestion('How are research papers evaluated?')">
                    <i class="fas fa-flask"></i> Research Evaluation
                </button>
            </div>

            <!-- Messages will be added here by JavaScript -->
        </div>

        <!-- Input Area -->
        <div class="input-area">
            <div class="input-wrapper">
                <i class="fas fa-comment-alt input-icon"></i>
                <input type="text" id="question" placeholder="Type your question about promotion, API score, or research..." 
                       onkeypress="handleKeyPress(event)">
            </div>
            <button class="send-btn" onclick="sendMessage()">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <button class="action-btn" onclick="clearChat()">
                <i class="fas fa-trash-alt"></i> Clear Chat
            </button>
            <button class="action-btn" onclick="showSuggestions()">
                <i class="fas fa-lightbulb"></i> Suggestions
            </button>
            <button class="action-btn" onclick="window.print()">
                <i class="fas fa-print"></i> Print Chat
            </button>
            <button class="action-btn" onclick="location.href='faculty_dashboard.php'">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </button>
        </div>
    </div>

    <script>
        // Initialize chat
        let chatHistory = [];
        
        // Get current time in HH:MM format
        function getCurrentTime() {
            const now = new Date();
            return now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        }
        
        // Add message to chat
        function addMessage(sender, message, isBot = true) {
            const chatBody = document.getElementById('chatBody');
            const time = getCurrentTime();
            const senderName = isBot ? 'AI Assistant' : 'You';
            const avatarText = isBot ? 'AI' : '<?php echo strtoupper(substr($faculty_name, 0, 2)); ?>';
            const messageClass = isBot ? 'bot-message' : 'user-message';
            
            const messageHtml = `
                <div class="message ${messageClass}">
                    <div class="message-header">
                        <div class="message-avatar">${avatarText}</div>
                        <div class="sender-name">${senderName}</div>
                    </div>
                    <div class="message-content">${message}</div>
                    <div class="message-time">${time}</div>
                </div>
            `;
            
            chatBody.innerHTML += messageHtml;
            chatBody.scrollTop = chatBody.scrollHeight;
            
            // Save to history
            chatHistory.push({
                sender: senderName,
                message: message,
                time: time,
                isBot: isBot
            });
        }
        
        // Show typing indicator
        function showTyping() {
            const chatBody = document.getElementById('chatBody');
            chatBody.innerHTML += `
                <div class="typing-indicator">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
            `;
            chatBody.scrollTop = chatBody.scrollHeight;
        }
        
        // Remove typing indicator
        function removeTyping() {
            const typingIndicator = document.querySelector('.typing-indicator');
            if (typingIndicator) {
                typingIndicator.remove();
            }
        }
        
        // Send message to server
        function sendMessage() {
            const questionInput = document.getElementById('question');
            const question = questionInput.value.trim();
            
            if (!question) return;
            
            // Add user message
            addMessage('user', question, false);
            questionInput.value = '';
            
            // Show typing indicator
            showTyping();
            
            // Send to server
            fetch("chatbot_api.php", {
                method: "POST",
                headers: { 
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: JSON.stringify({ 
                    question: question,
                    faculty_id: "<?php echo $faculty_id; ?>",
                    faculty_name: "<?php echo $faculty_name; ?>"
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                removeTyping();
                if (data.reply) {
                    addMessage('bot', data.reply, true);
                } else {
                    addMessage('bot', "I apologize, but I couldn't generate a response. Please try again or rephrase your question.", true);
                }
            })
            .catch(error => {
                removeTyping();
                console.error('Error:', error);
                addMessage('bot', "I'm experiencing technical difficulties. Here's some general information: For promotion, you need a minimum API score of 80. Teaching activities count for 50%, co-curricular for 30%, and research for 20%. Make sure all your documents are properly certified.", true);
            });
        }
        
        // Handle Enter key press
        function handleKeyPress(event) {
            if (event.key === 'Enter') {
                sendMessage();
            }
        }
        
        // Quick question button handler
        function askQuickQuestion(question) {
            document.getElementById('question').value = question;
            sendMessage();
        }
        
        // Clear chat
        function clearChat() {
            if (confirm('Are you sure you want to clear the chat history?')) {
                const chatBody = document.getElementById('chatBody');
                const welcomeMessage = document.querySelector('.welcome-message');
                const quickActions = document.querySelector('.quick-actions');
                
                chatBody.innerHTML = '';
                chatBody.appendChild(welcomeMessage);
                chatBody.appendChild(quickActions);
                
                chatHistory = [];
                addMessage('bot', "Chat cleared! How can I help you today?", true);
            }
        }
        
        // Show suggestions
        function showSuggestions() {
            const suggestions = `
                <div class="message bot-message">
                    <div class="message-header">
                        <div class="message-avatar">AI</div>
                        <div class="sender-name">AI Assistant</div>
                    </div>
                    <div class="message-content">
                        <h4>Suggested Questions:</h4>
                        <ul style="margin-top: 10px; padding-left: 20px;">
                            <li>What is the promotion timeline?</li>
                            <li>How to calculate API score for Category I?</li>
                            <li>What are the different promotion levels?</li>
                            <li>How many research papers are required for Associate Professor?</li>
                            <li>What is the student feedback weightage?</li>
                            <li>How to apply for promotion online?</li>
                            <li>What documents need to be notarized?</li>
                            <li>How are co-curricular activities evaluated?</li>
                        </ul>
                        <p style="margin-top: 15px; font-style: italic;">Click any question or type your own!</p>
                    </div>
                    <div class="message-time">${getCurrentTime()}</div>
                </div>
            `;
            
            document.getElementById('chatBody').innerHTML += suggestions;
            document.getElementById('chatBody').scrollTop = document.getElementById('chatBody').scrollHeight;
        }
        
        // Load chat history from localStorage
        function loadChatHistory() {
            const savedHistory = localStorage.getItem('chatHistory_<?php echo $faculty_id; ?>');
            if (savedHistory) {
                try {
                    chatHistory = JSON.parse(savedHistory);
                    // Replay last 10 messages
                    const lastMessages = chatHistory.slice(-10);
                    lastMessages.forEach(msg => {
                        addMessage(msg.sender, msg.message, msg.isBot);
                    });
                } catch (e) {
                    console.error('Error loading chat history:', e);
                }
            }
        }
        
        // Save chat history to localStorage
        function saveChatHistory() {
            localStorage.setItem('chatHistory_<?php echo $faculty_id; ?>', JSON.stringify(chatHistory));
        }
        
        // Auto-save chat history every 10 seconds
        setInterval(saveChatHistory, 10000);
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadChatHistory();
            
            // Focus on input field
            document.getElementById('question').focus();
            
            // Add welcome message if no history exists
            if (chatHistory.length === 0) {
                setTimeout(() => {
                    addMessage('bot', "I'm here to help you with all your career advancement queries! Feel free to ask anything about promotions, API scores, teaching methods, or research publications.", true);
                }, 1000);
            }
        });
    </script>
</body>
</html>
>>>>>>> 90e527b (Initial commit)
