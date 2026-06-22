<?php

return [
    'api_key' => env('OPENROUTER_API_KEY'),
    'model' => env('OPENROUTER_MODEL', 'sourceful/riverflow-v2.5-pro'),
    'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
    'timeout' => (int) env('OPENROUTER_TIMEOUT', 600),
    'http_referer' => env('OPENROUTER_HTTP_REFERER', env('APP_URL', 'http://localhost')),
    'app_title' => env('OPENROUTER_APP_TITLE', env('APP_NAME', 'Mother Art')),

    'import' => [
        'prompt' => 'Обработай эту картину или керамическую поделку: изолируй объект, разверни рисунок правильной стороной, помести на чистый белый фон, профессиональное студийное освещение, объект по центру кадра. Результат должен быть идеален для карточки товара интернет-магазина. Сохрани точные цвета и детали произведения.',
        'modalities' => ['image'],
        'max_base64_bytes' => (int) env('OPENROUTER_MAX_BASE64_BYTES', 3_500_000),
        'image_config' => [
            'background_mode' => 'solid',
            'background_hex_color' => '#FFFFFF',
            'aspect_ratio' => '1:1',
            'image_size' => env('OPENROUTER_IMPORT_IMAGE_SIZE', '2K'),
            'scoring_prompt' => 'Чистый белый фон, объект по центру, чёткие края, равномерное студийное освещение, без посторонних предметов и артефактов.',
        ],
    ],
];
