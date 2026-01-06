<?php

$isLocal = isLocal();

return [
    /*
     |--------------------------------------------------------------------------
     | Debugbar Settings
     |--------------------------------------------------------------------------
     |
     | Debugbar is enabled by default, when debug is set to true in app.php.
     | You can override the value by setting enable to true or false instead of null.
     |
     | You can provide an array of URI's that must be ignored (eg. 'api/*')
     |
     */

    'enabled' => false,
    'hide_empty_tabs' => true, // Hide tabs until they have content
    'except' => [
        'telescope*',
        'horizon*',
        '_boost/browser-logs',
    ],

    /*
     |--------------------------------------------------------------------------
     | Inject Debugbar in Response
     |--------------------------------------------------------------------------
     |
     | Usually, the debugbar is added just before </body>, by listening to the
     | Response after the App is done. If you disable this, you have to add them
     | in your template yourself. See http://phpdebugbar.com/docs/rendering.html
     |
     */

    'inject' => $isLocal,

    /*
     |--------------------------------------------------------------------------
     | Debugbar route prefix
     |--------------------------------------------------------------------------
     |
     | Sometimes you want to set route prefix to be used by Debugbar to load
     | its resources from. Usually the need comes from misconfigured web server or
     | from trying to overcome bugs like this: http://trac.nginx.org/nginx/ticket/97
     |
     */
    'route_prefix' => env('DEBUGBAR_ROUTE_PREFIX', '_debugbar'),

    /*
     |--------------------------------------------------------------------------
     | Debugbar route middleware
     |--------------------------------------------------------------------------
     |
     | Additional middleware to run on the Debugbar routes
     */
    'route_middleware' => [],

    /*
     |--------------------------------------------------------------------------
     | Debugbar route domain
     |--------------------------------------------------------------------------
     |
     | By default Debugbar route served from the same domain that request served.
     | To override default domain, specify it as a non-empty value.
     */
    'route_domain' => env('DEBUGBAR_ROUTE_DOMAIN'),

    /*
     |--------------------------------------------------------------------------
     | Debugbar theme
     |--------------------------------------------------------------------------
     |
     | Switches between light and dark theme. If set to auto it will respect system preferences
     | Possible values: auto, light, dark
     */
    'theme' => env('DEBUGBAR_THEME', 'auto'),

    /*
     |--------------------------------------------------------------------------
     | Backtrace stack limit
     |--------------------------------------------------------------------------
     |
     | By default, the Debugbar limits the number of frames returned by the 'debug_backtrace()' function.
     | If you need larger stacktraces, you can increase this number. Setting it to 0 will result in no limit.
     */
    'debug_backtrace_limit' => (int) env('DEBUGBAR_DEBUG_BACKTRACE_LIMIT', 50),
];
