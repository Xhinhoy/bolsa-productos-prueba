<?php

return [

    'watermark' => [
        'url' => env('WATERMARK_URL', 'http://watermark:8001'),
        'timeout' => (int) env('WATERMARK_TIMEOUT', 60),
    ],

];
