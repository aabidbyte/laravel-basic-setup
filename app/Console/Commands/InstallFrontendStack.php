<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\warning;

class InstallFrontendStack extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:stack {--stack= : The frontend stack to install (blade, livewire, react, vue)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install and configure a frontend stack (Livewire, React, or Vue)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $stack = $this->option('stack') ?? select(
            label: 'Which frontend stack would you like to install?',
            options: [
                'blade' => 'Blade (Traditional server-side rendering with Blade templates)',
                'livewire' => 'Livewire (Server-side components with Volt & Flux UI)',
                'react' => 'React (Inertia.js with React)',
                'vue' => 'Vue (Inertia.js with Vue 3)',
            ],
            default: 'blade',
            hint: 'You can change this later by running the command again.'
        );

        if (! in_array($stack, ['blade', 'livewire', 'react', 'vue'])) {
            error("Invalid stack: {$stack}. Must be 'blade', 'livewire', 'react', or 'vue'.");

            return self::FAILURE;
        }

        info("Installing {$stack} stack...");

        match ($stack) {
            'blade' => $this->installBlade(),
            'livewire' => $this->installLivewire(),
            'react' => $this->installReact(),
            'vue' => $this->installVue(),
        };

        info("✅ {$stack} stack installed successfully!");
        info('');
        info('Next steps:');
        info('1. Run: npm install');
        info('2. Run: npm run build');
        info('3. Configure your .env file');
        info('4. Run: php artisan migrate');

