<?php

declare(strict_types=1);

namespace App\Services\EmailTemplate;

use Exception;
use Illuminate\Support\Facades\File;

class BladeTemplateParser
{
    /**
     * Parse the metadata block from a Blade file.
     * Expects a @php ... @endphp block at the beginning of the file.
     *
     * @param  string  $path  Absolute path to the blade file
     * @return array Metadata variables
     *
     * @throws Exception
     */
    public function parse(string $path): array
    {
        if (! File::exists($path)) {
            throw new Exception("File not found: {$path}");
        }

        $content = File::get($path);

        // Extract the first @php ... @endphp block
        preg_match('/@php\s+(.*?)\s+@endphp/s', $content, $matches);

        if (empty($matches[1])) {
            return [];
        }

        $phpCode = $matches[1];

        // Evaluate the code to get variables
        // We wrap it in a closure to avoid polluting global scope
        $metadata = (function () use ($phpCode) {
            // Define expected variables with defaults to avoid undefined errors
            $name = '';
            $description = '';
            $type = 'transactional';
            $entity_types = [];
            $context_variables = [];
            $layout = null;

            eval($phpCode);

            return [
                'name' => $name,
                'description' => $description,
                'type' => $type,
                'entity_types' => $entity_types,
                'context_variables' => $context_variables,
                'layout' => $layout,
                'subject' => $subject ?? null, // Optional subject for templates
                'display_name' => $display_name ?? null, // For layouts
                'is_active' => $is_active ?? true,
                'is_default' => $is_default ?? false,
            ];
        })();

        return $metadata;
    }
}
