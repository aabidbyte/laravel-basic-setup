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

        // Note: Migrations are not automatically moved. Users should organize migrations manually
        // based on their needs. Central migrations stay in database/migrations/
        // Tenant migrations should be placed in database/migrations/tenant/ if needed.

        // Update routes
        $this->updateRoutes();

        // Register tenant routes in bootstrap/app.php
        $this->registerTenantRoutes();

        // Configure package integrations
        $this->configurePackageIntegrations();

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
     * Organize migrations by moving user migration and two-factor columns migration to tenant folder.
     */
    protected function organizeMigrations(): void
    {
        $tenantMigrationsDir = database_path('migrations/tenant');

        // Create tenant migrations directory if it doesn't exist
        if (! File::isDirectory($tenantMigrationsDir)) {
            File::makeDirectory($tenantMigrationsDir, 0755, true);
        }

        // Move users table migration
        $this->moveUsersTableMigration($tenantMigrationsDir);

        // Move two-factor columns migration
        $this->moveTwoFactorColumnsMigration($tenantMigrationsDir);
    }

    /**
     * Move users table migration to tenant folder.
     */
    protected function moveUsersTableMigration(string $tenantMigrationsDir): void
    {
        $usersMigration = database_path('migrations/0001_01_01_000000_create_users_table.php');

        if (! File::exists($usersMigration)) {
            info('Users migration not found, skipping.');

            return;
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
     * Move two-factor columns migration to tenant folder.
     */
    protected function moveTwoFactorColumnsMigration(string $tenantMigrationsDir): void
    {
        // Find two-factor columns migration using glob pattern
        $twoFactorMigrations = File::glob(database_path('migrations/*_add_two_factor_columns_to_users_table.php'));

        if (empty($twoFactorMigrations)) {
            info('Two-factor columns migration not found, skipping.');

            return;
        }

        foreach ($twoFactorMigrations as $twoFactorMigration) {
            $fileName = basename($twoFactorMigration);
            $destination = $tenantMigrationsDir.'/'.$fileName;

            if (File::exists($destination)) {
                info("Two-factor columns migration {$fileName} already in tenant folder, skipping.");

                continue;
            }

            File::move($twoFactorMigration, $destination);
            info("✅ Moved two-factor columns migration {$fileName} to tenant migrations folder");
        }
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

    /**
     * Create tenant routes file.
     * Note: Tenant routes are loaded by TenancyServiceProvider, not through withRouting().
     */
    protected function registerTenantRoutes(): void
    {
        // First, fix any broken bootstrap/app.php from previous attempts
        $this->fixBrokenBootstrapApp();

        // Ensure universal routes middleware group exists in bootstrap/app.php
        $this->ensureUniversalRoutesMiddlewareGroup();

        $tenantRoutesPath = base_path('routes/tenant.php');

        // Create tenant routes file if it doesn't exist
        if (! File::exists($tenantRoutesPath)) {
            $tenantRoutesContent = <<<'PHP'
<?php

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

// Tenant routes are automatically loaded by TenancyServiceProvider
// These routes are only accessible on tenant domains
Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/', function () {
        return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
    });
});
PHP;
            File::put($tenantRoutesPath, $tenantRoutesContent);
            info('✅ Created tenant routes file (routes/tenant.php)');
        } else {
            info('Tenant routes file already exists.');
        }

        // Ensure TenancyServiceProvider loads tenant routes
        $this->ensureTenancyServiceProviderLoadsTenantRoutes();
    }

    /**
     * Fix broken bootstrap/app.php file that may have invalid tenant parameter.
     * This can happen if a previous installation attempt failed.
     */
    protected function fixBrokenBootstrapApp(): void
    {
        $appPath = base_path('bootstrap/app.php');

        if (! File::exists($appPath)) {
            return;
        }

        $appContent = File::get($appPath);

        // Check if there's an invalid tenant parameter in withRouting
        if (preg_match("/tenant:\s*__DIR__\.'\/\.\.\/routes\/tenant\.php'/", $appContent)) {
            warning('Found invalid tenant parameter in bootstrap/app.php. Fixing...');

            // Remove tenant route registration from withRouting
            // Pattern 1: tenant: __DIR__.'/../routes/tenant.php', (with comma and newline)
            $appContent = preg_replace(
                '/,\s*tenant:\s*__DIR__\.\'\/\.\.\/routes\/tenant\.php\'\s*,?\s*\n/',
                '',
                $appContent
            );

            // Pattern 2: tenant: __DIR__.'/../routes/tenant.php' (last item, no comma)
            $appContent = preg_replace(
                '/,\s*tenant:\s*__DIR__\.\'\/\.\.\/routes\/tenant\.php\'/',
                '',
                $appContent
            );

            // Pattern 3: tenant: __DIR__.'/../routes/tenant.php', (standalone line)
            $appContent = preg_replace(
                '/\s*tenant:\s*__DIR__\.\'\/\.\.\/routes\/tenant\.php\'\s*,?\s*\n/',
                '',
                $appContent
            );

            File::put($appPath, $appContent);
            info('✅ Fixed broken bootstrap/app.php (removed invalid tenant parameter)');
        }
    }

    /**
     * Ensure universal routes middleware group exists in bootstrap/app.php.
     * This is required for universal routes feature to work properly.
     */
    protected function ensureUniversalRoutesMiddlewareGroup(): void
    {
        $appPath = base_path('bootstrap/app.php');

        if (! File::exists($appPath)) {
            return;
        }

        $appContent = File::get($appPath);

        // Check if universal middleware group already exists
        if (str_contains($appContent, "->group('universal'") || str_contains($appContent, "middleware->group('universal'")) {
            return;
        }

        // Validate PHP syntax before modifying
        $tempFile = tempnam(sys_get_temp_dir(), 'bootstrap_app_');
        file_put_contents($tempFile, $appContent);
        exec("php -l {$tempFile} 2>&1", $output, $returnCode);
        unlink($tempFile);

        if ($returnCode !== 0) {
            warning('bootstrap/app.php has syntax errors. Skipping universal routes middleware group addition.');
            warning('Please fix the syntax errors first, then run the setup again.');

            return;
        }

        // Find the withMiddleware function and add universal group before the closing brace
        // Use a line-by-line approach for reliability
        $lines = explode("\n", $appContent);
        $newLines = [];
        $inMiddlewareFunction = false;
        $braceDepth = 0;
        $inserted = false;

        foreach ($lines as $line) {
            // Detect start of withMiddleware function
            if (str_contains($line, '->withMiddleware(function (Middleware $middleware): void {')) {
                $inMiddlewareFunction = true;
                $braceDepth = 1;
                $newLines[] = $line;

                continue;
            }

            // If we're in the middleware function, track brace depth
            if ($inMiddlewareFunction) {
                $braceDepth += substr_count($line, '{') - substr_count($line, '}');

                // When we reach the closing brace of the function (braceDepth == 0)
                // and the line contains the closing pattern
                if ($braceDepth === 0 && ! $inserted && (trim($line) === '})' || str_ends_with(trim($line), '})'))) {
                    // Insert universal group before the closing brace
                    $indent = str_repeat(' ', 8);
                    $newLines[] = $indent."\$middleware->group('universal', []);";
                    $newLines[] = $line;
                    $inserted = true;
                    $inMiddlewareFunction = false;

                    continue;
                }
            }

            $newLines[] = $line;
        }

        if ($inserted) {
            $newContent = implode("\n", $newLines);

            // Validate the new content before writing
            $tempFile = tempnam(sys_get_temp_dir(), 'bootstrap_app_new_');
            file_put_contents($tempFile, $newContent);
            exec("php -l {$tempFile} 2>&1", $validationOutput, $validationReturnCode);
            unlink($tempFile);

            if ($validationReturnCode === 0) {
                File::put($appPath, $newContent);
                info('✅ Added universal routes middleware group to bootstrap/app.php');
            } else {
                warning('Failed to add universal routes middleware group: syntax error would be introduced.');
                warning('Output: '.implode("\n", $validationOutput));
            }
        }
    }

    /**
     * Ensure TenancyServiceProvider loads tenant routes.
     */
    protected function ensureTenancyServiceProviderLoadsTenantRoutes(): void
    {
        $providerPath = app_path('Providers/TenancyServiceProvider.php');

        if (! File::exists($providerPath)) {
            // TenancyServiceProvider will be created by tenancy:install command
            // It should automatically load tenant routes
            return;
        }

        $providerContent = File::get($providerPath);

        // Check if tenant routes are already being loaded
        if (str_contains($providerContent, 'routes/tenant.php') || str_contains($providerContent, 'loadRoutesFrom')) {
            info('Tenant routes are already configured in TenancyServiceProvider.');

            return;
        }

        // Add code to load tenant routes in boot method
        $loadRoutesCode = "\n        // Load tenant routes\n        \$this->loadRoutesFrom(base_path('routes/tenant.php'));";

        // Find the boot method and add the route loading code
        if (preg_match('/public function boot\(\): void\s*\{([^}]*)\}/s', $providerContent, $matches)) {
            $bootMethodContent = $matches[1];

            // Add call to loadRoutesFrom at the end of boot method
            if (! str_contains($bootMethodContent, 'loadRoutesFrom')) {
                $newBootMethod = $bootMethodContent.$loadRoutesCode;
                $providerContent = str_replace($matches[0], 'public function boot(): void {'.$newBootMethod."\n    }", $providerContent);

                File::put($providerPath, $providerContent);
                info('✅ Configured TenancyServiceProvider to load tenant routes');
            }
        }
    }

    /**
     * Configure package integrations for multi-tenancy.
     */
    protected function configurePackageIntegrations(): void
    {
        // Configure Telescope tags
        $this->configureTelescopeTags();

        // Configure Livewire
        $this->configureLivewire();

        // Configure Sanctum (without moving migrations)
        $this->configureSanctumWithoutMovingMigrations();

        // Configure Spatie Permission (without moving migrations)
        $this->configureSpatiePermissionWithoutMovingMigrations();
    }

    /**
     * Enable Telescope tags feature in tenancy config.
     */
    protected function configureTelescopeTags(): void
    {
        $configPath = config_path('tenancy.php');

        if (! File::exists($configPath)) {
            return;
        }

        $configContent = File::get($configPath);

        // Check if Telescope tags are already configured
        if (str_contains($configContent, 'telescope_tags') || str_contains($configContent, 'TelescopeTags')) {
            info('Telescope tags already configured in tenancy config.');

            return;
        }

        // Add Telescope tags and UniversalRoutes to features array
        // Look for 'features' => [...] pattern
        if (preg_match("/'features'\s*=>\s*\[/", $configContent)) {
            // Check if features are already added
            $needsTelescopeTags = ! str_contains($configContent, 'TelescopeTags');
            $needsUniversalRoutes = ! str_contains($configContent, 'UniversalRoutes');

            if ($needsTelescopeTags || $needsUniversalRoutes) {
                $featuresToAdd = [];
                if ($needsTelescopeTags) {
                    $featuresToAdd[] = '\\Stancl\\Tenancy\\Features\\TelescopeTags::class,';
                }
                if ($needsUniversalRoutes) {
                    $featuresToAdd[] = '\\Stancl\\Tenancy\\Features\\UniversalRoutes::class,';
                }

                if (! empty($featuresToAdd)) {
                    $configContent = preg_replace(
                        "/('features'\s*=>\s*\[)/",
                        "$1\n        ".implode("\n        ", $featuresToAdd),
                        $configContent
                    );
                }
            }
        } else {
            warning('Could not find features array in tenancy config. Please enable Telescope tags and UniversalRoutes manually.');
        }

        // Enable universal routes (needed for Livewire and Sanctum)
        if (! str_contains($configContent, "'universal' => true") && ! str_contains($configContent, "'universal' => false")) {
            // Add universal routes configuration
            // Look for a good place to add it, typically near the top of the config array
            if (preg_match("/return\s*\[/", $configContent)) {
                $configContent = preg_replace(
                    "/(return\s*\[)/",
                    "$1\n    'universal' => true,\n",
                    $configContent
                );
            }
        } elseif (preg_match("/'universal'\s*=>\s*false/", $configContent)) {
            // Update if it's set to false
            $configContent = preg_replace(
                "/'universal'\s*=>\s*false/",
                "'universal' => true",
                $configContent
            );
        }

        File::put($configPath, $configContent);
        info('✅ Enabled Telescope tags and UniversalRoutes feature in tenancy config');
    }

    /**
     * Configure Livewire for multi-tenancy.
     */
    protected function configureLivewire(): void
    {
        // Check if Livewire is installed
        if (! class_exists(\Livewire\Livewire::class)) {
            info('Livewire not installed, skipping Livewire configuration.');

            return;
        }

        // Update TenancyServiceProvider to configure Livewire routes
        $this->updateTenancyServiceProviderForLivewire();

        // Note: Livewire file upload temporary_file_upload.middleware config
        // needs to be updated manually in config/livewire.php if it exists
        // to include: 'throttle:60,1', 'universal', InitializeTenancyByDomain::class
        info('✅ Configured Livewire for multi-tenancy');
    }

    /**
     * Update TenancyServiceProvider to configure Livewire update route.
     */
    protected function updateTenancyServiceProviderForLivewire(): void
    {
        $providerPath = app_path('Providers/TenancyServiceProvider.php');

        if (! File::exists($providerPath)) {
            warning('TenancyServiceProvider not found. Livewire configuration will need to be done manually.');

            return;
        }

        $providerContent = File::get($providerPath);

        // Check if Livewire configuration already exists
        if (str_contains($providerContent, 'Livewire::setUpdateRoute') || str_contains($providerContent, 'livewire/update')) {
            info('Livewire already configured in TenancyServiceProvider.');

            return;
        }

        // Add Livewire configuration to boot method
        $livewireConfig = <<<'PHP'

    /**
     * Configure Livewire for multi-tenancy.
     */
    protected function configureLivewire(): void
    {
        if (! class_exists(\Livewire\Livewire::class)) {
            return;
        }

        \Livewire\Livewire::setUpdateRoute(function ($handle) {
            return \Illuminate\Support\Facades\Route::post('/livewire/update', $handle)
                ->middleware(
                    'web',
                    'universal',
                    \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
                );
        });

        // Configure file upload middleware if FilePreviewController exists
        if (class_exists(\Livewire\Features\SupportFileUploads\FilePreviewController::class)) {
            \Livewire\Features\SupportFileUploads\FilePreviewController::$middleware = [
                'web',
                'universal',
                \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
            ];
        }
    }
PHP;

        // Find the boot method and add the configuration
        if (preg_match('/public function boot\(\): void\s*\{([^}]*)\}/s', $providerContent, $matches)) {
            $bootMethodContent = $matches[1];

            // Add call to configureLivewire at the end of boot method
            if (! str_contains($bootMethodContent, 'configureLivewire')) {
                $newBootMethod = $bootMethodContent."\n        \$this->configureLivewire();";
                $providerContent = str_replace($matches[0], 'public function boot(): void {'.$newBootMethod."\n    }", $providerContent);
            }

            // Add the configureLivewire method before the closing brace of the class
            if (! str_contains($providerContent, 'protected function configureLivewire')) {
                $providerContent = preg_replace(
                    '/(\n\})$/',
                    $livewireConfig.'$1',
                    $providerContent
                );
            }

            File::put($providerPath, $providerContent);
            info('✅ Updated TenancyServiceProvider for Livewire');
        } else {
            warning('Could not find boot method in TenancyServiceProvider. Livewire configuration will need to be done manually.');
        }
    }

    /**
     * Configure Sanctum for multi-tenancy without moving migrations.
     */
    protected function configureSanctumWithoutMovingMigrations(): void
    {
        // Check if Sanctum is installed
        if (! class_exists(\Laravel\Sanctum\Sanctum::class)) {
            info('Sanctum not installed, skipping Sanctum configuration.');

            return;
        }

        // Update Sanctum config
        $this->updateSanctumConfig();

        // Update AuthServiceProvider to ignore migrations
        $this->updateAuthServiceProviderForSanctum();

        // Add Sanctum csrf-cookie route to tenant routes
        $this->addSanctumRouteToTenantRoutes();

        // Note: Migrations are not automatically moved. Users should organize migrations manually.
    }

    /**
     * Configure Sanctum for multi-tenancy.
     */
    protected function configureSanctum(): void
    {
        // Check if Sanctum is installed
        if (! class_exists(\Laravel\Sanctum\Sanctum::class)) {
            info('Sanctum not installed, skipping Sanctum configuration.');

            return;
        }

        // Update Sanctum config
        $this->updateSanctumConfig();

        // Update AuthServiceProvider to ignore migrations
        $this->updateAuthServiceProviderForSanctum();

        // Add Sanctum csrf-cookie route to tenant routes
        $this->addSanctumRouteToTenantRoutes();

        // Move Sanctum migrations to tenant folder
        $this->moveSanctumMigrationsToTenant();
    }

    /**
     * Update Sanctum config to disable routes.
     */
    protected function updateSanctumConfig(): void
    {
        $configPath = config_path('sanctum.php');

        if (! File::exists($configPath)) {
            return;
        }

        $configContent = File::get($configPath);

        // Check if already configured
        if (preg_match("/'routes'\s*=>\s*(true|false)/", $configContent)) {
            // Update to false
            $configContent = preg_replace(
                "/'routes'\s*=>\s*(true|false)/",
                "'routes' => false",
                $configContent
            );
        } else {
            // Add routes => false before the closing bracket
            $configContent = preg_replace(
                '/(\];\s*)$/',
                "    'routes' => false,\n$1",
                $configContent
            );
        }

        File::put($configPath, $configContent);
        info('✅ Updated Sanctum config to disable routes');
    }

    /**
     * Update AuthServiceProvider to ignore Sanctum migrations.
     */
    protected function updateAuthServiceProviderForSanctum(): void
    {
        $providerPath = app_path('Providers/AuthServiceProvider.php');

        if (! File::exists($providerPath)) {
            // AuthServiceProvider might not exist in Laravel 12
            return;
        }

        $providerContent = File::get($providerPath);

        // Check if already configured
        if (str_contains($providerContent, 'Sanctum::ignoreMigrations')) {
            info('Sanctum migrations already ignored in AuthServiceProvider.');

            return;
        }

        // Add Sanctum::ignoreMigrations() to register method
        if (preg_match('/public function register\(\): void\s*\{([^}]*)\}/s', $providerContent, $matches)) {
            $registerMethodContent = $matches[1];

            if (! str_contains($registerMethodContent, 'Sanctum::ignoreMigrations')) {
                $newRegisterMethod = $registerMethodContent."\n        \\Laravel\\Sanctum\\Sanctum::ignoreMigrations();";
                $providerContent = str_replace($matches[0], 'public function register(): void {'.$newRegisterMethod."\n    }", $providerContent);

                File::put($providerPath, $providerContent);
                info('✅ Updated AuthServiceProvider to ignore Sanctum migrations');
            }
        }
    }

    /**
     * Add Sanctum csrf-cookie route to tenant routes.
     */
    protected function addSanctumRouteToTenantRoutes(): void
    {
        $tenantRoutesPath = base_path('routes/tenant.php');

        if (! File::exists($tenantRoutesPath)) {
            // Create tenant routes file if it doesn't exist
            $tenantRoutesContent = "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n";
            File::put($tenantRoutesPath, $tenantRoutesContent);
        }

        $routesContent = File::get($tenantRoutesPath);

        // Check if Sanctum route already exists
        if (str_contains($routesContent, 'sanctum.csrf-cookie') || str_contains($routesContent, 'sanctum/csrf-cookie')) {
            info('Sanctum csrf-cookie route already exists in tenant routes.');

            return;
        }

        // Add Sanctum route
        $sanctumRoute = <<<'PHP'

// Sanctum csrf-cookie route for tenant app
Route::group(['prefix' => config('sanctum.prefix', 'sanctum')], static function () {
    Route::get('/csrf-cookie', [\Laravel\Sanctum\Http\Controllers\CsrfCookieController::class, 'show'])
        ->middleware([
            'web',
            \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
        ])->name('sanctum.csrf-cookie');
});
PHP;

        // Append to the end of the file before closing PHP tag
        $routesContent = rtrim($routesContent)."\n".$sanctumRoute."\n";

        File::put($tenantRoutesPath, $routesContent);
        info('✅ Added Sanctum csrf-cookie route to tenant routes');
    }

    /**
     * Configure Spatie Permission for multi-tenancy without moving migrations.
     */
    protected function configureSpatiePermissionWithoutMovingMigrations(): void
    {
        // Check if Spatie Permission is installed
        if (! class_exists(\Spatie\Permission\PermissionServiceProvider::class)) {
            info('Spatie Permission not installed, skipping Spatie Permission configuration.');
            info('💡 To use Spatie Permission with multi-tenancy, install it first: composer require spatie/laravel-permission');

            return;
        }

        // Add event listeners to TenancyServiceProvider
        $this->addSpatiePermissionEventListeners();

        // Note: Migrations are not automatically moved. Users should organize migrations manually.
    }

    /**
     * Configure Spatie Permission for multi-tenancy.
     */
    protected function configureSpatiePermission(): void
    {
        // Check if Spatie Permission is installed
        if (! class_exists(\Spatie\Permission\PermissionServiceProvider::class)) {
            info('Spatie Permission not installed, skipping Spatie Permission configuration.');
            info('💡 To use Spatie Permission with multi-tenancy, install it first: composer require spatie/laravel-permission');

            return;
        }

        // Publish and move migrations
        $migrationsPublished = $this->publishAndMoveSpatiePermissionMigrations();

        // Only add event listeners if migrations were successfully published or already exist
        if ($migrationsPublished) {
            // Add event listeners to TenancyServiceProvider
            $this->addSpatiePermissionEventListeners();
        }
    }

    /**
     * Publish Spatie Permission migrations and move them to tenant folder.
     *
     * @return bool Returns true if migrations were published/moved successfully, false otherwise
     */
    protected function publishAndMoveSpatiePermissionMigrations(): bool
    {
        $projectRoot = base_path();
        $migrationsPath = database_path('migrations');
        $tenantMigrationsPath = database_path('migrations/tenant');

        // Check if migrations already exist in tenant folder
        $existingTenantMigrations = File::glob($tenantMigrationsPath.'/*_create_permission_tables.php');
        if (! empty($existingTenantMigrations)) {
            info('Spatie Permission migrations already exist in tenant folder.');
            // Check if there are any in main folder that need to be moved
            $mainMigrations = File::glob($migrationsPath.'/*_create_permission_tables.php');
            if (! empty($mainMigrations)) {
                foreach ($mainMigrations as $migrationFile) {
                    $fileName = basename($migrationFile);
                    File::delete($migrationFile);
                    info("✅ Removed duplicate Spatie Permission migration {$fileName} from main migrations folder");
                }
            }

            return true;
        }

        // Check if migrations exist in main folder (maybe already published)
        $existingMigrations = File::glob($migrationsPath.'/*_create_permission_tables.php');
        if (! empty($existingMigrations)) {
            info('Found existing Spatie Permission migrations in main folder, moving to tenant folder...');
        } else {
            // Publish migrations
            info('Publishing Spatie Permission migrations...');
            $publishOutput = [];
            $publishReturnCode = 0;
            exec("cd {$projectRoot} && php artisan vendor:publish --provider=\"Spatie\Permission\PermissionServiceProvider\" --tag=\"migrations\" --no-interaction 2>&1", $publishOutput, $publishReturnCode);

            if ($publishReturnCode !== 0) {
                $outputString = implode("\n", $publishOutput);
                // Check if the error is because migrations are already published
                if (str_contains($outputString, 'Nothing to publish') || str_contains($outputString, 'already exists')) {
                    // Check again if migrations exist
                    $existingMigrations = File::glob($migrationsPath.'/*_create_permission_tables.php');
                    if (empty($existingMigrations)) {
                        warning('Spatie Permission migrations could not be published. They may already be published or the package may not be properly installed.');
                        warning('You can try manually: php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="migrations"');

                        return false;
                    }
                } else {
                    warning('Could not publish Spatie Permission migrations.');
                    warning('Output: '.$outputString);
                    warning('You may need to run manually: php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="migrations"');

                    return false;
                }
            } else {
                info('✅ Published Spatie Permission migrations');
            }
        }

        // Move migrations to tenant folder
        // Create tenant migrations directory if it doesn't exist
        if (! File::isDirectory($tenantMigrationsPath)) {
            File::makeDirectory($tenantMigrationsPath, 0755, true);
        }

        // Find Spatie Permission migrations
        $migrationFiles = File::glob($migrationsPath.'/*_create_permission_tables.php');

        if (empty($migrationFiles)) {
            warning('No Spatie Permission migrations found to move. They may have already been moved or published to a different location.');

            return false;
        }

        foreach ($migrationFiles as $migrationFile) {
            $fileName = basename($migrationFile);
            $destination = $tenantMigrationsPath.'/'.$fileName;

            if (! File::exists($destination)) {
                File::move($migrationFile, $destination);
                info("✅ Moved Spatie Permission migration {$fileName} to tenant migrations folder");
            } else {
                // If already exists in tenant folder, remove the one in main folder
                File::delete($migrationFile);
                info("✅ Removed duplicate Spatie Permission migration {$fileName} from main migrations folder");
            }
        }

        return true;
    }

    /**
     * Add Spatie Permission event listeners to TenancyServiceProvider.
     */
    protected function addSpatiePermissionEventListeners(): void
    {
        $providerPath = app_path('Providers/TenancyServiceProvider.php');

        if (! File::exists($providerPath)) {
            warning('TenancyServiceProvider not found. Spatie Permission event listeners will need to be added manually.');

            return;
        }

        $providerContent = File::get($providerPath);

        // Check if Spatie Permission listeners already exist
        if (str_contains($providerContent, 'PermissionRegistrar') || str_contains($providerContent, 'spatie.permission.cache')) {
            info('Spatie Permission event listeners already configured in TenancyServiceProvider.');

            return;
        }

        // Spatie Permission event listeners code
        $spatieListeners = "            \Stancl\Tenancy\Events\TenancyBootstrapped::class => [\n                function (\Stancl\Tenancy\Events\TenancyBootstrapped \$event) {\n                    \$permissionRegistrar = app(\Spatie\Permission\PermissionRegistrar::class);\n                    \$permissionRegistrar->cacheKey = 'spatie.permission.cache.tenant.' . \$event->tenancy->tenant->getTenantKey();\n                },\n            ],\n            \Stancl\Tenancy\Events\TenancyEnded::class => [\n                function (\Stancl\Tenancy\Events\TenancyEnded \$event) {\n                    \$permissionRegistrar = app(\Spatie\Permission\PermissionRegistrar::class);\n                    \$permissionRegistrar->cacheKey = 'spatie.permission.cache';\n                },\n            ],";

        // Check if events() method already exists - count occurrences to detect duplicates
        $eventsMethodCount = substr_count($providerContent, 'public function events(): array');

        if ($eventsMethodCount > 1) {
            warning('TenancyServiceProvider already has multiple events() methods. Skipping Spatie Permission event listeners.');
            warning('Please manually add the event listeners or fix the duplicate methods.');

            return;
        }

        if ($eventsMethodCount === 1) {
            // Update existing events method to include Spatie Permission listeners
            // Use a more robust pattern that handles multiline method bodies
            if (preg_match('/public function events\(\): array\s*\{((?:[^{}]++|\{(?:[^{}]++|\{[^{}]*+\})*+\})*+)\}/s', $providerContent, $matches)) {
                $methodBody = $matches[1];

                // Check if Spatie listeners are already in the method
                if (str_contains($methodBody, 'PermissionRegistrar')) {
                    info('Spatie Permission event listeners already exist in events() method.');

                    return;
                }

                // Find the return array and add listeners to it
                if (preg_match('/(return\s*\[)([^\]]*)(\]\s*;?\s*)/s', $methodBody, $returnMatches)) {
                    $beforeReturn = $returnMatches[1];
                    $existingListeners = $returnMatches[2];
                    $afterReturn = $returnMatches[3];

                    // Add Spatie listeners to existing return array
                    $newListeners = $existingListeners;
                    if (! empty(trim($existingListeners))) {
                        $newListeners .= ",\n";
                    }
                    $newListeners .= $spatieListeners;

                    $newMethodBody = str_replace($returnMatches[0], $beforeReturn.$newListeners.$afterReturn, $methodBody);
                    $providerContent = str_replace($matches[0], 'public function events(): array {'.$newMethodBody.'}', $providerContent);
                } else {
                    warning('Could not find return array in existing events() method. Skipping Spatie Permission listeners.');

                    return;
                }
            } else {
                warning('Could not parse existing events() method. Skipping Spatie Permission listeners.');

                return;
            }
        } else {
            // Add events() method before the closing brace of the class
            // Find the last closing brace of the class (not nested)
            $lines = explode("\n", $providerContent);
            $newLines = [];
            $braceDepth = 0;
            $classStarted = false;
            $inserted = false;

            foreach ($lines as $line) {
                // Detect class start
                if (preg_match('/^\s*class\s+\w+/', $line)) {
                    $classStarted = true;
                    $braceDepth = 0;
                }

                if ($classStarted) {
                    $braceDepth += substr_count($line, '{') - substr_count($line, '}');

                    // When we find the closing brace of the class (braceDepth == 0 after class started)
                    if ($braceDepth === 0 && ! $inserted && trim($line) === '}') {
                        // Insert events() method before the closing brace
                        $eventsMethod = "\n    /**\n     * Configure event listeners for multi-tenancy.\n     */\n    public function events(): array\n    {\n        return [\n".$spatieListeners."\n        ];\n    }";
                        $newLines[] = $eventsMethod;
                        $newLines[] = $line;
                        $inserted = true;

                        continue;
                    }
                }

                $newLines[] = $line;
            }

            if ($inserted) {
                $providerContent = implode("\n", $newLines);
            } else {
                warning('Could not find class closing brace. Skipping Spatie Permission event listeners.');

                return;
            }
        }

        File::put($providerPath, $providerContent);
        info('✅ Added Spatie Permission event listeners to TenancyServiceProvider');
    }

    /**
     * Move Sanctum migrations to tenant folder.
     */
    protected function moveSanctumMigrationsToTenant(): void
    {
        $migrationsPath = database_path('migrations');
        $tenantMigrationsPath = database_path('migrations/tenant');

        // Create tenant migrations directory if it doesn't exist
        if (! File::isDirectory($tenantMigrationsPath)) {
            File::makeDirectory($tenantMigrationsPath, 0755, true);
        }

        // Find Sanctum migrations
        $migrationFiles = File::glob($migrationsPath.'/*_create_personal_access_tokens_table.php');

        foreach ($migrationFiles as $migrationFile) {
            $fileName = basename($migrationFile);
            $destination = $tenantMigrationsPath.'/'.$fileName;

            if (! File::exists($destination)) {
                File::move($migrationFile, $destination);
                info("✅ Moved Sanctum migration {$fileName} to tenant migrations folder");
            }
        }
    }
}
