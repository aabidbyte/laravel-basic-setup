<?php

namespace App\Listeners\Database;

use App\Enums\Database\ConnectionType;
use App\Events\Database\TenantCreated;
use App\Models\User;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Throwable;

class SetupTenantDatabase implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(TenantCreated $event): void
    {
        $tenant = $event->tenant;
        $dbName = $tenant->db_name;
        $type = ConnectionType::TENANT;

        // Resolve Creator User
        $user = null;
        if ($event->creatorUserUuid) {
            if ($event->creatorConnection === ConnectionType::LANDLORD->connectionName()) {
                $user = User::where('uuid', $event->creatorUserUuid)->first();
            } elseif ($event->creatorConnection) {
                // If the user lives in a specific Master/Tenant DB, we need to switch connection to resolve them
                try {
                    $connection = configureDbConnection($event->creatorConnection);
                    $user = User::on($connection)->where('uuid', $event->creatorUserUuid)->first();
                } catch (Throwable $e) {
                    Log::warning("Could not resolve creator user '{$event->creatorUserUuid}' from connection '{$event->creatorConnection}': " . $e->getMessage());
                }
            }
        }

        try {
            // 1. Create Physical DB
            databaseService()->createDatabase($dbName, $type);

            // 2. Run Migrations & Optionally Seed
            Artisan::call('migrate:tenant', [
                'dbName' => $dbName,
                '--force' => true,
                '--seed' => $event->shouldSeed,
            ]);

            // 3. Notify Success
            if ($user) {
                if ($user->locale) {
                    app()->setLocale($user->locale);
                }

                NotificationBuilder::make()
                    ->title('messages.database.setup.tenant_ready')
                    ->subtitle($tenant->name)
                    ->content('messages.database.setup.success_content', [
                        'type' => __('types.tenant'),
                        'db' => $dbName,
                    ])
                    ->success()
                    ->toUser($user)
                    ->persist()
                    ->send();
            }
        } catch (Throwable $e) {
            // 4. Cleanup on Failure
            databaseService()->wipeDatabase($dbName, $type);
            $tenant->delete();

            // Notify Failure
            if ($user) {
                if ($user->locale) {
                    app()->setLocale($user->locale);
                }

                NotificationBuilder::make()
                    ->title('messages.database.setup.failure_title')
                    ->subtitle($tenant->name)
                    ->content('messages.database.setup.failure_content', [
                        'type' => __('types.tenant'),
                        'db' => $dbName,
                        'error' => $e->getMessage(),
                    ])
                    ->error()
                    ->toUser($user)
                    ->persist()
                    ->send();
            }

            throw $e;
        }
    }
}
