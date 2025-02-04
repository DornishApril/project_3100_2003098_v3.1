from flask import Flask, request, session
import os
import google.generativeai as genai
from dotenv import load_dotenv

app = Flask(__name__)
app.secret_key = os.getenv("FLASK_SECRET_KEY") or os.urandom(24)  # Add secret key for sessions

# Enable CORS for cross-origin requests
from flask_cors import CORS
CORS(app)

# Load environment variables from .env file
load_dotenv()

# Configure the API key
genai.configure(api_key=os.getenv("API_KEY"))

# Create the model configuration
generation_config = {
    "temperature": 1,
    "top_p": 0.95,
    "top_k": 40,
    "max_output_tokens": 8192,
    "response_mime_type": "text/plain",
}

# Store chat sessions in memory (reset on server reload)
chat_sessions = {}

@app.route('/')
def index():
    return "Flask server is running!"

@app.route('/generate', methods=['POST'])
def generate_response():
    client_id = request.headers.get('X-Client-ID')  # Frontend should send unique client ID
    
    # Initialize or reset session when server restarts
    if client_id not in chat_sessions:
        chat_sessions[client_id] = {
            "initialized": False,
            "session": genai.GenerativeModel(
                model_name="gemini-1.5-flash",
                generation_config=generation_config,
            ).start_chat(history=[])
        }

    # Get the chat session for this client
    chat_data = chat_sessions[client_id]
    
    # Use initial prompt only once per session
    if not chat_data["initialized"]:
        initial_prompt = ("You will act as a customer service provider for smartbazar!.your name is Marta, the AI shop-buddy and you always introduce yourself with happy positive emojis "
                         "Greet the user and offer help. Keep responses within 3 sentences.")
        chat_data["initialized"] = True
    else:
        initial_prompt = "Keep the response short and focused on the query. Always talk about products. Relate everything to a product always use emojis at the end"

    user_message = request.form['user_message']
    response = chat_data["session"].send_message(initial_prompt + user_message)
    
    return response.text

if __name__ == '__main__':
    app.run()