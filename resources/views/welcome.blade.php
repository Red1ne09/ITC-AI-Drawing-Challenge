<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('draw_challenge.game_title', 'AI Draw Challenge') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Vite Assets -->
    @vite(['resources/css/game.css', 'resources/js/game.js'])
    
    <!-- Game Configuration -->
    <script>
        window.canvasWidth = {{ config('draw_challenge.canvas_width') }};
        window.canvasHeight = {{ config('draw_challenge.canvas_height') }};
    </script>
</head>
<body>
    <div id="game-container">
        <!-- Start Screen -->
        <div id="start-screen" class="transition-opacity opacity-100">
            <div style="display:flex;justify-content:center;width:100%;margin:20px;gap:25px;padding:25px">
                @if (!empty(config('draw_challenge.club_logo')))
                <img src="{{ asset(config('draw_challenge.club_logo')) }}" alt="ITC" style="border-radius:15px;max-height: 80px;position:relative;align-self:center">
                @endif
                <div class="game-header">
                    <h1>{{ config('draw_challenge.game_title', 'AI Draw Challenge') }}</h1>
                    <p>{{ config('draw_challenge.game_description', 'You draw it. Our AI tries to guess it.') }}</p>
                </div>
            </div>
                
            <div class="scoreboard" id="leaderboard">
                <span class="label">Highest Score: </span>
                <span class="value" id="human-score">{{ $highestScorerName }}</span>
                <div class="separator">—</div>
                <span class="value" id="ai-score">{{ $highestScore }}</span>
                <button id="leaderboard-btn" class="btn secondary-btn">View Leaderboard</button>
            </div>

            <div class="rules">
                <h1>How to Play</h1>
                <ul>
                    <p>Draw the Chosen Word on the canvas.</p>
                    <p>Click "Guess!" when you're done.</p>
                    <p>If AI gets it right, you get a point.</p>
                    <p>Try To Score the highest by drawing well!</p>
                </ul>
                <button id="start-btn" class="btn btn-primary">Start Playing</button>
            </div>
            
        </div>
        
        <!-- Game Screen -->
        <div id="game-screen" class="transition-opacity hidden-space opacity-0">
            <div class="scoreboard">
                <div>
                    <span class="label">Your score: </span>
                    <span class="value" id="player-score">0</span>
                </div>
                <div class="separator">—</div>
                <div>
                    <span class="label">Highest score: </span>
                    <span class="value" id="highest-score">{{ $highestScore }}</span>
                </div>
            </div>
            
            <div class="prompt">
                <div style="display:flex;gap:50px;width:100%;align-content:center;justify-content:center">
                    <h2 id="current-prompt">Draw this:</h2>
                    <h2 id="prompt-text" style="color:white"></h2>
                    <p id="instruction-text"></p>

                </div>
            </div>
            
            <div class="canvas-container">
                <canvas id="drawing-canvas"></canvas>
            </div>
            
            <div class="drawing-toolbar">
                <button id="undo-btn" disabled>Undo</button>
                <button id="clear-btn">Clear</button>
                <button id="guess-btn" disabled>Guess!</button>
</div>
    
    </div>

    <!-- Leaderboard Screen -->
    <div id="leaderboard-screen" class="leaderboard-screen transition-opacity hidden-space opacity-0">
        <div class="leaderboard-header">
            <h1>{{ config('draw_challenge.game_title', 'AI Draw Challenge') }} - Leaderboard</h1>
            <button id="back-to-main-btn" class="btn secondary-btn">Back to Main</button>
        </div>
        <div class="leaderboard-table-wrapper">
            <table id="leaderboard-table" class="leaderboard-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Name</th>
                        <th>Score</th>
                        <th>Successes</th>
                        <th>Failures</th>
                        <th>Total Attempts</th>
                    </tr>
                </thead>
                <tbody id="leaderboard-body">
                    <!-- Populated via JavaScript -->
                </tbody>
            </table>
        </div>
        <div id="leaderboard-empty" class="leaderboard-empty" style="display: none;">
            No players yet. Be the first to play!
        </div>
    </div>

    <!-- Result Card Overlay -->
    <div id="result-card" class="result-card-overlay">
        <div class="result-card-content">
            <h3 id="result-heading"></h3>
            <div class="buttons">
                <button id="ai-correct-btn" disabled>AI guessed correctly</button>
                <button id="ai-wrong-btn" disabled>AI guessed wrong</button>
                <button id="next-round-btn" style="display:none;">Next Round</button>
            </div>
        </div>
    </div>

    <!-- Name Entry Modal -->
    <div id="name-modal" class="modal-overlay hidden">
        <div class="modal-content">
            <h2>Enter Your Name</h2>
            <p>What should we call you?</p>
            <input type="text" id="player-name-input" placeholder="Your full name" maxlength="255" autocomplete="off">
            <div class="modal-error" id="name-modal-error"></div>
            <div class="modal-buttons">
                <button id="name-cancel-btn" class="btn btn-secondary">Cancel</button>
                <button id="name-play-btn" class="btn btn-primary">Play</button>
            </div>
        </div>
    </div>
</body>
</html>
