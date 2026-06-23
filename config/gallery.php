<?php

return [
    'admin_email' => env('ADMIN_EMAIL', 'elena-burkaltseva@yandex.ru'),
    'admin_name' => env('ADMIN_NAME', 'Елена Буркальцева'),
    'admin_password' => env('ADMIN_PASSWORD'),
    'admin_order_email' => env('ADMIN_ORDER_EMAIL', env('MAIL_FROM_ADDRESS', 'owner@example.com')),
    'import_path' => storage_path('app/import-artworks'),
];
