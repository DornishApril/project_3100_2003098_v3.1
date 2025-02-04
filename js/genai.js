function sendMessage() {
    var chatInput = document.getElementById('chat-input');
    var chatContent = document.getElementById('chat-content');
    var message = chatInput.value;

    console.log("User message: " + message);  // Log the user message

    if (message.trim() !== '') {
        var userBubble = document.createElement('div');
        userBubble.className = 'chat-bubble user-bubble';
        userBubble.innerText = message;
        chatContent.appendChild(userBubble);
        chatInput.value = '';
        chatContent.scrollTop = chatContent.scrollHeight;

        // Generate the response using the Flask server
        generateAIResponse(message, function(response) {
            console.log("Response from assistant: " + response);  // Log the response

            var assistantBubble = document.createElement('div');
            assistantBubble.className = 'chat-bubble assistant-bubble';
            assistantBubble.innerText = response;
            chatContent.appendChild(assistantBubble);
            chatContent.scrollTop = chatContent.scrollHeight;

            // Send the response to the inbox (assumes you have a function to handle inbox messages)
            sendToInbox(response);
        });
    }
}



// Function to generate the AI response using the Flask server
function generateAIResponse(userMessage, callback) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'http://127.0.0.1:5000/generate', true);  // Ensure this URL matches your Flask server endpoint
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            callback(xhr.responseText);
        }
    };
    xhr.send('user_message=' + encodeURIComponent(userMessage));
}
// Toggle chat box visibility
// Modified toggle function
function toggleChatBox() {
    const chatBox = document.getElementById('chat-box');
    const chatButton = document.getElementById('floating-chat-button');
    
    if (chatBox.style.display === 'none' || !chatBox.style.display) {
        chatBox.style.display = 'flex';
        chatButton.style.display = 'none'; // Hide chat icon when opening
    } else {
        chatBox.style.display = 'none';
        chatButton.style.display = 'flex'; // Show chat icon when closing
    }
}

// Modified close function
function closeChatBox() {
    document.getElementById('chat-box').style.display = 'none';
    document.getElementById('floating-chat-button').style.display = 'flex'; // Show chat icon
}

// Add this to your CSS


// Optional: Allow pressing Enter to send a message
document.getElementById('chat-input').addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        sendMessage();
    }
});