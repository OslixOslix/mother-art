<?php

return [
    'admin_order_email' => env('ADMIN_ORDER_EMAIL', env('MAIL_FROM_ADDRESS', 'owner@example.com')),
    'import_path' => storage_path('app/import-artworks'),
];
