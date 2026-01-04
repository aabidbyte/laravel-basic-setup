<?php

namespace App\Console\Commands\StarterCommands\Support;

use Illuminate\Support\Facades\File;

class EnvFileManager
{
    /**
     * Update environment variables in .env file.
     *
     * @param  array<string, string>  $variables
     */
    public function update(array $variables): void
    {
        $envPath = base_path('.env');
        $envContent = File::get($envPath);

        foreach ($variables as $key => $value) {
            // Format the value for .env file
            $formattedValue = $this->formatValue($value);

            // Replace existing value or add new one
            if (preg_match("/^{$key}=.*/m", $envContent)) {
                $envContent = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$formattedValue}",
                    $envContent,
                );
            } else {
                // Add new variable at the end of the file
                $envContent .= "\n{$key}={$formattedValue}";
            }
        }

        File::put($envPath, $envContent);
    }

    /**
     * Format a value for .env file.
     */
    protected function formatValue(string $value): string
    {
        // Empty values should remain empty (no quotes)
        if ($value === '') {
            return '';
        }

        // Escape backslashes and dollar signs
        $escaped = str_replace(['\\', '$'], ['\\\\', '\\$'], $value);

        // If value contains spaces, special characters, or starts with a number, wrap in quotes
        if (preg_match('/[\s#=]|^\d/', $escaped)) {
            return '"' . $escaped . '"';
        }

        return $escaped;
    }
}
