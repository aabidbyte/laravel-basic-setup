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
    'hide_empty_tabs' => false, // Hide tabs until they have content
    'except' => [
        'telescope*',
        'horizon*',
        '_boost/browser-logs',
    ],

    /*
     |--------------------------------------------------------------------------
     | Storage settings
     |--------------------------------------------------------------------------
     |
     | Debugbar stores data for session/ajax requests.
     | You can disable this, so the debugbar stores data in headers/session,
     | but this can cause problems with large data collectors.
     | By default, file storage (in the storage folder) is used. Redis and PDO
     | can also be used. For PDO, run the package migrations first.
     |
     | Warning: Enabling storage.open will allow everyone to access previous
     | request, do not enable open storage in publicly available environments!
     | Specify a callback if you want to limit based on IP or authentication.
     | Leaving it to null will allow localhost only.
     */
    'storage' => [
        'enabled' => $isLocal,
        'open' => env('DEBUGBAR_OPEN_STORAGE'), // bool/callback.
        'driver' => 'redis', // redis, file, pdo, socket, custom
        'path' => storage_path('debugbar'), // For file driver
    ],

    /*
    |--------------------------------------------------------------------------
    | Editor
    |--------------------------------------------------------------------------
    |
    | Choose your preferred editor to use when clicking file name.
    |
    | Supported: "phpstorm", "vscode", "vscode-insiders", "vscode-remote",
    |            "vscode-insiders-remote", "vscodium", "textmate", "emacs",
    |            "sublime", "atom", "nova", "macvim", "idea", "netbeans",
    |            "xdebug", "espresso"
    |
    */

    'editor' => env('DEBUGBAR_EDITOR') ?: env('IGNITION_EDITOR', 'phpstorm'),

    /*
    |--------------------------------------------------------------------------
    | Remote Path Mapping
    |--------------------------------------------------------------------------
    |
    | If you are using a remote dev server, like Laravel Homestead, Docker, or
    | even a remote VPS, it will be necessary to specify your path mapping.
    |
    | Leaving one, or both of these, empty or null will not trigger the remote
    | URL changes and Debugbar will treat your editor links as local files.
    |
    | "remote_sites_path" is an absolute base path for your sites or projects
    | in Homestead, Vagrant, Docker, or another remote development server.
    |
    | Example value: "/home/vagrant/Code"
    |
    | "local_sites_path" is an absolute base path for your sites or projects
    | on your local computer where your IDE or code editor is running on.
    |
    | Example values: "/Users/<name>/Code", "C:\Users\<name>\Documents\Code"
    |
    */

    'remote_sites_path' => env('DEBUGBAR_REMOTE_SITES_PATH'),
    'local_sites_path' => env('DEBUGBAR_LOCAL_SITES_PATH', env('IGNITION_LOCAL_SITES_PATH')),

    /*
     |--------------------------------------------------------------------------
     | Vendors
     |--------------------------------------------------------------------------
     |
     | Vendor files are included by default, but can be set to false.
     | This can also be set to 'js' or 'css', to only include javascript or css vendor files.
     | Vendor files are for css: font-awesome (including fonts) and highlight.js (css files)
     | and for js: jquery and highlight.js
     | So if you want syntax highlighting, set it to true.
     | jQuery is set to not conflict with existing jQuery scripts.
     |
     */

    'include_vendors' => $isLocal,

    /*
     |--------------------------------------------------------------------------
     | Capture Ajax Requests
     |--------------------------------------------------------------------------
     |
     | The Debugbar can capture Ajax requests and display them. If you don't want this (ie. because of errors),
     | you can use this option to disable sending the data through the headers.
     |
     | Optionally, you can also send ServerTiming headers on ajax requests for the Chrome DevTools.
     |
     | Note for your request to be identified as ajax requests they must either send the header
     | X-Requested-With with the value XMLHttpRequest (most JS libraries send this), or have application/json as a Accept header.
     |
     | By default `ajax_handler_auto_show` is set to true allowing ajax requests to be shown automatically in the Debugbar.
     | Changing `ajax_handler_auto_show` to false will prevent the Debugbar from reloading.
     |
     | You can defer loading the dataset, so it will be loaded with ajax after the request is done. (Experimental)
     */

    'capture_ajax' => $isLocal,
    'add_ajax_timing' => $isLocal,
    'ajax_handler_auto_show' => $isLocal,
    'ajax_handler_enable_tab' => $isLocal,
    'defer_datasets' => $isLocal,
    /*
     |--------------------------------------------------------------------------
     | Custom Error Handler for Deprecated warnings
     |--------------------------------------------------------------------------
     |
     | When enabled, the Debugbar shows deprecated warnings for Symfony components
     | in the Messages tab.
     |
     */
    'error_handler' => $isLocal,

    /*
     |--------------------------------------------------------------------------
     | Clockwork integration
     |--------------------------------------------------------------------------
     |
     | The Debugbar can emulate the Clockwork headers, so you can use the Chrome
     | Extension, without the server-side code. It uses Debugbar collectors instead.
     |
     */
    'clockwork' => $isLocal,

    /*
     |--------------------------------------------------------------------------
     | DataCollectors
     |--------------------------------------------------------------------------
     |
     | Enable/disable DataCollectors
     |
     */

    'collectors' => [
        'phpinfo' => $isLocal,         // Php version
        'messages' => $isLocal,         // Messages
        'time' => $isLocal,             // Time Datalogger
        'memory' => $isLocal,           // Memory usage
        'exceptions' => $isLocal,       // Exception displayer
        'log' => $isLocal,              // Logs from Monolog (merged in messages if enabled)
        'db' => $isLocal,               // Show database (PDO) queries and bindings
        'views' => $isLocal,            // Views with their data
        'route' => $isLocal,           // Current route information
        'auth' => $isLocal,            // Display Laravel authentication status
        'gate' => $isLocal,             // Display Laravel Gate checks
        'session' => $isLocal,         // Display session data
        'symfony_request' => $isLocal,  // Only one can be enabled..
        'mail' => $isLocal,             // Catch mail messages
        'laravel' => $isLocal,          // Laravel version and environment
        'events' => $isLocal,          // All events fired
        'default_request' => $isLocal, // Regular or special Symfony request logger
        'logs' => $isLocal,            // Add the latest log messages
        'files' => $isLocal,           // Show the included files
        'config' => $isLocal,          // Display config settings
        'cache' => $isLocal,           // Display cache events
        'models' => $isLocal,           // Display models
        'livewire' => $isLocal,         // Display Livewire (when available)
        'jobs' => $isLocal,            // Display dispatched jobs
        'pennant' => $isLocal,         // Display Pennant feature flags
    ],

    /*
     |--------------------------------------------------------------------------
     | Extra options
     |--------------------------------------------------------------------------
     |
     | Configure some DataCollectors
     |
     */

    'options' => [
        'time' => [
            'memory_usage' => $isLocal, // Calculated by subtracting memory start and end, it may be inaccurate
        ],
        'messages' => [
            'trace' => $isLocal,                  // Trace the origin of the debug message
            'capture_dumps' => $isLocal, // Capture laravel `dump();` as message
        ],
        'memory' => [
            'reset_peak' => $isLocal,       // run memory_reset_peak_usage before collecting
            'with_baseline' => $isLocal, // Set boot memory usage as memory peak baseline
            'precision' => (int) $isLocal,       // Memory rounding precision
        ],
        'auth' => [
            'show_name' => $isLocal,     // Also show the users name/email in the debugbar
            'show_guards' => $isLocal, // Show the guards that are used
        ],
        'gate' => [
            'trace' => false,      // Trace the origin of the Gate checks
        ],
        'db' => [
            'with_params' => $isLocal,   // Render SQL with the parameters substituted
            'exclude_paths' => [       // Paths to exclude entirely from the collector
                // 'vendor/laravel/framework/src/Illuminate/Session', // Exclude sessions queries
            ],
            'backtrace' => $isLocal,   // Use a backtrace to find the origin of the query in your files.
            'backtrace_exclude_paths' => [],   // Paths to exclude from backtrace. (in addition to defaults)
            'timeline' => $isLocal,  // Add the queries to the timeline
            'duration_background' => $isLocal,   // Show shaded background on each query relative to how long it took to execute.
            'explain' => [                 // Show EXPLAIN output on queries
                'enabled' => $isLocal,
            ],
            'hints' => $isLocal,          // Show hints for common mistakes
            'show_copy' => $isLocal,       // Show copy button next to the query,
            'only_slow_queries' => $isLocal, // Only track queries that last longer than `slow_threshold`
            'slow_threshold' => $isLocal, // Max query execution time (ms). Exceeding queries will be highlighted
            'memory_usage' => $isLocal,   // Show queries memory usage
            'soft_limit' => (int) $isLocal,  // After the soft limit, no parameters/backtrace are captured
            'hard_limit' => (int) $isLocal,  // After the hard limit, queries are ignored
        ],
        'mail' => [
            'timeline' => $isLocal,  // Add mails to the timeline
            'show_body' => $isLocal,
        ],
        'views' => [
            'timeline' => $isLocal,                  // Add the views to the timeline
            'data' => $isLocal,                         // True for all data, 'keys' for only names, false for no parameters.
            'group' => (int) env('DEBUGBAR_OPTIONS_VIEWS_GROUP', 50),                    // Group duplicate views. Pass value to auto-group, or true/false to force
            'inertia_pages' => env('DEBUGBAR_OPTIONS_VIEWS_INERTIA_PAGES', 'js/Pages'),  // Path for Inertia views
            'exclude_paths' => [    // Add the paths which you don't want to appear in the views
                'vendor/filament',   // Exclude Filament components by default
            ],
        ],
        'route' => [
            'label' => $isLocal,  // Show complete route on bar
        ],
        'session' => [
            'hiddens' => [], // Hides sensitive values using array paths
        ],
        'symfony_request' => [
            'label' => $isLocal,  // Show route on bar
            'hiddens' => [], // Hides sensitive values using array paths, example: request_request.password
        ],
        'events' => [
            'data' => $isLocal, // Collect events data, listeners
            'excluded' => [], // Example: ['eloquent.*', 'composing', Illuminate\Cache\Events\CacheHit::class]
        ],
        'logs' => [
            'file' => env('DEBUGBAR_OPTIONS_LOGS_FILE'),
        ],
        'cache' => [
            'values' => $isLocal, // Collect cache values
        ],
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
