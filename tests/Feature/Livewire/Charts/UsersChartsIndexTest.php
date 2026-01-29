<?php

namespace Tests\Feature\Livewire\Charts;

use App\Livewire\Charts\Users\UsersChartsIndex;
use App\Models\User;
use App\Services\Stats\Data\ChartPayload;
use App\Services\Stats\Data\MetricPayload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UsersChartsIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_can_render()
    {
        Livewire::test(UsersChartsIndex::class)
            ->assertStatus(200);
    }

    public function test_computed_properties_return_correct_types()
    {
        User::factory()->count(5)->create();

        $component = Livewire::test(UsersChartsIndex::class);

        $totalUsers = $component->instance()->totalUsersStat();
        $this->assertInstanceOf(MetricPayload::class, $totalUsers);
        $this->assertEquals('Total Users', $totalUsers->label);

        $chart = $component->instance()->registrationsChart();
        $this->assertInstanceOf(ChartPayload::class, $chart);
        $this->assertEquals('line', $chart->type->value);
    }
}
