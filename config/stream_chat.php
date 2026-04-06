<?php

return [
    'api_key' => env('STREAM_CHAT_API_KEY'),
    'api_secret' => env('STREAM_CHAT_API_SECRET'),
    'channel_type' => env('STREAM_CHAT_CHANNEL_TYPE', 'messaging'),
    'channel_prefix' => env('STREAM_CHAT_CHANNEL_PREFIX', 'support'),
    'agent_user_id' => env('STREAM_CHAT_AGENT_USER_ID', 'support_agent_1'),
];
