<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Player;

class DrawingController extends Controller
{
    /**
     * Save a drawing to storage.
     */
    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|file|image|max:2048', // 2MB max
            'player_id' => 'required|integer|exists:players,id',
            'drawing_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $image = $request->file('image');
            $playerId = $request->input('player_id');
            $drawingName = $request->input('drawing_name');
            
            // Generate a unique ID for the drawing
            $drawingId = uniqid();
            
            // Get file extension
            $extension = $image->getClientOriginalExtension();
            if (!$extension) {
                $extension = 'png'; // Default to png if no extension
            }
            
            // Create the file path: /[player_id]/[drawing_name][drawing_id].extension
            $filePath = "{$playerId}/{$drawingName}{$drawingId}.{$extension}";
            
            // Store the file
            $path = Storage::disk('public')->putFileAs(
                dirname($filePath), 
                $image, 
                basename($filePath)
            );
            
            // Get the URL for the stored file
            $url = Storage::disk('public')->url($path);
            
            return response()->json([
                'success' => true,
                'message' => 'Drawing saved successfully',
                'data' => [
                    'file_path' => $filePath,
                    'url' => $url,
                    'drawing_id' => $drawingId,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error saving drawing: '.$e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save drawing. Please try again.',
            ], 500);
        }
    }
    
    /**
     * Show gallery of saved drawings.
     */
    public function gallery()
    {
        // Get all files from the public storage directory
        $files = Storage::disk('public')->allFiles();
        
        $drawings = [];
        
        foreach ($files as $file) {
            // Only process image files
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                continue;
            }
            
            // Parse the file path to extract player_id and drawing info
            // Format: {player_id}/{drawing_name}{drawing_id}.{extension}
            $pathParts = explode('/', $file);
            if (count($pathParts) < 2) {
                continue;
            }
            
            $playerId = $pathParts[0];
            $filenameWithExtension = $pathParts[1];
            
            // Remove extension to get drawing_name + drawing_id
            $filename = pathinfo($filenameWithExtension, PATHINFO_FILENAME);
            
            // Try to get player info
            $player = Player::find($playerId);
            $playerName = $player ? $player->name : 'Unknown Player (#'.$playerId.')';
            
            $drawings[] = [
                'file_path' => $file,
                'url' => Storage::disk('public')->url($file),
                'player_id' => (int)$playerId,
                'player_name' => $playerName,
                'filename' => $filename,
                'extension' => $extension,
                'full_filename' => $filenameWithExtension,
            ];
        }
        
        // Sort by upload time (newest first)
        usort($drawings, function($a, $b) {
            $timeA = Storage::disk('public')->lastModified($a['file_path']);
            $timeB = Storage::disk('public')->lastModified($b['file_path']);
            return $timeB - $timeA;
        });
        
        return response()->json([
            'success' => true,
            'drawings' => $drawings,
        ]);
    }
}