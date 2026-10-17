/**
 * AI Draw Challenge - Frontend Logic
 */

class DrawChallengeGame {
    constructor() {
        this.state = 'start'; // start, name-entry, drawing, thinking, result, choice-made, error
        this.roundActive = false;
        this.currentPrompt = null;
        this.humanScore = 0;
        this.aiScore = 0;
        this.highestScore = 0;
        this.highestScorerName = '—';
        this.undoStack = [];
        this.maxUndoDepth = 20;
        this.isDrawing = false;
        this.lastPoint = null;
        this.errorOverlay = null;
        this.transitionEndListener = null;
        this.pendingAiGuessedCorrectly = null;
        
        // Player info
        this.currentPlayer = null;
        this.playerName = null;
        
        // Timer
        this.timerDuration = 120; // 2 minutes in seconds
        this.timeRemaining = this.timerDuration;
        this.timerInterval = null;
        
        // DOM elements
        this.container = document.getElementById('game-container');
        this.startScreen = document.getElementById('start-screen');
        this.gameScreen = document.getElementById('game-screen');
        this.promptDisplay = document.getElementById('current-prompt');
        this.promptText = document.getElementById('prompt-text');
        this.instructionText = document.getElementById('instruction-text');
        this.canvas = document.getElementById('drawing-canvas');
        this.ctx = this.canvas.getContext('2d');
        this.scoreboard = {
            player: document.getElementById('player-score'),
            highest: document.getElementById('highest-score')
        };
        this.undoBtn = document.getElementById('undo-btn');
        this.clearBtn = document.getElementById('clear-btn');
        this.guessBtn = document.getElementById('guess-btn');
        this.resultCard = document.getElementById('result-card');
        this.resultHeading = document.getElementById('result-heading');
        this.resultButtons = {
            correct: document.getElementById('ai-correct-btn'),
            wrong: document.getElementById('ai-wrong-btn'),
        };
        
        // Name modal elements
        this.nameModal = document.getElementById('name-modal');
        this.nameInput = document.getElementById('player-name-input');
        this.nameError = document.getElementById('name-modal-error');
        this.nameCancelBtn = document.getElementById('name-cancel-btn');
        this.namePlayBtn = document.getElementById('name-play-btn');
        
        // Leaderboard elements
        this.leaderboardScreen = document.getElementById('leaderboard-screen');
        this.leaderboardBody = document.getElementById('leaderboard-body');
        this.leaderboardEmpty = document.getElementById('leaderboard-empty');
        
        // Timer display
        this.timerDisplay = document.createElement('div');
        this.timerDisplay.id = 'timer-display';
        this.timerDisplay.className = 'timer';
        this.promptDisplay.appendChild(this.timerDisplay);
        
        // Configuration from PHP
        this.canvasWidth = parseInt(canvasWidth) || 700;
        this.canvasHeight = parseInt(canvasHeight) || 450;
        
        // Initialize
        this.init();
    }
    
    init() {
        this.resizeCanvas();
        this.setupCanvasEvents();
        this.setupButtonEvents();
        this.setupWindowEvents();
        this.setupNameModalEvents();
        this.fetchHighestScore();
        this.showStartScreen();
    }
    
    setupButtonEvents() {
        document.getElementById('start-btn').addEventListener('click', () => this.showNameModal());
        document.getElementById('leaderboard-btn').addEventListener('click', () => this.showLeaderboard());
        document.getElementById('back-to-main-btn').addEventListener('click', () => this.hideLeaderboard());
        this.undoBtn.addEventListener('click', () => this.undo());
        this.clearBtn.addEventListener('click', () => this.clearCanvas());
        this.guessBtn.addEventListener('click', () => this.submitGuess());
        this.resultButtons.correct.addEventListener('click', () => this.scoreRound(true));
        this.resultButtons.wrong.addEventListener('click', () => this.scoreRound(false));
        document.getElementById('next-round-btn').addEventListener('click', () => this.nextRound());
    }
    
