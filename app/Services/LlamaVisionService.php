<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LlamaVisionService
{
    protected $baseUrl;
    protected $apiKey;
    protected $model;
    protected $connectTimeout;
    protected $timeout;
    protected $maxTokens;
    protected $temperature;

    public function __construct()
    {
        $this->baseUrl = config('llama.base_url');
        $this->apiKey = config('llama.api_key');
        $this->model = config('llama.model');
        $this->connectTimeout = (int) config('llama.connect_timeout');
        $this->timeout = (int) config('llama.timeout');
        $this->maxTokens = (int) config('llama.max_tokens');
        $this->temperature = (float) config('llama.temperature');
    }

    /**
     * Get a guess from the vision model for the given image.
     *
     * @param string $base64Image Base64 encoded PNG image
     * @return string|null The guess or null if failed
     */
    public function getGuess(string $base64Image)
    {
        $response = Http::withHeaders([
            'Authorization' => $this->apiKey ? 'Bearer ' . $this->apiKey : null,
            'Content-Type' => 'application/json',
        ])
        ->withOptions([
            'connect_timeout' => $this->connectTimeout,
            'timeout' => $this->timeout,
        ])
        ->post($this->baseUrl . '/v1/chat/completions', [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Identify the main object in this simple black-and-white doodle. Respond with your best guess in a few words. If the drawing is unclear, explicitly say you are unsure. Do not describe the drawing at length.'
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => 'data:image/png;base64,' . $base64Image
                            ]
                        ]
                    ]
                ]
            ],
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxTokens,
            'stream' => false,
        ]);

        if ($response->failed()) {
            return null;
        }

        $data = $response->json();

        if (!isset($data['choices'][0]['message']['content'])) {
            return null;
        }

        $guess = trim($data['choices'][0]['message']['content']);

        // Remove any surrounding quotes or extra whitespace
        $guess = trim($guess, '"\'');

        // If the guess is empty, return null
        if (empty($guess)) {
            return null;
        }

        return $guess;
    }
}
