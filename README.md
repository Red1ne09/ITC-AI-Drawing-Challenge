# AI Draw Challenge

A drawing-guessing game for an AI club's welcome day. Players draw a random word on a canvas, and a local AI model (Qwen3-VL via llama-server) attempts to guess the drawing.

## Features

- Drawing canvas with mouse/touch/stylus support
- Undo and clear drawing functions
- Random word prompts from a curated list
- AI guessing via local llama-server (Qwen3-VL)
- Score tracking (human vs AI)
- Error handling and retry mechanism
- Responsive design
- Club branding with black, red, and white color scheme

## Requirements

- PHP 8.2+ (matching Laravel version)
- Composer
- llama-server with Qwen3-VL vision model
- Web browser with Canvas API support

## Setup

### 1. Laravel Application

```bash
# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure environment variables (see below)
```

### 2. llama-server

Download and run llama-server with a Qwen3-VL GGUF model that supports vision:
```bash
./llama-server -m /path/to/qwen3-vl.Q4_K_M.gguf --port 8080 --ctx-size 2048
```

Ensure the server is accessible at `http://127.0.0.1:8080` and supports image inputs via the OpenAI-compatible chat completions endpoint.

### 3. Environment Variables

Edit `.env` to set the following:

```env
# Club branding
DRAW_CHALLENGE_CLUB_NAME="AI Club"
DRAW_CHALLENGE_CLUB_LOGO=/icons/itc-logo.png  # Optional, path relative to public folder
DRAW_CHALLENGE_GAME_TITLE="AI Draw Challenge"
DRAW_CHALLENGE_GAME_DESCRIPTION="You draw it. Our AI tries to guess it."

# Llama server connection
LLAMA_BASE_URL=http://127.0.0.1:8080
LLAMA_API_KEY=  # Set if your llama-server requires authentication
LLAMA_MODEL=local-vision-model
LLAMA_CONNECT_TIMEOUT=5
LLAMA_TIMEOUT=120
LLAMA_MAX_TOKENS=100
LLAMA_TEMPERATURE=0.1

# Game settings
DRAW_CHALLENGE_MAX_IMAGE_KB=2048
DRAW_CHALLENGE_MAX_IMAGE_DIMENSION=2048
```

### 4. Storage Link (if needed)

```bash
php artisan storage:link
```

### 5. Run the Application

Start the Laravel development server:
```bash
php artisan serve
```

Visit `http://127.0.0.1:8000` in your browser.

## How to Play

1. Click "Start Playing" to begin a round.
2. A prompt word will appear at the top of the screen.
3. Draw the word on the white canvas (avoid writing the word itself).
4. Click "Guess!" when you finish your drawing.
5. Wait for the AI to process and display its guess.
6. Click either "AI guessed correctly" or "AI guessed wrong" to score the round.
7. Click "Next Drawing" to continue to the next round.

## Testing

Run the PHPUnit test suite:
```bash
php artisan test
```

## Design Notes

- All AI inference is routed through the Laravel backend to protect the llama-server URL and API key.
- Scores are stored only in browser memory and reset on page reload.
- No drawings, scores, or user data are persisted on the server.
- The application uses Laravel Blade, vanilla JavaScript, and standard CSS.
- No external frontend frameworks (React, Vue, etc.) are used.

## Troubleshooting

- If the AI guessing fails, ensure llama-server is running and accessible.
- Check the browser console for JavaScript errors.
- Verify that the CSRF token is being included in requests (handled automatically by Laravel).
- For CORS issues, ensure llama-server is accessed from the same origin or properly configured (though the proxy through Laravel avoids this).

## License

MIT
