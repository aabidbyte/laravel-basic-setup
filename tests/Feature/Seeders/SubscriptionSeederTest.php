<?php

use App\Models\Subscription;
use Database\Seeders\CentralSeeders\Development\CentralUserSeeder;
use Database\Seeders\CentralSeeders\Development\SubscriptionSeeder;

it('seeds development subscriptions with UUIDs when model events are disabled', function (): void {
    $this->seed(CentralUserSeeder::class);
    $this->seed(SubscriptionSeeder::class);

    $subscriptions = Subscription::query()->get();

    expect($subscriptions)->not->toBeEmpty()
        ->and($subscriptions->pluck('uuid')->filter())->toHaveCount($subscriptions->count());

    $subscriptionUuids = $subscriptions->pluck('uuid', 'tenant_id')->all();

    $this->seed(SubscriptionSeeder::class);

    expect(Subscription::query()->count())->toBe($subscriptions->count())
        ->and(Subscription::query()->pluck('uuid', 'tenant_id')->all())->toBe($subscriptionUuids);
})->group('tenancy-provisioning');
