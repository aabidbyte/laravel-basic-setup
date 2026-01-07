<?php

use Illuminate\Support\Facades\View;

/**
 * Set the page title and optional subtitle for the current view.
 * This shares the data with the view composer for the layout/head.
 */
function setPageTitle(string $title, ?string $subtitle = null): void
{
    View::share('pageTitle', $title);

    if ($subtitle) {
        setPageSubtitle($subtitle);
    }
}

function setPageSubtitle(string $subtitle)
{
    View::share('pageSubtitle', $subtitle);
}
