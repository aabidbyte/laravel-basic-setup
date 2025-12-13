<?php

namespace App\Console\Commands\StarterCommands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\info;

class InstallFrontendStack extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:stack';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install and configure Livewire stack';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        info('Installing Livewire stack...');
        $this->installLivewire();

        info('✅ Livewire stack installed successfully!');
        info('');
        info('Next steps:');
        info('1. Run: npm install');
        info('2. Run: npm run build');
        info('3. Configure your .env file');
        info('4. Run: php artisan migrate');

        return self::SUCCESS;
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
    }

    /**
     * Execute a shell command.
     */
    protected function executeCommand(string $command): void
    {
        $this->info("Running: {$command}");
        exec($command.' 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            $this->warn('Command may have failed. Output: '.implode("\n", $output));
        }
    }
}
