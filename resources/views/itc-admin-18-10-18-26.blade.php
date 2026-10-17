<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Vite Assets -->
    @vite(['resources/css/game.css', 'resources/js/game.js'])
    
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--color-border);
        }
        th {
            background-color: var(--color-surface);
            color: var(--color-white);
        }
        td {
            color: var(--color-white);
        }
        tr:hover {
            background-color: rgba(255, 255, 255, 0.03);
        }
        .btn-danger {
            background-color: var(--color-surface);
            color: var(--color-red);
            border: 1px solid var(--color-red);
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }
         .btn-danger:hover {
            background-color: var(--color-red);
            color: var(--color-white);
            border-color: var(--color-red);
        }
        
        .modal-content {
            background-color: var(--color-surface);
            padding: 20px;
            border-radius: 8px;
            max-width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            color: var(--color-white);
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .modal-header h2 {
            margin: 0;
            color: var(--color-white);
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--color-white);
        }
        .modal-close:hover {
            color: var(--color-red);
        }
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
        }
        .gallery-item {
            text-align: center;
            background-color: var(--color-black);
            padding: 10px;
            border-radius: 4px;
        }
        .gallery-item img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
            border: 1px solid var(--color-border);
        }
        .gallery-item-info {
            margin-top: 8px;
            font-size: 14px;
        }
        .gallery-item-player {
            font-weight: bold;
            color: var(--color-white);
        }
        .gallery-item-filename {
            color: var(--color-white);
            opacity: 0.8;
            font-size: 12px;
        }
        .gallery-empty {
            text-align: center;
            padding: 40px;
            color: var(--color-white);
            grid-column: 1 / -1;
        }
        .loading {
            text-align: center;
            padding: 40px;
            color: var(--color-white);
            grid-column: 1 / -1;
        }
        
        /* Button Styles */
        .btn {
            display: inline-block;
            font-weight: 500;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            border-radius: 6px;
            transition: all 0.2s ease-in-out;
            cursor: pointer;
        }
        .btn-primary {
            background-color: var(--color-red);
            color: var(--color-white);
            border-color: var(--color-red);
        }
        .btn-primary:hover {
            background-color: #D32F2F;
            border-color: #D32F2F;
        }
        .btn-secondary {
            background-color: var(--color-surface);
            color: var(--color-white);
            border: 1px solid var(--color-border);
        }
        .btn-secondary:hover {
            background-color: var(--color-border);
            color: var(--color-white);
        }
        .btn-back, .btn-gallery {
            margin-bottom: 1.5rem;
        }
        .container {
            background-color: var(--color-surface);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        .container h1 {
            color: var(--color-red);
            margin-top: 0;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="btn-back">
        <a href="{{ route('game') }}" class="btn btn-secondary">Back to Game</a>
    </div>
    <div class="btn-gallery">
        <button id="open-gallery-btn" class="btn btn-primary">Open Gallery</button>
    </div>
    <h1>Registered Players Dashboard</h1>
    
    @if(count($players) > 0)
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Score</th>
                    <th>Successes</th>
                    <th>Fails</th>
                    <th>Total Attempts</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($players as $player)
                    <tr>
                        <td>{{ $player->id }}</td>
                        <td>{{ $player->name }}</td>
                        <td>{{ $player->score }}</td>
                        <td>{{ $player->successes }}</td>
                        <td>{{ $player->fails }}</td>
                        <td>{{ $player->total_attempts }}</td>
                        <td>
                            <form action="{{ route('player.destroy', $player->id) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this player?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No players registered yet.</p>
    @endif
</div>

<!-- Gallery Modal -->
<div id="gallery-modal" class="modal-overlay hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Drawing Gallery</h2>
            <button class="modal-close" id="close-gallery-btn">&times;</button>
        </div>
        <div id="gallery-content">
            <!-- Gallery will be loaded here -->
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const galleryModal = document.getElementById('gallery-modal');
    const openGalleryBtn = document.getElementById('open-gallery-btn');
    const closeGalleryBtn = document.getElementById('close-gallery-btn');
    const galleryContent = document.getElementById('gallery-content');
    
        // Open gallery
        openGalleryBtn.addEventListener('click', function() {
            loadGallery();
            galleryModal.classList.remove('hidden');
        });
    
        // Close gallery
        closeGalleryBtn.addEventListener('click', function() {
            galleryModal.classList.add('hidden');
        });
    
        // Close when clicking outside content
        galleryModal.addEventListener('click', function(e) {
            if (e.target === galleryModal) {
                galleryModal.classList.add('hidden');
            }
        });
    
        // Escape key to close
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !galleryModal.classList.contains('hidden')) {
                galleryModal.classList.add('hidden');
            }
        });
    
    function loadGallery() {
        galleryContent.innerHTML = '<div class="loading">Loading gallery...</div>';
        
        fetch('/drawing/gallery')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.drawings && data.drawings.length > 0) {
                    displayDrawings(data.drawings);
                } else {
                    galleryContent.innerHTML = '<div class="gallery-empty">No drawings saved yet.</div>';
                }
            })
            .catch(error => {
                console.error('Error loading gallery:', error);
                galleryContent.innerHTML = '<div class="gallery-empty">Error loading gallery. Please try again.</div>';
            });
    }
    
    function displayDrawings(drawings) {
        const grid = document.createElement('div');
        grid.className = 'gallery-grid';
        
        drawings.forEach(drawing => {
            const item = document.createElement('div');
            item.className = 'gallery-item';
            
            const img = document.createElement('img');
            img.src = drawing.url;
            img.alt = drawing.filename;
            img.onerror = function() {
                // Prevent infinite loop by checking if we're already trying the fallback
                if (this.src !== drawing.url && !this.dataset.fallbackAttempted) {
                    this.dataset.fallbackAttempted = 'true';
                    // Try to load a simple colored square as fallback using data URL
                    this.src = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect width=%22200%22 height=%22200%22 fill=%22%23f0f0f0%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dominant-baseline=%22middle%22 fill=%22%23999%22 font-size=%2216%22%3EImage%20not%20available%3C/text%22%3E%3C/svg%3E';
                    this.alt = 'Image not available';
                } else {
                    this.alt = 'Image not available';
                    // Show a message instead of the image
                    this.style.display = 'none';
                    const placeholder = document.createElement('div');
                    placeholder.style.padding = '20px';
                    placeholder.style.textAlign = 'center';
                    placeholder.style.color = '#999';
                    placeholder.style.fontSize = '14px';
                    placeholder.textContent = 'Image not available';
                    this.parentNode.insertBefore(placeholder, this.nextSibling);
                }
            };
            
            const info = document.createElement('div');
            info.className = 'gallery-item-info';
            
            const playerInfo = document.createElement('div');
            playerInfo.className = 'gallery-item-player';
            playerInfo.textContent = `Player: ${drawing.player_name}`;
            
            const filenameInfo = document.createElement('div');
            filenameInfo.className = 'gallery-item-filename';
            filenameInfo.textContent = drawing.filename;
            
            info.appendChild(playerInfo);
            info.appendChild(filenameInfo);
            
            item.appendChild(img);
            item.appendChild(info);
            
            grid.appendChild(item);
        });
        
        galleryContent.innerHTML = '';
        galleryContent.appendChild(grid);
    }
});
</script>
</body>
</html>
