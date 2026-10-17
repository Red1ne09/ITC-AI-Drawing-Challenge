<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Services\LlamaVisionService;

class GuessController extends Controller
{
    protected $llamaVisionService;

    public function __construct(LlamaVisionService $llamaVisionService)
    {
        Log::debug('GuessController constructor called');
        $this->llamaVisionService = $llamaVisionService;
        // Trigger an error to see if logging works
        // undefined_function();
    }

    /**
     * Process a drawing guess.
     */
    public function guess(Request $request)
    {
        Log::debug('GuessController::guess method called');
        
        $validator = Validator::make($request->all(), [
            'image' => 'required|file',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed in GuessController: '.$validator->errors()->toJson());
            return response()->json([
                'success' => false,
                'message' => 'Invalid image. Please upload a valid PNG image.',
            ], 422);
        }

        try {
            // Get the image content
            $image = $request->file('image');
            $imageBase64 = base64_encode(file_get_contents($image->getRealPath()));

            // Get the guess from the vision model
            $guess = $this->llamaVisionService->getGuess($imageBase64);

            if ($guess === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'The AI was unable to make a guess. Please try again.',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'guess' => $guess,
            ]);
        } catch (\Exception $e) {
            // Log the exception for debugging (optional)
            Log::error('Error in GuessController: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'The AI is unavailable right now. Please try again.',
            ], 503);
        }
    }
}
