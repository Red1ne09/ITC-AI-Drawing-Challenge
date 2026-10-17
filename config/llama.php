<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Llama Server Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for connecting to the local llama-server instance.
    |
    */

    'base_url' => env('LLAMA_BASE_URL', 'http://0.0.0.0:8080'),

    'api_key' => env('LLAMA_API_KEY', ''),

    'model' => env('LLAMA_MODEL', 'local-vision-model'),

    'connect_timeout' => env('LLAMA_CONNECT_TIMEOUT', 5),

    'timeout' => env('LLAMA_TIMEOUT', 120),

    'max_tokens' => env('LLAMA_MAX_TOKENS', 100),

    'temperature' => env('LLAMA_TEMPERATURE', 0.1),

];
