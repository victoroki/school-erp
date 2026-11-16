<?php

return [
    'api_key' => env('SOZURI_API_KEY', ''),
    'project' => env('SOZURI_PROJECT', ''),
    'sender_id' => env('SOZURI_SENDER_ID', ''),
    'message_type' => env('SOZURI_MESSAGE_TYPE', 'transactional'),
    'auth_key' => env('SOZURI_AUTH_KEY'),
];
