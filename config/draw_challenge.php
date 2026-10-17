<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Game Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the AI Draw Challenge game.
    |
    */

    'club_name' => env('DRAW_CHALLENGE_CLUB_NAME', 'AI Club'),

    'club_logo' => env('DRAW_CHALLENGE_CLUB_LOGO', ''),

    'game_title' => env('DRAW_CHALLENGE_GAME_TITLE', 'AI Draw Challenge'),

    'game_description' => env('DRAW_CHALLENGE_GAME_DESCRIPTION', 'You draw it. Our AI tries to guess it.'),

    /*
    |--------------------------------------------------------------------------
    | Drawing Prompts
    |--------------------------------------------------------------------------
    |
    | List of words that the player can be asked to draw.
    |
    */

    'prompts' => [

        // Animals
        'cat', 'dog', 'fish', 'rabbit', 'elephant', 'butterfly',
        'bird', 'horse', 'turtle', 'frog',

        // Objects
        'umbrella', 'key', 'clock', 'chair', 'bicycle', 'camera',
        'phone', 'laptop', 'book', 'bottle',

        // Food
        'pizza', 'apple', 'ice cream', 'burger', 'banana',
        'orange', 'carrot', 'bread', 'cheese',

        // Nature
        'tree', 'flower', 'sun', 'moon', 'cloud', 'mountain',
        'star', 'rainbow', 'river', 'fruit',

        // Transport
        'car', 'airplane', 'boat', 'train', 'rocket',
        'bus', 'truck', 'ship', 'skateboard',

        // Everyday concepts
        'house', 'book', 'guitar', 'phone', 'lamp', 'crown',
        'hat', 'shoe', 'clock', 'window',

        // Add more to reach 40-60
        'smile', 'heart', 'star', 'cloud', 'sun', 'moon',
        'robot', 'alien', 'dinosaur', 'flower',
        'house', 'car', 'tree', 'fish', 'bird',
        'key', 'door', 'window', 'chair', 'table',
        'cup', 'plate', 'spoon', 'fork', 'knife',
        'hat', 'shoe', 'shirt', 'pants', 'dress',
        'watch', 'ring', 'necklace', 'bracelet',
        'ball', 'bat', 'racket', 'net',
        'tree', 'house', 'sun', 'cloud', 'wind',
        'fire', 'earth',
        'music', 'note', 'guitar', 'piano', 'drum',
        'tv', 'phone', 'computer', 'camera', 'microwave',
        'refrigerator', 'oven', 'toaster', 'blender',
        'clock', 'watch',
        'book', 'pen', 'pencil', 'eraser', 'ruler',
        'bag', 'box', 'basket', 'trash', 'recycle',
        'lamp', 'bulb',
        'door', 'window', 'wall',
        'chair', 'table', 'sofa', 'bed', 'pillow',
        'mirror', 'picture', 'poster', 'clock', 'vase',
        'plant', 'flower', 'tree', 'leaf',
        'fruit', 'apple', 'banana', 'orange', 'grape',
        'pizza', 'burger', 'sandwich', 'salad',
        'cheese',
        'bread', 'cake', 'cookie', 'pie', 'donut',
        'coffee',
        'cat', 'dog', 'fish', 'bird', 'horse',
        'cow', 'pig', 'sheep', 'rabbit', 'turtle',
        'frog', 'snake', 'spider', 'bee', 'ant',
        'butterfly', 'beetle', 'lion', 'tiger', 'bear',
        'wolf', 'fox', 'deer', 'elephant', 'giraffe',
        'zebra', 'monkey', 'kangaroo', 'koala', 'panda'

    ],

    /*
    |--------------------------------------------------------------------------
    | Game Rules
    |--------------------------------------------------------------------------
    |
    | Scoring and game mechanics.
    |
    */

    'points_per_round' => 1,

    /*
    |--------------------------------------------------------------------------
    | Canvas Settings
    |--------------------------------------------------------------------------
    |
    | Default canvas dimensions and styling.
    |
    */

    'canvas_width' => 700,
    'canvas_height' => 450,

    /*
    |--------------------------------------------------------------------------
    | Image Upload Limits
    |--------------------------------------------------------------------------
    |
    | Constraints for the drawing upload.
    |
    */

    'max_image_kb' => env('DRAW_CHALLENGE_MAX_IMAGE_KB', 2048),
    'max_image_dimension' => env('DRAW_CHALLENGE_MAX_IMAGE_DIMENSION', 2048),

];
