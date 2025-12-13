<?php

namespace App\Console\Commands\StarterCommands\Support;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\warning;

class MultiTenancySetup
{
    public function __construct(
        protected EnvFileManager $envManager,
        protected Command $command
    ) {}

    /**
     * Install and configure multi-tenancy package.
     */
    public function install(): int
    {
        info('Installing multi-tenancy package...');

        // Install the package via composer
        info('Installing stancl/tenancy package...');
        $output = [];
        $returnCode = 0;
        $command = 'composer require stancl/tenancy --no-interaction 2>&1';
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            error('❌ Failed to install stancl/tenancy package.');
            error('Output: '.implode("\n", $output));

            info('Please run manually: composer require stancl/tenancy');
            info('You can continue with the setup and install multi-tenancy later.');

            return Command::FAILURE;
        }

        info('✅ Package installed successfully');

        // Regenerate autoloader and clear caches
        info('Regenerating autoloader...');
        $projectRoot = base_path();
        exec("cd {$projectRoot} && composer dump-autoload --no-interaction 2>&1", $autoloadOutput, $autoloadReturnCode);

        if ($autoloadReturnCode !== 0) {
            warning('Warning: Autoloader regeneration had issues, but continuing...');
        }

        // Clear config cache to ensure new package is discovered
        $this->command->call('config:clear');
        $this->command->call('package:discover', ['--ansi' => true]);

        // Run tenancy:install command
        info('Setting up tenancy configuration...');

        // Use shell execution in a fresh process to ensure the command is available
        // This runs in a new PHP process where the newly installed package will be autoloaded
        $tenancyInstallOutput = [];
        $tenancyInstallReturnCode = 0;
        exec("cd {$projectRoot} && php artisan tenancy:install --no-interaction 2>&1", $tenancyInstallOutput, $tenancyInstallReturnCode);

        if ($tenancyInstallReturnCode !== 0) {
            error('❌ Failed to run tenancy:install');
            error('Output: '.implode("\n", $tenancyInstallOutput));

            info('Please run manually: php artisan tenancy:install');

            return Command::FAILURE;
        }

        info('✅ Tenancy configuration files created');

        // Create Tenant model
        $this->createTenantModel();

        // Update tenancy config
        $this->updateTenancyConfig();

        // Register service provider
        $this->registerTenancyServiceProvider();

        // Organize migrations
        $this->organizeMigrations();

        // Update routes
        $this->updateRoutes();

        // Update .env file
        $this->envManager->update(['MULTI_TENANCY_ENABLED' => 'true']);

        info('✅ Multi-tenancy setup completed!');

