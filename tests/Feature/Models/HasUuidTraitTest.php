<?php

declare(strict_types=1);

use App\Models\Base\BaseModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Schema::create('test_models', function ($table): void {
        $table->id();
        $table->uuid('uuid')->unique()->index();
        $table->string('name')->nullable();
        $table->timestamps();
    });
});

class TestModel extends BaseModel
{
    protected $table = 'test_models';

    protected $fillable = ['name', 'uuid'];
}

it('automatically generates a UUID when creating a model', function (): void {
    $model = TestModel::create(['name' => 'Test']);

    expect($model->uuid)->not->toBeEmpty()
        ->and($model->uuid)->toBeString()
        ->and(strlen($model->uuid))->toBe(36); // UUID v4 format
});

it('generates unique UUIDs for different models', function (): void {
    $model1 = TestModel::create(['name' => 'Test 1']);
    $model2 = TestModel::create(['name' => 'Test 2']);

    expect($model1->uuid)->not->toBe($model2->uuid);
});

it('does not overwrite existing UUID when provided', function (): void {
    $customUuid = '550e8400-e29b-41d4-a716-446655440000';
    $model = TestModel::create([
        'name' => 'Test',
        'uuid' => $customUuid,
    ]);

    expect($model->uuid)->toBe($customUuid);
});

it('uses UUID as route key name', function (): void {
    $model = TestModel::create(['name' => 'Test']);

    expect($model->getRouteKeyName())->toBe('uuid');
});

it('can find model by UUID', function (): void {
    $model = TestModel::create(['name' => 'Test']);
    $found = TestModel::where('uuid', $model->uuid)->first();

    expect($found)->not->toBeNull()
        ->and($found->id)->toBe($model->id)
        ->and($found->uuid)->toBe($model->uuid);
});