    setupNameModalEvents() {
        this.nameCancelBtn.addEventListener('click', () => this.hideNameModal());
        this.namePlayBtn.addEventListener('click', () => this.createPlayer());
        this.nameInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.createPlayer();
        });
        this.nameInput.addEventListener('input', () => {
            this.nameError.textContent = '';
            this.nameInput.style.borderColor = '';
        });
    }
    
    setupCanvasEvents() {
        this.canvas.addEventListener('pointerdown', (e) => this.startDrawing(e));
        this.canvas.addEventListener('pointermove', (e) => this.draw(e));
        this.canvas.addEventListener('pointerup', (e) => this.endDrawing(e));
        this.canvas.addEventListener('pointerleave', (e) => this.endDrawing(e));
        this.canvas.addEventListener('pointercancel', (e) => this.endDrawing(e));
        this.canvas.addEventListener('touchmove', (e) => e.preventDefault(), { passive: false });
    }
    
    setupWindowEvents() {
        window.addEventListener('resize', () => this.resizeCanvas());
    }

    async fetchHighestScore() {
        try {
            const response = await fetch('/highest-score');
            const data = await response.json();
            
            if (data.success) {
                this.highestScore = data.highest_score;
                this.highestScorerName = data.highest_scorer_name;
                this.updateHighestScoreDisplay();
            }
        } catch (error) {
            console.error('Error fetching highest score:', error);
        }
    }

    async showLeaderboard() {
        this.stopTimer();
        this.hideErrorOverlay();
        this.resultCard.classList.remove('show');
        
        // Hide start screen
        this.startScreen.classList.remove('opacity-100');
        this.startScreen.classList.add('opacity-0');
        
        if (this.transitionEndListener) {
            this.startScreen.removeEventListener('transitionend', this.transitionEndListener);
        }
        
        this.transitionEndListener = () => {
            this.startScreen.classList.add('hidden-space');
            this.startScreen.classList.remove('opacity-100');
            this.startScreen.classList.add('opacity-0');
            this.leaderboardScreen.classList.remove('hidden-space');
            this.leaderboardScreen.classList.remove('opacity-0');
            this.leaderboardScreen.classList.add('opacity-100');
            this.startScreen.removeEventListener('transitionend', this.transitionEndListener);
            this.transitionEndListener = null;
            this.fetchLeaderboard();
        };
        
        this.startScreen.addEventListener('transitionend', this.transitionEndListener);
    }

    hideLeaderboard() {
        this.stopTimer();
        this.hideErrorOverlay();
        
        // Hide leaderboard screen
        this.leaderboardScreen.classList.remove('opacity-100');
        this.leaderboardScreen.classList.add('opacity-0');
        
        if (this.transitionEndListener) {
            this.leaderboardScreen.removeEventListener('transitionend', this.transitionEndListener);
        }
        
        this.transitionEndListener = () => {
            this.leaderboardScreen.classList.add('hidden-space');
            this.leaderboardScreen.classList.remove('opacity-100');
            this.leaderboardScreen.classList.add('opacity-0');
            this.startScreen.classList.remove('hidden-space');
            this.startScreen.classList.remove('opacity-0');
            this.startScreen.classList.add('opacity-100');
            this.leaderboardScreen.removeEventListener('transitionend', this.transitionEndListener);
            this.transitionEndListener = null;
        };
        
        this.leaderboardScreen.addEventListener('transitionend', this.transitionEndListener);
    }

    async fetchLeaderboard() {
        try {
            const response = await fetch('/leaderboard');
            const data = await response.json();
            
            if (data.success && data.players) {
                this.renderLeaderboard(data.players);
            } else {
                this.showLeaderboardEmpty();
            }
        } catch (error) {
            console.error('Error fetching leaderboard:', error);
            this.showLeaderboardEmpty();
        }
    }

    renderLeaderboard(players) {
        this.leaderboardBody.innerHTML = '';
        
        if (players.length === 0) {
            this.showLeaderboardEmpty();
            return;
        }
        
        this.leaderboardEmpty.style.display = 'none';
        
        players.forEach((player, index) => {
            const row = document.createElement('tr');
            
            const attempts = player.successes + player.fails;
            
            
            row.innerHTML = `
                <td>${index + 1}</td>
                <td>${this.escapeHtml(player.name)}</td>
                <td>${player.score}</td>
                <td>${player.successes}</td>
                <td>${player.fails}</td>
                <td>${attempts}</td>
            `;
            
            // Highlight current player if they're in the leaderboard
            if (this.currentPlayer && player.id === this.currentPlayer.id) {
                row.classList.add('current-player');
            }
            
            this.leaderboardBody.appendChild(row);
        });
    }

    showLeaderboardEmpty() {
        this.leaderboardBody.innerHTML = '';
        this.leaderboardEmpty.style.display = 'block';
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    showNameModal() {
        this.nameInput.value = '';
        this.nameError.textContent = '';
        this.nameInput.style.borderColor = '';
        this.nameModal.classList.remove('hidden');
        this.nameInput.focus();
    }
    
    hideNameModal() {
        this.nameModal.classList.add('hidden');
        this.nameInput.value = '';
        this.nameError.textContent = '';
    }
    
    async createPlayer() {
        const name = this.nameInput.value.trim();
        if (!name) {
            this.nameError.textContent = 'Please enter your name';
            this.nameInput.style.borderColor = 'var(--color-red)';
            this.nameInput.focus();
            return;
        }
        if (name.length > 255) {
            this.nameError.textContent = 'Name is too long (max 255 characters)';
            this.nameInput.style.borderColor = 'var(--color-red)';
            return;
        }
        
        this.namePlayBtn.disabled = true;
        this.namePlayBtn.textContent = 'Creating...';
        this.hideNameModal();
        
        try {
            const response = await fetch('/player', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ name })
            });
            const data = await response.json();
            
            if (data.success) {
                this.currentPlayer = data.player;
                this.playerName = data.player.name;
                this.hideNameModal();
                this.startGame();
            } else {
                this.nameError.textContent = data.message || 'Failed to create player';
                this.nameInput.style.borderColor = 'var(--color-red)';
            }
        } catch (error) {
            console.error('Error creating player:', error);
            this.nameError.textContent = 'Network error. Please try again.';
            this.nameInput.style.borderColor = 'var(--color-red)';
        } finally {
            this.namePlayBtn.disabled = false;
            this.namePlayBtn.textContent = 'Play';
        }
    }
    
    showStartScreen() {
        this.state = 'start';
        this.hideErrorOverlay();
        this.resultCard.classList.remove('show');
        this.stopTimer();
        
        if (!this.gameScreen.classList.contains('hidden-space')) {
            this.gameScreen.classList.remove('opacity-100');
            this.gameScreen.classList.add('opacity-0');
            if (this.transitionEndListener) {
                this.gameScreen.removeEventListener('transitionend', this.transitionEndListener);
            }
            this.transitionEndListener = () => {
                this.startScreen.classList.remove('hidden-space');
                this.startScreen.classList.remove('opacity-0');
                this.startScreen.classList.add('opacity-100');
                this.gameScreen.classList.add('hidden-space');
                this.gameScreen.classList.remove('opacity-100');
                this.gameScreen.classList.add('opacity-0');
                this.gameScreen.removeEventListener('transitionend', this.transitionEndListener);
                this.transitionEndListener = null;
            };
            this.gameScreen.addEventListener('transitionend', this.transitionEndListener);
        } else {
            this.startScreen.classList.remove('hidden-space');
            this.startScreen.classList.remove('opacity-0');
            this.startScreen.classList.add('opacity-100');
            this.gameScreen.classList.add('hidden-space');
            this.gameScreen.classList.remove('opacity-100');
            this.gameScreen.classList.add('opacity-0');
        }
        
        this.humanScore = 0;
        this.aiScore = 0;
        this.currentPlayer = null;
        this.playerName = null;
        this.updateScoreboard();
        this.updateHighestScoreDisplay();
        this.clearCanvas();
        this.undoStack = [];
    }
    
    startGame() {
        this.state = 'drawing';
        this.hideErrorOverlay();
        
        this.startScreen.classList.remove('opacity-100');
        this.startScreen.classList.add('opacity-0');
        
        if (this.transitionEndListener) {
            this.startScreen.removeEventListener('transitionend', this.transitionEndListener);
        }
        
        this.transitionEndListener = () => {
            this.gameScreen.classList.remove('hidden-space');
            this.gameScreen.classList.remove('opacity-0');
            this.gameScreen.classList.add('opacity-100');
            this.startScreen.classList.add('hidden-space');
            this.startScreen.classList.remove('opacity-100');
            this.startScreen.classList.add('opacity-0');
            this.startScreen.removeEventListener('transitionend', this.transitionEndListener);
            this.transitionEndListener = null;
            this.startRound();
            this.startTimer();
        };
        
        this.startScreen.addEventListener('transitionend', this.transitionEndListener);
    }
    
    startRound() {
        this.roundActive = true;
        this.currentPrompt = null;
        this.resultCard.classList.remove('show');
        this.clearCanvas();
        this.enableDrawing();
        this.fetchNewPrompt();
    }
    
    fetchNewPrompt() {
        fetch('/game/new-round', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.currentPrompt = data.prompt;
                this.promptText.textContent = this.currentPrompt;
                this.instructionText.textContent = 'Draw this without writing the word!';
                this.enableDrawing();
            } else {
                this.showErrorOverlay('Failed to get a new prompt. Please try again.');
            }
        })
        .catch(() => {
            this.showErrorOverlay('Network error. Please try again.');
        });
    }
    
    resizeCanvas() {
        const container = this.canvas.parentElement;
        const ratio = this.canvasWidth / this.canvasHeight;
        let width = container.clientWidth;
        let height = width / ratio;
        
        if (height > container.clientHeight) {
            height = container.clientHeight;
            width = height * ratio;
        }
        
        if (width > this.canvasWidth) {
            width = this.canvasWidth;
            height = this.canvasHeight;
        }
        
        this.canvas.width = width;
        this.canvas.height = height;
        
        if (this.undoStack.length > 0) {
            this.redrawFromStack();
        } else {
            this.clearCanvas();
        }
    }
    
    enableDrawing() {
        this.canvas.style.cursor = 'crosshair';
        this.undoBtn.disabled = false;
        this.clearBtn.disabled = false;
        this.guessBtn.disabled = false;
    }
    
    disableDrawing() {
        this.canvas.style.cursor = 'not-allowed';
        this.undoBtn.disabled = true;
        this.clearBtn.disabled = true;
        this.guessBtn.disabled = true;
    }
    
    startDrawing(e) {
        if (!this.roundActive) return;
        this.isDrawing = true;
        const point = this.getPointerPosition(e);
        this.lastPoint = point;
        this.undoStack.push([]);
        this.addPointToCurrentStroke(point);
    }
    
    draw(e) {
        if (!this.isDrawing || !this.roundActive) return;
        const point = this.getPointerPosition(e);
        this.addPointToCurrentStroke(point);
        this.renderStroke(this.lastPoint, point);
        this.lastPoint = point;
    }
    
    endDrawing(e) {
        if (!this.isDrawing) return;
        this.isDrawing = false;
        this.lastPoint = null;
    }
    
    getPointerPosition(e) {
        const rect = this.canvas.getBoundingClientRect();
        const scaleX = this.canvas.width / rect.width;
        const scaleY = this.canvas.height / rect.height;
        return {
            x: (e.clientX - rect.left) * scaleX,
            y: (e.clientY - rect.top) * scaleY
        };
    }
    
    addPointToCurrentStroke(point) {
        if (this.undoStack.length === 0) this.undoStack.push([]);
        this.undoStack[this.undoStack.length - 1].push(point);
    }
    
    renderStroke(start, end) {
        this.ctx.beginPath();
        this.ctx.moveTo(start.x, start.y);
        this.ctx.lineTo(end.x, end.y);
        this.ctx.strokeStyle = '#000000';
        this.ctx.lineWidth = 2;
        this.ctx.lineCap = 'round';
        this.ctx.lineJoin = 'round';
        this.ctx.stroke();
    }
    
    redrawFromStack() {
        this.clearCanvas();
        this.undoStack.forEach(stroke => {
            if (stroke.length < 2) return;
            for (let i = 1; i < stroke.length; i++) {
                this.renderStroke(stroke[i-1], stroke[i]);
            }
        });
    }
    
    clearCanvas() {
        this.ctx.fillStyle = '#FFFFFF';
        this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
        this.ctx.strokeStyle = '#000000';
        this.ctx.lineWidth = 2;
        this.ctx.lineCap = 'round';
        this.ctx.lineJoin = 'round';
        this.undoStack = [];
        this.updateUndoButton();
    }
    
    undo() {
        if (this.undoStack.length === 0) return;
        this.undoStack.pop();
        if (this.undoStack.length === 0) {
            this.clearCanvas();
        } else {
            this.redrawFromStack();
        }
        this.updateUndoButton();
    }
    
    updateUndoButton() {
        this.undoBtn.disabled = this.undoStack.length === 0;
    }
    
    isCanvasBlank() {
        const imageData = this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height);
        const data = imageData.data;
        for (let i = 0; i < data.length; i += 4) {
            if (data[i] !== 255 || data[i+1] !== 255 || data[i+2] !== 255) {
                return false;
            }
        }
        return true;
    }
    
    async submitGuess() {
        if (!this.roundActive) return;
        if (this.isCanvasBlank()) {
            this.showErrorOverlay('Please draw something before submitting.');
            return;
        }
        
        this.state = 'thinking';
        this.disableDrawing();
        this.guessBtn.disabled = true;
        this.hideErrorOverlay();
        
        try {
            const blob = await new Promise(resolve => this.canvas.toBlob(resolve, 'image/png'));
            if (!blob || blob.size === 0) throw new Error('Failed to create image from canvas.');
            
            // Save the drawing to our storage
            await this.saveDrawing(blob);
            
            const formData = new FormData();
            formData.append('image', blob, 'drawing.png');
            
            const response = await fetch('/game/guess', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            });
            
            const data = await response.json();
            if (data.success && data.guess) {
                this.showResult(data.guess);
            } else {
                this.showErrorOverlay(data.message || 'Failed to get a guess from the AI.');
            }
        } catch (error) {
            console.error('Error submitting guess:', error);
            this.showErrorOverlay(error.message || 'An error occurred while processing your drawing.');
        }
    }
    
    /**
     * Save the current drawing to storage
     */
    async saveDrawing(blob) {
        if (!this.currentPlayer) return;
        
        try {
            const formData = new FormData();
            formData.append('image', blob, 'drawing.png');
            formData.append('player_id', this.currentPlayer.id);
            formData.append('drawing_name', this.currentPrompt || 'drawing');
            
            const response = await fetch('/drawing/save', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            });
            
            const data = await response.json();
            if (!data.success) {
                console.warn('Failed to save drawing:', data.message);
            }
            // We don't want to interrupt the game if saving fails
        } catch (error) {
            console.error('Error saving drawing:', error);
            // We don't want to interrupt the game if saving fails
        }
    }
    
    showResult(guess) {
        this.state = 'result';
        this.resultHeading.textContent = `The AI guesses: ${guess}`;
        this.resultCard.classList.add('show');
        this.resultButtons.correct.disabled = false;
        this.resultButtons.wrong.disabled = false;
        this.hideErrorOverlay();
    }
    
    async scoreRound(aiGuessedCorrectly) {
        if (this.state !== 'result' || !this.currentPlayer) return;
        
        this.resultButtons.correct.disabled = true;
        this.resultButtons.wrong.disabled = true;
        this.pendingAiGuessedCorrectly = aiGuessedCorrectly;
        
        try {
            const response = await fetch(`/player/${this.currentPlayer.id}/score`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ ai_correct: aiGuessedCorrectly })
            });
            const data = await response.json();
            
            if (data.success) {
                this.currentPlayer = data.player;
                this.humanScore = this.currentPlayer.score;
                this.updateScoreboard();
                await this.fetchHighestScore();
            }
        } catch (error) {
            console.error('Error scoring round:', error);
        }
        
        this.nextRound();
    }
    
    nextRound() {
        this.pendingAiGuessedCorrectly = null;
        this.state = 'drawing';
        this.hideErrorOverlay();
        this.startRound();
    }
    
    showErrorOverlay(message) {
        this.state = 'error';
        this.disableDrawing();
        this.guessBtn.disabled = true;
        this.hideErrorOverlay();
        this.guessBtn.style.display = '';
        
        if (!this.errorOverlay) this.createErrorOverlay();
        this.errorOverlay.querySelector('.error-overlay-message').textContent = message;
        this.errorOverlay.classList.add('show');
    }
    
    createErrorOverlay() {
        this.errorOverlay = document.createElement('div');
        this.errorOverlay.className = 'error-overlay';
        this.errorOverlay.innerHTML = `
            <div class="error-overlay-content">
                <h3>Oops!</h3>
                <p class="error-overlay-message"></p>
                <div class="error-overlay-buttons">
                    <button id="error-cancel-btn">Cancel</button>
                    <button id="error-refresh-btn">Refresh Page</button>
                </div>
            </div>
        `;
        document.body.appendChild(this.errorOverlay);
        
        this.errorOverlay.querySelector('#error-cancel-btn').addEventListener('click', () => {
            this.hideErrorOverlay();
            if (this.state === 'error' && this.roundActive) {
                this.enableDrawing();
                this.guessBtn.disabled = false;
                this.state = 'drawing';
            }
        });
        
        this.errorOverlay.querySelector('#error-refresh-btn').addEventListener('click', () => {
            window.location.reload();
        });
    }
    
    hideErrorOverlay() {
        if (this.errorOverlay) this.errorOverlay.classList.remove('show');
    }
    
    updateScoreboard() {
        if (this.scoreboard.player) this.scoreboard.player.textContent = this.humanScore;
        if (this.scoreboard.highest) this.scoreboard.highest.textContent = this.highestScore;
        
        [this.scoreboard.player, this.scoreboard.highest].forEach(el => {
            if (el) {
                el.classList.add('score-change');
                setTimeout(() => el.classList.remove('score-change'), 300);
            }
        });
        
        // Also update the start screen leaderboard
        const humanScoreEl = document.getElementById('human-score');
        const aiScoreEl = document.getElementById('ai-score');
        if (humanScoreEl) humanScoreEl.textContent = this.highestScorerName;
        if (aiScoreEl) aiScoreEl.textContent = this.highestScore;
    }

    updateHighestScoreDisplay() {
        const highestScoreEl = document.getElementById('highest-score');
        const humanScoreEl = document.getElementById('human-score');
        const aiScoreEl = document.getElementById('ai-score');
        
        if (highestScoreEl) highestScoreEl.textContent = this.highestScore;
        if (humanScoreEl) humanScoreEl.textContent = this.highestScorerName;
        if (aiScoreEl) aiScoreEl.textContent = this.highestScore;
     }
    // Timer methods
    updateTimerDisplay() {
        const minutes = Math.floor(this.timeRemaining / 60);
        const seconds = this.timeRemaining % 60;
        const timeString = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        this.timerDisplay.textContent = `Time: ${timeString}`;
        this.timerDisplay.style.color = 'var(--color-muted)';
        this.timerDisplay.style.fontSize = '1rem';
        this.timerDisplay.style.marginTop = '0.5rem';
    }

    startTimer() {
        this.timeRemaining = this.timerDuration;
        this.updateTimerDisplay();
        if (this.timerInterval) {
            clearInterval(this.timerInterval);
        }
        this.timerInterval = setInterval(() => {
            this.timeRemaining--;
            this.updateTimerDisplay();
            if (this.timeRemaining <= 0) {
                this.onTimerEnd();
            }
        }, 1000);
    }

    stopTimer() {
        if (this.timerInterval) {
            clearInterval(this.timerInterval);
            this.timerInterval = null;
        }
    }

    async onTimerEnd() {
        this.stopTimer();
        this.state = 'timer-ended';
        this.disableDrawing();
        this.guessBtn.disabled = true;
        this.hideErrorOverlay();
        await this.showTimerEndPopup();
    }

    async showTimerEndPopup() {
        // Fetch leaderboard to get position
        let position = '—';
        try {
            const response = await fetch('/leaderboard');
            const data = await response.json();
            if (data.success && data.players) {
                // Find current player in leaderboard
                const playerEntry = data.players.find(p => p.id === this.currentPlayer?.id);
                if (playerEntry) {
                    position = data.players.findIndex(p => p.id === this.currentPlayer.id) + 1;
                }
            }
        } catch (error) {
            console.error('Error fetching leaderboard for timer end:', error);
        }

        // Setup result card overlay
        this.resultHeading.textContent = 'Time\'s up!';
        const resultContent = document.querySelector('.result-card-content');
        // We'll replace innerHTML with our custom content
        resultContent.innerHTML = `
            <p>Your score: ${this.humanScore}</p>
            <p>Your position: #${position}</p>
            <div class="buttons">
                <button id="end-session-btn">End Session</button>
                <button id="restart-session-btn">Restart Session</button>
            </div>
        `;
        this.resultCard.classList.add('show');

        // Add event listeners
        document.getElementById('end-session-btn').addEventListener('click', () => this.endSession());
        document.getElementById('restart-session-btn').addEventListener('click', () => this.restartSession());

        // Ensure buttons are disabled
        this.resultButtons.correct.disabled = true;
        this.resultButtons.wrong.disabled = true;
        this.guessBtn.disabled = true;
    }

    endSession() {
        this.stopTimer();
        this.showStartScreen();
    }

    restartSession() {
        this.stopTimer();
        this.humanScore = 0;
        this.aiScore = 0;
        this.currentPlayer = null;
        this.playerName = null;
        this.updateScoreboard();
        this.updateHighestScoreDisplay();
        this.clearCanvas();
        this.undoStack = [];
        this.startGame(); // Will start timer after name entry
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.game = new DrawChallengeGame();
});