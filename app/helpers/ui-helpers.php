<?php

/**
 * Generate Alpine.js class mapping for dynamic colors.
 */
function alpineColorClasses(string $expression, string $prefix = 'btn-'): string
{
    return "\$store.ui.colorClass($expression, '$prefix')";
}
