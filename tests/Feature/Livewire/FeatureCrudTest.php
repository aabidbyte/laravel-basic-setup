<?php

use App\Constants\Auth\Permissions;
use App\Enums\Feature\FeatureValueType;
use App\Models\Feature;
use App\Models\User;
use Database\Seeders\CentralSeeders\Production\RoleAndPermissionSeeder;
use Livewire\Livewire;
use Livewire\Volt\Volt;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignPermission(Permissions::VIEW_FEATURES());
    $this->admin->assignPermission(Permissions::CREATE_FEATURES());
    $this->admin->assignPermission(Permissions::EDIT_FEATURES());
    $this->admin->assignPermission(Permissions::DELETE_FEATURES());

    $this->actingAs($this->admin);
});

it('can render the features index page', function () {
    $this->get(route('features.index'))
        ->assertOk()
        ->assertSee(__('features.title'));
});

it('can render the create feature page', function () {
    $this->get(route('features.create'))
        ->assertOk()
        ->assertSee(__('features.create_title'));
});

it('validates required fields when creating a feature', function () {
    Volt::test('pages::features.edit')
        ->set('key', '')
        ->set('name_en_US', '')
        ->set('name_fr_FR', '')
        ->call('create')
        ->assertHasErrors(['key', 'name_en_US', 'name_fr_FR']);
});

it('can create a feature', function () {
    Volt::test('pages::features.edit')
        ->set('key', 'advanced_reports')
        ->set('name_en_US', 'Advanced Reports')
        ->set('name_fr_FR', 'Rapports avancés')
        ->set('type', FeatureValueType::BOOLEAN->value)
        ->set('default_value', 'true')
        ->call('create')
        ->assertHasNoErrors()
        ->assertRedirect(route('features.index'));

    $feature = Feature::query()->where('key', 'advanced_reports')->first();

    expect($feature)->not->toBeNull()
        ->and($feature->getTranslation('name', 'en_US'))->toBe('Advanced Reports')
        ->and($feature->default_value)->toBeTrue();
});

it('can update a feature', function () {
    $feature = Feature::factory()->create([
        'key' => 'old_key',
        'type' => FeatureValueType::STRING,
    ]);

    Volt::test('pages::features.edit', ['feature' => $feature])
        ->set('name_en_US', 'Updated Feature')
        ->set('name_fr_FR', 'Fonctionnalité mise à jour')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('features.index'));

    expect($feature->fresh()->getTranslation('name', 'en_US'))->toBe('Updated Feature');
});

it('can delete a feature from the table', function () {
    $feature = Feature::factory()->create();

    Livewire::test('tables.feature-table')
        ->call('executeAction', 'delete', $feature->uuid);

    expect(Feature::query()->whereKey($feature->id)->exists())->toBeFalse();
});
