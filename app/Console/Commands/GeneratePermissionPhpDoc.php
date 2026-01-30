<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Auth\PermissionMatrix;
use Illuminate\Console\Command;

/**
 * Generate PHPDoc annotations for the Permissions class.
 *
 * This command reads the PermissionMatrix and generates @method annotations
 * for IDE autocomplete support in the Permissions class.
 */
class GeneratePermissionPhpDoc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:generate-phpdoc
                          {--check : Check if PHPDoc is up-to-date without modifying files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate PHPDoc @method annotations for Permissions class based on PermissionMatrix';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Generating PHPDoc annotations for Permissions class...');

        $permissionsFile = app_path('Constants/Auth/Permissions.php');

        if (! \file_exists($permissionsFile)) {
            $this->error("Permissions file not found: {$permissionsFile}");

            return self::FAILURE;
        }

        $currentContent = \file_get_contents($permissionsFile);
        $newContent = $this->generatePhpDoc($currentContent);

        if ($this->option('check')) {
            if ($currentContent === $newContent) {
                $this->info('✓ PHPDoc is up-to-date');

                return self::SUCCESS;
            }

            $this->error('✗ PHPDoc is out of date. Run without --check to update.');

            return self::FAILURE;
        }

        \file_put_contents($permissionsFile, $newContent);

        $this->info('✓ PHPDoc annotations generated successfully');
        $this->newLine();
        $this->line('Updated file: ' . $permissionsFile);

        return self::SUCCESS;
    }

    /**
     * Generate PHPDoc annotations for all permissions.
     */
    private function generatePhpDoc(string $currentContent): string
    {
        $matrix = new PermissionMatrix;
        $methods = [];

        // Generate @method annotations for each permission
        foreach ($matrix->getPermissionsByEntity() as $entity => $permissions) {
            foreach ($permissions as $permission) {
                $constantName = $this->permissionToConstantName($permission);
                $methods[] = " * @method static string {$constantName}()";
            }
        }

        $methodsString = \implode("\n", $methods);

        // Find the class docblock and replace @method annotations
        $pattern = '/(\/\*\*.*?CRITICAL RULE:.*?\*\n)(.*?)(\s+\*\/\nclass Permissions)/s';

        if (! \preg_match($pattern, $currentContent)) {
            $this->error('Could not find docblock pattern to replace');

            return $currentContent;
        }

        $replacement = "$1 *\n{$methodsString}\n$3";

        return \preg_replace($pattern, $replacement, $currentContent);
    }

    /**
     * Convert a permission string to its constant name format.
     */
    private function permissionToConstantName(string $permission): string
    {
        return strtoupper(\str_replace(' ', '_', $permission));
    }
}
