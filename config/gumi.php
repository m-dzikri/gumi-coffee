<?php

return [
    'operating_hours' => [
        'open' => env('GUMI_OPEN_TIME', '08:00'),
        'close' => env('GUMI_CLOSE_TIME', '22:00'),
    ],
    'estimated_pickup_text' => env('GUMI_ESTIMATED_PICKUP_TEXT', 'Estimasi siap 10-15 menit'),
    'categories' => ['Coffee', 'Non-Coffee', 'Pastry'],
    'location' => 'Jl. Braga No. 42, Bandung',
];
