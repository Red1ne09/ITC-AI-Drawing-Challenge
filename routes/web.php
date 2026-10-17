<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;
use App\Http\Controllers\GuessController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\DrawingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you register web routes for the application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [GameController::class, 'index'])->name('game');

Route::post('/game/new-round', [GameController::class, 'newRound'])->name('game.new-round');

Route::post('/game/guess', [GuessController::class, 'guess'])->name('game.guess');

Route::post('/player', [PlayerController::class, 'store'])->name('player.store');
Route::post('/player/{player}/score', [PlayerController::class, 'scoreRound'])->name('player.score');
Route::get('/leaderboard', [PlayerController::class, 'leaderboard'])->name('leaderboard');
Route::get('/highest-score', [PlayerController::class, 'highestScore'])->name('highest-score');
Route::get('/itc-admin-18-10-18-26', [PlayerController::class, 'dashboard'])->name('player.dashboard');
Route::delete('/player/{player}', [PlayerController::class, 'destroy'])->name('player.destroy');

// Drawing routes
Route::post('/drawing/save', [DrawingController::class, 'save'])->name('drawing.save');
Route::get('/drawing/gallery', [DrawingController::class, 'gallery'])->name('drawing.gallery');