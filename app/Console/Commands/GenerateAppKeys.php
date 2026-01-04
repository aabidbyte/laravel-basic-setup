<?php

namespace App\Console\Commands;

use App\Console\Commands\StarterCommands\Support\EnvFileManager;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateAppKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-keys';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate APP_KEY and Reverb credentials (REVERB_APP_ID, REVERB_APP_KEY, REVERB_APP_SECRET)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $envManager = new EnvFileManager;

        // Generate APP_KEY if not set
        $appKey = config('app.key');
        if (empty($appKey) || $appKey === 'base64:') {
            $appKey = 'base64:' . base64_encode(random_bytes(32));
            $envManager->update(['APP_KEY' => $appKey]);
            $this->info('✅ Generated APP_KEY');
        } else {
            $this->line('ℹ️  APP_KEY already set, skipping...');
        }

        // Generate Reverb credentials
        $reverbAppId = env('REVERB_APP_ID');
        $reverbAppKey = env('REVERB_APP_KEY');
        $reverbAppSecret = env('REVERB_APP_SECRET');

        $reverbUpdates = [];

        if (empty($reverbAppId)) {
            $reverbUpdates['REVERB_APP_ID'] = (string) Str::uuid();
            $this->info('✅ Generated REVERB_APP_ID');
        }

        if (empty($reverbAppKey)) {
            $reverbUpdates['REVERB_APP_KEY'] = Str::random(20);
            $this->info('✅ Generated REVERB_APP_KEY');
        }

        if (empty($reverbAppSecret)) {
            $reverbUpdates['REVERB_APP_SECRET'] = Str::random(40);
            $this->info('✅ Generated REVERB_APP_SECRET');
        }

        if (! empty($reverbUpdates)) {
            $envManager->update($reverbUpdates);
        } else {
            $this->line('ℹ️  Reverb credentials already set, skipping...');
        }

        $this->info('✅ All keys generated successfully!');

        return self::SUCCESS;
    }
}