        return Command::SUCCESS;
    }

    /**
     * Create the Tenant model.
     */
    protected function createTenantModel(): void
    {
        $tenantModelPath = app_path('Models/Tenant.php');

        if (File::exists($tenantModelPath)) {
            info('Tenant model already exists, skipping creation.');

            return;
        }

        $tenantModelContent = <<<'PHP'
<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;
}
PHP;

        File::put($tenantModelPath, $tenantModelContent);
        info('✅ Created Tenant model');
    }

    /**
     * Update tenancy configuration to use custom Tenant model.
     */
    protected function updateTenancyConfig(): void
    {
        $configPath = config_path('tenancy.php');

        if (! File::exists($configPath)) {
            warning('Tenancy config file not found. Please run: php artisan tenancy:install');

            return;
        }

        $configContent = File::get($configPath);

        // Update tenant_model - try different patterns for different package versions
        // Pattern 1: 'tenant_model' => ...
        if (preg_match("/'tenant_model'\s*=>\s*[^,)]+/", $configContent)) {
            $configContent = preg_replace(
                "/'tenant_model'\s*=>\s*[^,)]+/",
                "'tenant_model' => \\App\\Models\\Tenant::class",
                $configContent
            );
        }
        // Pattern 2: 'models' => ['tenant' => ...]
        elseif (preg_match("/'models'\s*=>\s*\[/", $configContent)) {
            if (preg_match("/'tenant'\s*=>\s*[^,)]+/", $configContent)) {
                $configContent = preg_replace(
                    "/'tenant'\s*=>\s*[^,)]+/",
                    "'tenant' => \\App\\Models\\Tenant::class",
                    $configContent
                );
            } else {
                $configContent = preg_replace(
                    "/('models'\s*=>\s*\[)/",
                    "$1\n        'tenant' => \\App\\Models\\Tenant::class,",
                    $configContent
                );
            }
        }

        // Update central domains based on APP_URL from .env
        $envPath = base_path('.env');
        $centralDomains = ['localhost', '127.0.0.1'];

        if (File::exists($envPath)) {
            $envContent = File::get($envPath);
            if (preg_match('/^APP_URL=(.+)$/m', $envContent, $matches)) {
                $appUrl = trim($matches[1], '"\'');
                // Try to extract domain from APP_URL
                if (preg_match('/https?:\/\/([^\/]+)/', $appUrl, $urlMatches)) {
                    $domain = $urlMatches[1];
                    if (! in_array($domain, $centralDomains)) {
                        $centralDomains[] = $domain;
                    }
                }
            }
        }

        $domainsString = "['".implode("', '", $centralDomains)."']";

        // Update central_domains - try different patterns
        // Pattern 1: Direct 'central_domains' => [...]
        if (preg_match("/'central_domains'\s*=>\s*\[[^\]]+\]/", $configContent)) {
            $configContent = preg_replace(
                "/'central_domains'\s*=>\s*\[[^\]]+\]/",
                "'central_domains' => {$domainsString}",
                $configContent
            );
        }
        // Pattern 2: Inside 'identification' => ['central_domains' => ...]
        elseif (preg_match("/'identification'\s*=>\s*\[/", $configContent)) {
            if (preg_match("/'central_domains'\s*=>\s*\[[^\]]+\]/", $configContent)) {
                $configContent = preg_replace(
                    "/'central_domains'\s*=>\s*\[[^\]]+\]/",
                    "'central_domains' => {$domainsString}",
                    $configContent
                );
            } else {
                // Add central_domains inside identification array
                $configContent = preg_replace(
                    "/('identification'\s*=>\s*\[)/",
                    "$1\n        'central_domains' => {$domainsString},",
                    $configContent
                );
            }
        }

        File::put($configPath, $configContent);
        info('✅ Updated tenancy configuration');
    }

    /**
     * Register TenancyServiceProvider in bootstrap/providers.php.
     */
    protected function registerTenancyServiceProvider(): void
    {
        $providersPath = base_path('bootstrap/providers.php');

        if (! File::exists($providersPath)) {
            warning('bootstrap/providers.php not found. Please register TenancyServiceProvider manually.');

            return;
        }

        $providersContent = File::get($providersPath);

        // Check if TenancyServiceProvider is already registered
        if (str_contains($providersContent, 'TenancyServiceProvider')) {
            info('TenancyServiceProvider already registered.');

            return;
        }

        // Add conditional registration after TelescopeServiceProvider and before VoltServiceProvider check
        $insertion = "// Only register TenancyServiceProvider if multi-tenancy is enabled\n";
        $insertion .= "if (class_exists(\\App\\Providers\\TenancyServiceProvider::class)) {\n";
        $insertion .= "    \$providers[] = App\\Providers\\TenancyServiceProvider::class;\n";
        $insertion .= "}\n\n";

        // Insert before the VoltServiceProvider check
        if (str_contains($providersContent, 'VoltServiceProvider')) {
            $providersContent = str_replace(
                '// Only register VoltServiceProvider if Livewire/Volt is installed',
                $insertion.'// Only register VoltServiceProvider if Livewire/Volt is installed',
                $providersContent
            );
        } else {
            // If no VoltServiceProvider check, add before return statement
            $providersContent = str_replace(
                "\nreturn \$providers;",
                "\n\n{$insertion}return \$providers;",
                $providersContent
            );
        }

        File::put($providersPath, $providersContent);
        info('✅ Registered TenancyServiceProvider');
    }

    /**
     * Organize migrations by moving user migration to tenant folder.
     */
    protected function organizeMigrations(): void
    {
        $usersMigration = database_path('migrations/0001_01_01_000000_create_users_table.php');
        $tenantMigrationsDir = database_path('migrations/tenant');

        if (! File::exists($usersMigration)) {
            info('Users migration not found, skipping migration organization.');

            return;
        }

        // Create tenant migrations directory if it doesn't exist
        if (! File::isDirectory($tenantMigrationsDir)) {
            File::makeDirectory($tenantMigrationsDir, 0755, true);
        }

        $destination = $tenantMigrationsDir.'/0001_01_01_000000_create_users_table.php';

        if (File::exists($destination)) {
            info('Users migration already in tenant folder, skipping.');

            return;
        }

        File::move($usersMigration, $destination);
        info('✅ Moved users migration to tenant migrations folder');
    }

    /**
     * Update routes to add comment about multi-tenancy routing.
     */
    protected function updateRoutes(): void
    {
        $routesPath = base_path('routes/web.php');

        if (! File::exists($routesPath)) {
            return;
        }

        $routesContent = File::get($routesPath);

        // Check if comment already exists
        if (str_contains($routesContent, 'multi-tenancy') || str_contains($routesContent, 'central_domains')) {
            info('Routes already have multi-tenancy comments.');

            return;
        }

        // Add comment at the top after PHP tag
        $comment = "\n// Multi-tenancy: Routes in this file are accessible on central domains only.\n";
        $comment .= "// Tenant routes should be defined in routes/tenant.php\n";

        // Insert after the last use statement
        // Use preg_quote to safely escape the namespace for regex
        $fortifyPattern = '/'.preg_quote('use Laravel\Fortify\Features;', '/').'/';

        if (preg_match($fortifyPattern, $routesContent)) {
            $routesContent = preg_replace(
                $fortifyPattern,
                '$0'.$comment,
                $routesContent
            );
        } else {
            // Fallback: insert after the last use statement or after opening PHP tag
            if (preg_match('/(use [^;]+;)\s*\n/', $routesContent, $matches)) {
                $routesContent = preg_replace(
                    '/(use [^;]+;)\s*\n/',
                    '$1'.$comment,
                    $routesContent,
                    1
                );
            } else {
                // Insert after opening PHP tag
                $routesContent = preg_replace(
                    '/^<\?php\s*\n/',
                    '<?php'.$comment,
                    $routesContent
                );
            }
        }

        File::put($routesPath, $routesContent);
        info('✅ Added multi-tenancy routing comments');
    }
}
