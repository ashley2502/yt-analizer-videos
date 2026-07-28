<?php

return [
    'provider' => env('AI_PROVIDER', 'openai'),
    'api_key' => env('OPENAI_API_KEY'),
    'base_url' => env('OPENAI_BASE_URL'),
    'model' => env('OPENAI_MODEL', 'gpt-5-nano'),
    'vision_model' => env('OPENAI_VISION_MODEL', 'gpt-5-nano'),
    'enabled' => (bool) env('OPENAI_API_KEY'),

    'channel' => [
        'name' => env('YT_CHANNEL_NAME', 'HollowGrim Beats'),
        'handle' => env('YT_CHANNEL_HANDLE', '@HollowGrimBeats'),
        'niche' => 'dark boom bap, Griselda, Mobb Deep, Alchemist, underground 90s hip-hop',
        'contact_email' => env('YT_CONTACT_EMAIL', 'asaucedagamez@gmail.com'),
        'weekly_beat_goal' => (int) env('YT_WEEKLY_BEAT_GOAL', 2),
        'weekly_shorts_goal' => (int) env('YT_WEEKLY_SHORTS_GOAL', 4),
    ],
];