        return self::SUCCESS;
    }

    /**
     * Install Blade-only stack.
     */
    protected function installBlade(): void
    {
        $this->info('Setting up Blade-only stack...');

        // Ensure app.js exists with basic setup
        if (! File::exists(resource_path('js/app.js'))) {
            $appJs = <<<'JS'
/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';
JS;
            File::put(resource_path('js/app.js'), $appJs);
        }

        // Set up Vite config for Blade
        $this->createBladeViteConfig();

        // Clean up other stack files
        $this->cleanupOtherStacks(['livewire', 'react', 'vue']);

        $this->info('✅ Blade stack configured. No additional packages required.');
    }

    /**
     * Install Livewire stack.
     */
    protected function installLivewire(): void
    {
        $this->info('Installing Livewire packages...');
        $this->executeCommand('composer require livewire/livewire livewire/volt livewire/flux --no-interaction');

        $this->info('Setting up Livewire structure...');

        // Ensure Livewire views directory exists
        if (! File::exists(resource_path('views/livewire'))) {
            File::makeDirectory(resource_path('views/livewire'), 0755, true);
        }

        // Copy vite config for Livewire
        if (File::exists(base_path('vite.config.livewire.js'))) {
            File::copy(base_path('vite.config.livewire.js'), base_path('vite.config.js'));
        }

        // Clean up other stack files if they exist
        $this->cleanupOtherStacks(['react', 'vue']);
    }

    /**
     * Install React stack.
     */
    protected function installReact(): void
    {
        $this->info('Installing Inertia.js and React packages...');
        $this->executeCommand('composer require inertiajs/inertia-laravel tightenco/ziggy --no-interaction');
        $this->executeCommand('npm install @inertiajs/react react react-dom @vitejs/plugin-react --save-dev');

        $this->info('Setting up React structure...');

        // Create Inertia middleware
        $this->createInertiaMiddleware();

        // Create React app entry point
        $this->createReactApp();

        // Create Inertia root view (after app entry point is created)
        $this->createInertiaAppView('app.jsx');

        // Create example pages
        $this->createReactPages();

        // Create layouts
        $this->createReactLayouts();

        // Copy vite config for React
        if (File::exists(base_path('vite.config.react.js'))) {
            File::copy(base_path('vite.config.react.js'), base_path('vite.config.js'));
        } else {
            $this->createReactViteConfig();
        }

        // Clean up other stack files
        $this->cleanupOtherStacks(['blade', 'livewire', 'vue']);
    }

    /**
     * Install Vue stack.
     */
    protected function installVue(): void
    {
        $this->info('Installing Inertia.js and Vue packages...');
        $this->executeCommand('composer require inertiajs/inertia-laravel tightenco/ziggy --no-interaction');
        $this->executeCommand('npm install @inertiajs/vue3 vue@^3 @vitejs/plugin-vue --save-dev');

        $this->info('Setting up Vue structure...');

        // Create Inertia middleware
        $this->createInertiaMiddleware();

        // Create Vue app entry point
        $this->createVueApp();

        // Create Inertia root view (after app entry point is created)
        $this->createInertiaAppView('app.js');

        // Create example pages
        $this->createVuePages();

        // Create layouts
        $this->createVueLayouts();

        // Copy vite config for Vue
        if (File::exists(base_path('vite.config.vue.js'))) {
            File::copy(base_path('vite.config.vue.js'), base_path('vite.config.js'));
        } else {
            $this->createVueViteConfig();
        }

        // Clean up other stack files
        $this->cleanupOtherStacks(['blade', 'livewire', 'react']);
    }

    /**
     * Create React app entry point.
     */
    protected function createReactApp(): void
    {
        $appJsx = <<<'JSX'
import '../css/app.css';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.jsx`, import.meta.glob('./Pages/**/*.jsx')),
    setup({ el, App, props }) {
        const root = createRoot(el);
        root.render(<App {...props} />);
    },
    progress: {
        color: '#4B5563',
    },
});
JSX;

        File::put(resource_path('js/app.jsx'), $appJsx);
    }

    /**
     * Create React example pages.
     */
    protected function createReactPages(): void
    {
        $pagesDir = resource_path('js/Pages');
        File::makeDirectory($pagesDir, 0755, true);
        File::makeDirectory($pagesDir.'/Auth', 0755, true);
        File::makeDirectory($pagesDir.'/Settings', 0755, true);

        // Dashboard
        $dashboard = <<<'JSX'
import AppLayout from '@/Layouts/AppLayout';

export default function Dashboard() {
    return (
        <AppLayout>
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900 dark:text-gray-100">
                            <h2 className="text-2xl font-bold mb-4">Dashboard</h2>
                            <p>You're logged in!</p>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
JSX;
        File::put($pagesDir.'/Dashboard.jsx', $dashboard);

        // Profile Settings
        $profile = <<<'JSX'
import AppLayout from '@/Layouts/AppLayout';

export default function Profile() {
    return (
        <AppLayout>
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900 dark:text-gray-100">
                            <h2 className="text-2xl font-bold mb-4">Profile Settings</h2>
                            <p>Profile settings page coming soon...</p>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
JSX;
        File::put($pagesDir.'/Settings/Profile.jsx', $profile);
    }

    /**
     * Create React layouts.
     */
    protected function createReactLayouts(): void
    {
        $layoutsDir = resource_path('js/Layouts');
        File::makeDirectory($layoutsDir, 0755, true);

        $appLayout = <<<'JSX'
import { Link } from '@inertiajs/react';

export default function AppLayout({ children }) {
    return (
        <div className="min-h-screen bg-gray-100 dark:bg-gray-900">
            <nav className="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16">
                        <div className="flex">
                            <Link href="/dashboard" className="flex items-center">
                                <span className="text-xl font-bold text-gray-900 dark:text-gray-100">
                                    {import.meta.env.VITE_APP_NAME || 'Laravel'}
                                </span>
                            </Link>
                        </div>
                    </div>
                </div>
            </nav>
            {children}
        </div>
    );
}
JSX;
        File::put($layoutsDir.'/AppLayout.jsx', $appLayout);
    }

    /**
     * Create Vue app entry point.
     */
    protected function createVueApp(): void
    {
        $appJs = <<<'JS'
import '../css/app.css';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
JS;

        File::put(resource_path('js/app.js'), $appJs);
    }

    /**
     * Create Vue example pages.
     */
    protected function createVuePages(): void
    {
        $pagesDir = resource_path('js/Pages');
        File::makeDirectory($pagesDir, 0755, true);
        File::makeDirectory($pagesDir.'/Auth', 0755, true);
        File::makeDirectory($pagesDir.'/Settings', 0755, true);

        // Dashboard
        $dashboard = <<<'VUE'
<template>
    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h2 class="text-2xl font-bold mb-4">Dashboard</h2>
                        <p>You're logged in!</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
</script>
VUE;
        File::put($pagesDir.'/Dashboard.vue', $dashboard);

        // Profile Settings
        $profile = <<<'VUE'
<template>
    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h2 class="text-2xl font-bold mb-4">Profile Settings</h2>
                        <p>Profile settings page coming soon...</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
</script>
VUE;
        File::put($pagesDir.'/Settings/Profile.vue', $profile);
    }

    /**
     * Create Vue layouts.
     */
    protected function createVueLayouts(): void
    {
        $layoutsDir = resource_path('js/Layouts');
        File::makeDirectory($layoutsDir, 0755, true);

        $appLayout = <<<'VUE'
<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <Link href="/dashboard" class="flex items-center">
                            <span class="text-xl font-bold text-gray-900 dark:text-gray-100">
                                {{ appName }}
                            </span>
                        </Link>
                    </div>
                </div>
            </div>
        </nav>
        <slot />
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const appName = computed(() => import.meta.env.VITE_APP_NAME || 'Laravel');
</script>
VUE;
        File::put($layoutsDir.'/AppLayout.vue', $appLayout);
    }

    /**
     * Create Blade Vite config.
     */
    protected function createBladeViteConfig(): void
    {
        $config = <<<'JS'
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
JS;
        File::put(base_path('vite.config.js'), $config);
    }

    /**
     * Create React Vite config.
     */
    protected function createReactViteConfig(): void
    {
        $config = <<<'JS'
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.jsx'],
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    server: {
        cors: true,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
JS;
        File::put(base_path('vite.config.js'), $config);
    }

    /**
     * Create Vue Vite config.
     */
    protected function createVueViteConfig(): void
    {
        $config = <<<'JS'
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    server: {
        cors: true,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
JS;
        File::put(base_path('vite.config.js'), $config);
    }

    /**
     * Clean up files from other stacks.
     */
    protected function cleanupOtherStacks(array $stacksToClean): void
    {
        foreach ($stacksToClean as $stack) {
            match ($stack) {
                'blade' => $this->cleanupBlade(),
                'livewire' => $this->cleanupLivewire(),
                'react' => $this->cleanupReact(),
                'vue' => $this->cleanupVue(),
            };
        }
    }

    /**
     * Clean up Blade files.
     */
    protected function cleanupBlade(): void
    {
        // Blade doesn't have specific files to clean up
        // This is mainly for consistency in the cleanup system
    }

    /**
     * Clean up Livewire files.
     */
    protected function cleanupLivewire(): void
    {
        // Remove Livewire-specific directories
        if (File::exists(resource_path('views/livewire'))) {
            File::deleteDirectory(resource_path('views/livewire'));
        }
    }

    /**
     * Clean up React files.
     */
    protected function cleanupReact(): void
    {
        // Remove React entry point
        if (File::exists(resource_path('js/app.jsx'))) {
            File::delete(resource_path('js/app.jsx'));
        }

        // Remove React pages and layouts
        if (File::exists(resource_path('js/Pages'))) {
            File::deleteDirectory(resource_path('js/Pages'));
        }
        if (File::exists(resource_path('js/Layouts'))) {
            File::deleteDirectory(resource_path('js/Layouts'));
        }

        // Remove Inertia-specific files (shared with Vue)
        $this->cleanupInertia();
    }

    /**
     * Clean up Vue files.
     */
    protected function cleanupVue(): void
    {
        // Remove Vue entry point if it contains Inertia/Vue code
        $appJsPath = resource_path('js/app.js');
        if (File::exists($appJsPath)) {
            $content = File::get($appJsPath);
            // Check if it's Vue's Inertia app.js (not the echo file)
            if (str_contains($content, '@inertiajs/vue3') || str_contains($content, 'createInertiaApp')) {
                File::delete($appJsPath);
            }
        }

        // Remove Vue pages and layouts
        if (File::exists(resource_path('js/Pages'))) {
            File::deleteDirectory(resource_path('js/Pages'));
        }
        if (File::exists(resource_path('js/Layouts'))) {
            File::deleteDirectory(resource_path('js/Layouts'));
        }

        // Remove Inertia-specific files (shared with React)
        $this->cleanupInertia();
    }

    /**
     * Clean up Inertia.js files (shared by React and Vue).
     */
    protected function cleanupInertia(): void
    {
        // Remove Inertia middleware
        $middlewarePath = app_path('Http/Middleware/HandleInertiaRequests.php');
        if (File::exists($middlewarePath)) {
            File::delete($middlewarePath);
        }

        // Remove Inertia root view
        $inertiaAppView = resource_path('views/app.blade.php');
        if (File::exists($inertiaAppView)) {
            File::delete($inertiaAppView);
        }

        // Remove Inertia middleware registration from bootstrap/app.php
        $this->unregisterInertiaMiddleware();
    }

    /**
     * Unregister Inertia middleware from bootstrap/app.php.
     */
    protected function unregisterInertiaMiddleware(): void
    {
        $appFile = base_path('bootstrap/app.php');
        if (! File::exists($appFile)) {
            return;
        }

        $content = File::get($appFile);

        // Check if middleware is registered
        if (! str_contains($content, 'HandleInertiaRequests')) {
            return;
        }

        // Remove HandleInertiaRequests from middleware registration
        $content = preg_replace(
            '/\s*\\\\App\\\\Http\\\\Middleware\\\\HandleInertiaRequests::class,?\s*/',
            '',
            $content
        );

        // If the middleware array is now empty, reset to empty comment
        if (preg_match('/->withMiddleware\(function \(Middleware \$middleware\): void \{([^}]+)\}/', $content, $matches)) {
            $middlewareBody = $matches[1];
            // Check if middleware body only contains empty array or whitespace
            if (preg_match('/^\s*\$middleware->web\(append:\s*\[\s*\]\);\s*$/', trim($middlewareBody))) {
                $content = preg_replace(
                    '/->withMiddleware\(function \(Middleware \$middleware\): void \{[^}]+\}/',
                    '->withMiddleware(function (Middleware $middleware): void {'."\n".
                    '        //'."\n".
                    '    })',
                    $content
                );
            }
        }

        File::put($appFile, $content);
    }

    /**
     * Create Inertia middleware.
     */
    protected function createInertiaMiddleware(): void
    {
        $middlewareDir = app_path('Http/Middleware');
        if (! File::exists($middlewareDir)) {
            File::makeDirectory($middlewareDir, 0755, true);
        }

        $middleware = <<<'PHP'
<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'ziggy' => fn () => [
                ...(new \Tighten\Ziggy\Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}
PHP;

        File::put($middlewareDir.'/HandleInertiaRequests.php', $middleware);

        // Update bootstrap/app.php to register the middleware
        $this->registerInertiaMiddleware();
    }

    /**
     * Create Inertia root view (app.blade.php).
     */
    protected function createInertiaAppView(string $entryPoint): void
    {
        $appView = <<<'BLADE'
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/css/app.css', 'resources/js/ENTRY_POINT'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
BLADE;

        $appView = str_replace('ENTRY_POINT', $entryPoint, $appView);

        File::put(resource_path('views/app.blade.php'), $appView);
    }

    /**
     * Register Inertia middleware in bootstrap/app.php.
     */
    protected function registerInertiaMiddleware(): void
    {
        $appFile = base_path('bootstrap/app.php');
        $content = File::get($appFile);

        // Check if middleware is already registered
        if (str_contains($content, 'HandleInertiaRequests')) {
            return;
        }

        // Add middleware registration
        $middlewareRegistration = <<<'PHP'
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
    })
PHP;

        // Replace the empty middleware function
        $content = preg_replace(
            '/->withMiddleware\(function \(Middleware \$middleware\): void \{\s*\/\/\s*\}\)/',
            '->withMiddleware(function (Middleware $middleware): void {'."\n".
            '        $middleware->web(append: ['."\n".
            '            \\App\\Http\\Middleware\\HandleInertiaRequests::class,'."\n".
            '        ]);'."\n".
            '    })',
            $content
        );

        File::put($appFile, $content);
    }

    /**
     * Execute a shell command.
     */
    protected function executeCommand(string $command): void
    {
        $this->info("Running: {$command}");
        exec($command.' 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            warning('Command may have failed. Output: '.implode("\n", $output));
        }
    }
}
