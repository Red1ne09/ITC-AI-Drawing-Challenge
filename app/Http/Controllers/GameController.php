<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GameController extends Controller
{
    /**
     * Show the game page.
     */
    public function index()
    {
        $topPlayer = Player::leaderboard()->first();
        $highestScore = $topPlayer ? $topPlayer->score : 0;
        $highestScorerName = $topPlayer ? $topPlayer->name : '—';

        return view('welcome', compact('highestScore', 'highestScorerName'));
    }

    /**
     * Start a new round and return a random prompt.
     */
    public function newRound(Request $request)
    {
        $prompts = config('draw_challenge.prompts');

        // Avoid selecting the same prompt consecutively if possible
        $lastPrompt = $request->session()->get('last_prompt');
        do {
            $prompt = $prompts[array_rand($prompts)];
        } while ($lastPrompt && $prompt === $lastPrompt && count($prompts) > 1);

        $request->session()->put('last_prompt', $prompt);

        return response()->json([
            'success' => true,
            'prompt' => $prompt,
        ]);
    }
}
