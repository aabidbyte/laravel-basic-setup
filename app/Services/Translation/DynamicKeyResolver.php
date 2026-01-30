<?php

declare(strict_types=1);

namespace App\Services\Translation;

use Exception;
use Illuminate\Support\Facades\Process;

class DynamicKeyResolver
{
    /**
     * Check if a translation key contains dynamic PHP variable interpolation.
     *
     * Detects patterns like:
     * - "permissions.actions.{$action}" (curly brace interpolation)
     * - "permissions.actions.$action" (direct variable)
     */
    public function isDynamicKey(string $key): bool
    {
        // Match {$var}, {$var->prop}, {$var['key']}, or direct $var patterns
        return \preg_match('/\{\$[a-zA-Z_]|\$[a-zA-Z_]/', $key) === 1;
    }

    /**
     * Get the value for a dynamic translation key with configuration instructions.
     */
    public function getDynamicKeyValue(string $key, string $locationValue): string
    {
        return 'DYNAMIC_KEY: This key was not found in config/translation-resolvers.php. '
            . "To auto-resolve this dynamic pattern:\n\n"
            . "1. Open config/translation-resolvers.php\n"
            . "2. Add this pattern with a resolver:\n"
            . "   '{$key}' => fn() => YourClass::getValues(),\n"
            . "3. Run: php artisan lang:sync --write\n\n"
            . "Example resolvers:\n"
            . "- Static method: fn() => PermissionAction::all()\n"
            . "- Service: fn() => array_keys(app(I18nService::class)->getSupportedLocales())\n"
            . "- Database: fn() => DB::table('x')->pluck('column')->toArray()\n"
            . "- Enum: fn() => array_map(fn(\$c) => \$c->value, Status::cases())\n\n"
            . "Source: {$locationValue}";
    }

    /**
     * Try to automatically resolve a dynamic translation key using config.
     */
    public function tryAutoResolve(string $key, array $locations): ?array
    {
        $resolvers = config('translation-resolvers.resolvers', []);

        // Check if we have a resolver for this pattern
        if (! isset($resolvers[$key])) {
            return null;
        }

        try {
            $resolver = $resolvers[$key];

            if (! \is_callable($resolver)) {
                return null;
            }

            // Execute the resolver
            $values = $resolver();

            if (! \is_array($values) || empty($values)) {
                return null;
            }

            return $values;
        } catch (Exception $e) {
            // Silent fail - will use DYNAMIC_KEY marker
            return null;
        }
    }

    /**
     * Extract code context around a translation key usage.
     */
    protected function extractContext(string $content, int $lineNum): array
    {
        $lines = \explode("\n", $content);
        $startLine = max(0, $lineNum - 10);
        $endLine = min(\count($lines), $lineNum + 10);

        $contextLines = array_slice($lines, $startLine, $endLine - $startLine);

        return [
            'code_block' => \implode("\n", $contextLines),
            'line_number' => $lineNum,
        ];
    }

    /**
     * Analyze variable source from code context.
     */
    protected function analyzeVariableSource(string $key, array $context, string $fullContent): ?array
    {
        $codeBlock = $context['code_block'];

        // Pattern 1: Foreach loops with iterable source
        if (\preg_match('/@foreach\s*\(\s*([^)]+?)\s+as\s+(?:\$\w+\s*=>\s*)?\$(\w+)\s*\)/', $codeBlock, $match)) {
            $source = \trim($match[1]);

            return $this->resolveIterableSource($source, $fullContent);
        }

        // Pattern 2: Model property access (e.g., $template->type)
        if (\preg_match('/\$(\w+)->(\w+)/', $key, $match)) {
            $varName = $match[1];
            $propertyName = $match[2];

            // Find the variable's class/type from context
            $modelClass = $this->findVariableType($varName, $codeBlock, $fullContent);

            if ($modelClass) {
                return [
                    'type' => 'model_property',
                    'model' => $modelClass,
                    'property' => $propertyName,
                ];
            }
        }

        return null;
    }

    /**
     * Resolve an iterable source (service call, static method, etc).
     */
    protected function resolveIterableSource(string $source, string $fullContent): ?array
    {
        // Pattern: app(ServiceClass::class)->method()
        if (\preg_match('/app\s*\(\s*([^)]+)\s*\)\s*->\s*(\w+)\s*\(\s*\)/', $source, $match)) {
            $service = \trim($match[1], '\'"');
            $method = $match[2];

            return [
                'type' => 'service_method',
                'service' => $service,
                'method' => $method,
            ];
        }

        // Pattern: ClassName::method()
        if (\preg_match('/([A-Z]\w+)::(\w+)\s*\(\s*\)/', $source, $match)) {
            $className = $match[1];
            $method = $match[2];

            $fullClassName = $this->resolveClassName($className, $fullContent);

            return [
                'type' => 'static_method',
                'class' => $fullClassName,
                'method' => $method,
            ];
        }

        // Pattern: $variable->method() - need to find the variable's type
        if (\preg_match('/\$(\w+)->(\w+)\s*\(\s*\)/', $source, $match)) {
            $varName = $match[1];
            $method = $match[2];

            $className = $this->findVariableType($varName, $fullContent, $fullContent);

            if ($className) {
                return [
                    'type' => 'instance_method',
                    'class' => $className,
                    'method' => $method,
                ];
            }
        }

        return null;
    }

