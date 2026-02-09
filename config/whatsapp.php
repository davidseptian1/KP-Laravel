<?php

return [
    'base_url' => env('WHATSAPP_METRIC_BASE_URL', 'https://whatsapp.zppayment.id'),
    'endpoint' => env('WHATSAPP_METRIC_ENDPOINT', '/api/send-message'),
    'token' => env('WHATSAPP_METRIC_TOKEN'),
    'api_key' => env('WHATSAPP_METRIC_API_KEY'),
    'sender' => env('WHATSAPP_METRIC_SENDER'),
    'admin_numbers' => array_filter(array_map('trim', explode(',', env('WHATSAPP_ADMIN_NUMBERS', '')))),
];
