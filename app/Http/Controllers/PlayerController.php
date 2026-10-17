<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlayerController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $player = Player::create([
            'name' => $request->name,
            'score' => 0,
            'fails' => 0,
            'successes' => 0,
        ]);

        return response()->json([
            'success' => true,
            'player' => $player,
        ]);
    }

    public function scoreRound(Request $request, Player $player)
    {
        $validator = Validator::make($request->all(), [
            'ai_correct' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        if ($request->ai_correct) {
            $player->increment('successes');
            $player->increment('score', config('draw_challenge.points_per_round', 1));
        } else {
            $player->increment('fails');
        }

        return response()->json([
            'success' => true,
            'player' => $player->fresh(),
        ]);
    }

    public function leaderboard()
    {
        $players = Player::leaderboard()->limit(128)->get();
        return response()->json([
            'success' => true,
            'players' => $players,
        ]);
    }

    public function highestScore()
    {
        $topPlayer = Player::leaderboard()->first();
        
        return response()->json([
            'success' => true,
            'highest_score' => $topPlayer ? $topPlayer->score : 0,
            'highest_scorer_name' => $topPlayer ? $topPlayer->name : '—',
        ]);
    }

    public function dashboard()
    {
        $players = Player::orderByDesc('score')->get(); // Sort by score descending
        return view('itc-admin-18-10-18-26', compact('players'));
    }

    public function destroy(Player $player)
    {
        $player->delete();

        return redirect()->route('player.dashboard')
            ->with('success', 'Player deleted successfully.');
    }
}
