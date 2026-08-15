<?php

return [
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'marketing_model' => env('OPENAI_MARKETING_MODEL', 'gpt-5-mini'),
    ],
    'meta' => [
        'app_id' => env('META_APP_ID'),
        'app_secret' => env('META_APP_SECRET'),
        'page_id' => env('META_PAGE_ID'),
        'page_access_token' => env('META_PAGE_ACCESS_TOKEN'),
        'verify_token' => env('META_WEBHOOK_VERIFY_TOKEN'),
    ],
    'whatsapp' => [
        'graph_version' => env('WHATSAPP_GRAPH_VERSION'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID'),
        'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
    ],
    'batixpro' => [
        'base_url' => env('BATIXPRO_API_URL'),
        'token' => env('BATIXPRO_API_TOKEN'),
    ],
];
