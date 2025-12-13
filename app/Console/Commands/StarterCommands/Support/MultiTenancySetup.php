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

    /**
     * Register tenant routes in bootstrap/app.php.
     */
    protected function registerTenantRoutes(): void
    {
        $appPath = base_path('bootstrap/app.php');

        if (! File::exists($appPath)) {
            return;
        }

        $appContent = File::get($appPath);

        // Check if tenant routes are already registered
        if (str_contains($appContent, 'tenant.php')) {
            info('Tenant routes already registered in bootstrap/app.php.');

            return;
        }

        // Add tenant route to withRouting call
        // Pattern: ->withRouting(web: ..., api: ..., tenant: ...)
        if (preg_match("/->withRouting\s*\(/", $appContent)) {
            // Add tenant route after the last route parameter
            $appContent = preg_replace(
                "/(health:\s*'\/up',)/",
                "$1\n        tenant: __DIR__.'/../routes/tenant.php',",
                $appContent
            );
        }

        File::put($appPath, $appContent);
        info('✅ Registered tenant routes in bootstrap/app.php');
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

        // Configure Sanctum
        $this->configureSanctum();

        // Configure Spatie Permission
        $this->configureSpatiePermission();
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

        // Add Telescope tags to features array
        // Look for 'features' => [...] pattern
        if (preg_match("/'features'\s*=>\s*\[/", $configContent)) {
            // Add TelescopeTags to features array
            $configContent = preg_replace(
                "/('features'\s*=>\s*\[)/",
                "$1\n        \\Stancl\\Tenancy\\Features\\TelescopeTags::class,",
                $configContent
            );
        } else {
            warning('Could not find features array in tenancy config. Please enable Telescope tags manually.');
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
        info('✅ Enabled Telescope tags and universal routes in tenancy config');
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
     * Configure Spatie Permission for multi-tenancy.
     */
    protected function configureSpatiePermission(): void
    {
        // Check if Spatie Permission is installed
        if (! class_exists(\Spatie\Permission\PermissionServiceProvider::class)) {
            info('Spatie Permission not installed, skipping Spatie Permission configuration.');

            return;
        }

        // Publish and move migrations
        $this->publishAndMoveSpatiePermissionMigrations();

        // Add event listeners to TenancyServiceProvider
        $this->addSpatiePermissionEventListeners();
    }

    /**
     * Publish Spatie Permission migrations and move them to tenant folder.
     */
    protected function publishAndMoveSpatiePermissionMigrations(): void
    {
        $projectRoot = base_path();

        // Publish migrations
        info('Publishing Spatie Permission migrations...');
        $publishOutput = [];
        $publishReturnCode = 0;
        exec("cd {$projectRoot} && php artisan vendor:publish --provider=\"Spatie\Permission\PermissionServiceProvider\" --tag=\"migrations\" --no-interaction 2>&1", $publishOutput, $publishReturnCode);

        if ($publishReturnCode !== 0) {
            warning('Could not publish Spatie Permission migrations. You may need to run this manually.');
            warning('Command: php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="migrations"');

            return;
        }

        info('✅ Published Spatie Permission migrations');

        // Move migrations to tenant folder
        $migrationsPath = database_path('migrations');
        $tenantMigrationsPath = database_path('migrations/tenant');

        // Create tenant migrations directory if it doesn't exist
        if (! File::isDirectory($tenantMigrationsPath)) {
            File::makeDirectory($tenantMigrationsPath, 0755, true);
        }

        // Find Spatie Permission migrations
        $migrationFiles = File::glob($migrationsPath.'/*_create_permission_tables.php');

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

        // Check if events() method already exists
        if (preg_match('/public function events\(\): array/', $providerContent)) {
            // Update existing events method to include Spatie Permission listeners
            if (preg_match('/public function events\(\): array\s*\{([^}]*return\s*\[)([^\]]*)(\]\s*;?\s*)\}/s', $providerContent, $matches)) {
                $beforeReturn = $matches[1];
                $existingListeners = $matches[2];
                $afterReturn = $matches[3];

                // Check if Spatie listeners are already in the array
                if (! str_contains($existingListeners, 'PermissionRegistrar')) {
                    // Add Spatie listeners to existing return array
                    $newListeners = $existingListeners;
                    if (! empty(trim($existingListeners))) {
                        $newListeners .= ",\n";
                    }
                    $newListeners .= $spatieListeners;

                    $providerContent = str_replace(
                        $matches[0],
                        'public function events(): array {'.$beforeReturn.$newListeners.$afterReturn.'}',
                        $providerContent
                    );
                }
            } else {
                // Try simpler pattern - just add before closing brace of events method
                $providerContent = preg_replace(
                    '/(public function events\(\): array\s*\{[^}]*return\s*\[)([^\]]*)(\]\s*;?\s*\})/s',
                    '$1$2,'."\n".$spatieListeners.'$3',
                    $providerContent
                );
            }
        } else {
            // Add events() method before the closing brace of the class
            $eventsMethod = "\n\n    /**\n     * Configure event listeners for multi-tenancy.\n     */\n    public function events(): array\n    {\n        return [\n".$spatieListeners."\n        ];\n    }";

            $providerContent = preg_replace(
                '/(\n\})$/',
                $eventsMethod.'$1',
                $providerContent
            );
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