    /**
     * Resolve class name to fully qualified name.
     */
    protected function resolveClassName(string $className, string $fileContent): string
    {
        // Check for use statements
        $pattern = '/use\s+([^;]+\\\\' . preg_quote($className) . ')\s*;/';
        if (\preg_match($pattern, $fileContent, $match)) {
            return $match[1];
        }

        // Common Laravel namespaces
        $commonNamespaces = [
            'App\\Constants\\Auth\\',
            'App\\Enums\\',
            'App\\Services\\',
            'App\\Models\\',
        ];

        foreach ($commonNamespaces as $namespace) {
            $fqn = $namespace . $className;
            if (\class_exists($fqn)) {
                return $fqn;
            }
        }

        return $className;
    }

    /**
     * Find variable type from context.
     */
    protected function findVariableType(string $varName, string $context, string $fullContent): ?string
    {
        // Pattern: Type $varName (method parameter)
        if (\preg_match('/([A-Z]\w+)\s+\$' . preg_quote($varName) . '\b/', $context, $match)) {
            return $this->resolveClassName($match[1], $fullContent);
        }

        // Pattern: $varName = new ClassName()
        if (\preg_match('/\$' . preg_quote($varName) . '\s*=\s*new\s+([A-Z]\w+)/', $context, $match)) {
            return $this->resolveClassName($match[1], $fullContent);
        }

        return null;
    }

    /**
     * Execute resolver to get actual values.
     */
    protected function executeResolver(array $resolver): ?array
    {
        try {
            $command = null;

            if ($resolver['type'] === 'service_method') {
                $command = \sprintf(
                    'echo json_encode(array_keys(app(%s)->%s()));',
                    $resolver['service'],
                    $resolver['method'],
                );
            } elseif ($resolver['type'] === 'static_method' || $resolver['type'] === 'instance_method') {
                $command = \sprintf(
                    'echo json_encode(%s::%s());',
                    $resolver['class'],
                    $resolver['method'],
                );
            } elseif ($resolver['type'] === 'model_property') {
                // Check if property is an enum cast
                $command = $this->buildEnumCommand($resolver);
            }

            if ($command === null) {
                return null;
            }

            $result = Process::timeout(10)->run(
                \sprintf('php artisan tinker --execute="%s"', addslashes($command)),
            );

            if ($result->successful()) {
                $output = \trim($result->output());
                $decoded = \json_decode($output, true);

                if (json_last_error() === JSON_ERROR_NONE && \is_array($decoded)) {
                    return $decoded;
                }
            }
        } catch (Exception $e) {
            // Silent fail - will fall back to DYNAMIC_KEY
        }

        return null;
    }

    /**
     * Build command to get enum values from model cast.
     */
    protected function buildEnumCommand(array $resolver): ?string
    {
        return \sprintf(
            '\$reflection = new \\ReflectionClass(%s); ' .
            '\$casts = \$reflection->hasMethod(\'casts\') ? (new %s)->casts() : []; ' .
            'if (isset(\$casts[\'%s\']) && enum_exists(\$casts[\'%s\'])) { ' .
            '  echo json_encode(array_map(fn(\$case) => \$case->value, \$casts[\'%s\']::cases())); ' .
            '}',
            $resolver['model'],
            $resolver['model'],
            $resolver['property'],
            $resolver['property'],
            $resolver['property'],
        );
    }

    /**
     * Expand a dynamic key pattern with a concrete value.
     * For 'locales.{$translation->locale}' with value 'en_US', returns 'locales.en_US'
     */
    public function expandKey(string $pattern, string|int $value): string
    {
        $value = (string) $value;
        // Replace {$var->property} patterns (e.g., {$translation->locale})
        $expanded = \preg_replace('/\{\$[\w>-]+\}/', $value, $pattern);

        // Replace {$var} patterns
        $expanded = \preg_replace('/\{\$\w+\}/', $value, $expanded);

        // Replace $var patterns (e.g., $type, $code)
        $expanded = \preg_replace('/\$\w+/', $value, $expanded);

        return $expanded;
    }

    /**
     * Generate a human-readable translation value from a key.
     */
    public function generateTranslationValue(string $key): string
    {
        // Get the last part of the key
        $parts = \explode('.', $key);
        $value = end($parts);

        // Convert snake_case or kebab-case to Title Case
        $value = \str_replace(['_', '-'], ' ', $value);
        $value = ucwords($value);

        return $value;
    }

    /**
     * Check if a value appears to be a raw location reference.
     */
    public function isRawLocationValue(mixed $value): bool
    {
        if (! \is_string($value)) {
            return false;
        }

        // Check for common file extensions and line number pattern: .php:123 or .blade.php:123
        return \preg_match('/\.php:\d+/', $value) === 1 || \preg_match('/\.blade\.php:\d+/', $value) === 1;
    }
}
