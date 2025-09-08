<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------- 
    | Default Session Driver
    |--------------------------------------------------------------------------- 
    */
    'driver' => env('SESSION_DRIVER', 'database'),

    /*
    |--------------------------------------------------------------------------- 
    | Session Lifetime
    |--------------------------------------------------------------------------- 
    |
    | Set the session lifetime to 5 minutes (in minutes). This means the user
    | will stay logged in for 5 minutes of inactivity.
    |
    */
    'lifetime' => (int) env('SESSION_LIFETIME', 120),

    /*
    |--------------------------------------------------------------------------- 
    | Session Cookie Lifetime
    |--------------------------------------------------------------------------- 
    |
    | Set the cookie lifetime to match the session lifetime (5 minutes).
    | This ensures the session cookie expires after the session duration.
    |
    */
    'cookie_lifetime' => 120, 

    /*
    |--------------------------------------------------------------------------- 
    | Session Expiry on Close
    |--------------------------------------------------------------------------- 
    |
    | Set to false to prevent the session from expiring immediately when the browser is closed.
    |
    */
    'expire_on_close' => false,  // Session does not expire when the tab is closed

    /*
    |--------------------------------------------------------------------------- 
    | Session Encryption
    |--------------------------------------------------------------------------- 
    |
    | Enable session data encryption.
    |
    */
    'encrypt' => env('SESSION_ENCRYPT', false),

    /*
    |--------------------------------------------------------------------------- 
    | Session File Location
    |--------------------------------------------------------------------------- 
    |
    | When using "file" session driver, this is the location where session files are stored.
    |
    */
    'files' => storage_path('framework/sessions'),

    /*
    |--------------------------------------------------------------------------- 
    | Session Database Connection
    |--------------------------------------------------------------------------- 
    |
    | The database connection to use when using the "database" session driver.
    |
    */
    'connection' => env('SESSION_CONNECTION'),

    /*
    |--------------------------------------------------------------------------- 
    | Session Database Table
    |--------------------------------------------------------------------------- 
    |
    | The table to use when using the "database" session driver.
    |
    */
    'table' => env('SESSION_TABLE', 'sessions'),

    /*
    |--------------------------------------------------------------------------- 
    | Session Cache Store
    |--------------------------------------------------------------------------- 
    |
    | The cache store to use for session storage when using cache drivers like Redis.
    |
    */
    'store' => env('SESSION_STORE'),

    /*
    |--------------------------------------------------------------------------- 
    | Session Sweeping Lottery
    |--------------------------------------------------------------------------- 
    |
    | The odds that the session will be cleaned on a given request.
    |
    */
    'lottery' => [2, 100],

    /*
    |--------------------------------------------------------------------------- 
    | Session Cookie Name
    |--------------------------------------------------------------------------- 
    |
    | The name of the session cookie.
    |
    */
    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug(env('APP_NAME', 'laravel'), '_').'_session'
    ),

    /*
    |--------------------------------------------------------------------------- 
    | Session Cookie Path
    |--------------------------------------------------------------------------- 
    |
    | The path for which the cookie will be valid.
    |
    */
    'path' => env('SESSION_PATH', '/'),

    /*
    |--------------------------------------------------------------------------- 
    | Session Cookie Domain
    |--------------------------------------------------------------------------- 
    |
    | The domain for which the session cookie will be valid.
    |
    */
    'domain' => env('SESSION_DOMAIN'),

    /*
    |--------------------------------------------------------------------------- 
    | HTTPS Only Cookies
    |--------------------------------------------------------------------------- 
    |
    | Set this to true to restrict session cookies to HTTPS connections only.
    |
    */
    'secure' => env('SESSION_SECURE_COOKIE', false), // Set to true for HTTPS connections

    /*
    |--------------------------------------------------------------------------- 
    | HTTP Access Only
    |--------------------------------------------------------------------------- 
    |
    | Prevent JavaScript access to the session cookie.
    |
    */
    'http_only' => env('SESSION_HTTP_ONLY', true),

    /*
    |--------------------------------------------------------------------------- 
    | Same-Site Cookies
    |--------------------------------------------------------------------------- 
    |
    | Set to "lax" for cross-site requests.
    |
    */
    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    /*
    |--------------------------------------------------------------------------- 
    | Partitioned Cookies
    |--------------------------------------------------------------------------- 
    |
    | Set to true to enable partitioned cookies for better security in cross-site contexts.
    |
    */
    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),
];
