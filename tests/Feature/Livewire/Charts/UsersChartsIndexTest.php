<?php

use App\Livewire\Charts\Users\UsersChartsIndex;
use App\Models\User;
use App\Services\Stats\Data\ChartPayload;
use App\Services\Stats\Data\MetricPayload;
use Livewire\Livewire;

it('can render component', function () {
    Livewire::test(UsersChartsIndex::class)
        ->assertStatus(200);
});

it('returns correct types for computed properties', function () {
    User::factory()->count(5)->create();

    $component = Livewire::test(UsersChartsIndex::class);

    $totalUsers = $component->instance()->totalUsersStat();
    expect($totalUsers)->toBeInstanceOf(MetricPayload::class)
        ->and($totalUsers->label)->toBe('Total Users');

    $chart = $component->instance()->registrationsChart();
    expect($chart)->toBeInstanceOf(ChartPayload::class)
        ->and($chart->type->value)->toBe('line');
});
