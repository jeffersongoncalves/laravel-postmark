<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Postmark API URL
    |--------------------------------------------------------------------------
    */
    'api_url' => env('POSTMARK_API_URL', 'https://api.postmarkapp.com'),

    /*
    |--------------------------------------------------------------------------
    | Postmark Server API Token
    |--------------------------------------------------------------------------
    |
    | Find it under Servers > (your server) > API Tokens in your Postmark
    | account. Sent as the "X-Postmark-Server-Token" header.
    |
    */
    'token' => env('POSTMARK_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Default Message Stream
    |--------------------------------------------------------------------------
    |
    | Used when no explicit stream is given for sends and suppressions.
    |
    */
    'message_stream' => env('POSTMARK_MESSAGE_STREAM', 'outbound'),

    /*
    |--------------------------------------------------------------------------
    | Default Pagination Count
    |--------------------------------------------------------------------------
    |
    | Used as the default "count" for list endpoints when none is given.
    |
    */
    'default_count' => env('POSTMARK_DEFAULT_COUNT', 50),

];
