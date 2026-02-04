<?php

declare(strict_types=1);

namespace App\Support\Preferences;

use Illuminate\Http\Request;

/**
 * Data Object for retrieving a datatable preference.
 */
readonly class DatatablePreferenceOptions
{
    /**
     * Create a new DatatablePreferenceOptions instance.
     *
     * @param  string  $identifier  The datatable identifier
     * @param  string  $key  The preference key
     * @param  mixed  $default  The default value
     * @param  Request|null  $request  The current request
     */
    public function __construct(
        public string $identifier,
        public string $key,
        public mixed $default = null,
        public ?Request $request = null,
    ) {}
}
