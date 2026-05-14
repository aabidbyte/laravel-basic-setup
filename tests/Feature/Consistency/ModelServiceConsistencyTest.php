<?php

declare(strict_types=1);

namespace Tests\Feature\Consistency;

use App\Models\Plan;
use App\Models\Team;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Users\UserService;
use RuntimeException;
use Tests\TestCase;

class ModelServiceConsistencyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        asTenant();
    }

    public function test_tenant_and_plan_have_uuid_casts()
    {
        $tenant = Tenant::factory()->create();
        $plan = Plan::factory()->create();

        expect($tenant->getCasts()['id'])->toBe('string');
        expect($plan->getCasts()['uuid'])->toBe('string');
    }

    public function test_delete_user_prevents_deleting_impersonated_user()
    {
        $user = User::factory()->create();
        session(['impersonated_user_id' => $user->id]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot delete a user while they are being impersonated.');

        app(UserService::class)->deleteUser($user);
    }

    public function test_team_has_created_by_relationship()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['created_by_user_id' => $user->id]);

        expect($team->createdBy->id)->toBe($user->id);
    }
}
