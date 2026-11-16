<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Access Token Lifetime
    |--------------------------------------------------------------------------
    |
    | Number of minutes until a mobile access token expires. The React Native
    | client silently refreshes before this window closes.
    |
    */

    'access_token_minutes' => (int) env('MOBILE_ACCESS_TOKEN_MINUTES', 45),

    /*
    |--------------------------------------------------------------------------
    | Refresh Token Lifetime
    |--------------------------------------------------------------------------
    |
    | Number of days until a mobile refresh token expires. This implements
    | the "sliding session" described in PRODUCT-MOBILE.md §4.2: each
    | successful refresh extends the window by this many days.
    |
    */

    'refresh_token_days' => (int) env('MOBILE_REFRESH_TOKEN_DAYS', 90),

];
