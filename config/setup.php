<?php

return [

    /*
    |--------------------------------------------------------------------------
    | One-Time Setup Token
    |--------------------------------------------------------------------------
    |
    | The secret token that unlocks the school onboarding page. Generate a
    | strong random value (e.g. `php -r "echo bin2hex(random_bytes(32));"`)
    | and set it in your .env as SETUP_TOKEN.
    |
    | The setup link is /setup/{token}. Once an administrator account has been
    | created the link is consumed and can never be used again.
    |
    */

    'token' => env('SETUP_TOKEN', ''),
];
