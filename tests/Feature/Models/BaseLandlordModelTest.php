<?php

declare(strict_types=1);

use App\Enums\Database\ConnectionType;
use App\Models\Base\BaseLandlordModel;
use App\Models\Domain;
use App\Models\Master;
use App\Models\Tenant;
use App\Services\Database\DatabaseService;

test('master model extends base landlord model', function () {
    $model = new Master();
    $service = app(DatabaseService::class);

    expect($model)->toBeInstanceOf(BaseLandlordModel::class);
    $expectedConnection = $service->createDynamicConnection(
        $service->generateLandlordDatabaseName(),
        ConnectionType::LANDLORD,
    );
    expect($model->getConnectionName())->toBe($expectedConnection);
});

test('tenant model extends base landlord model', function () {
    $model = new Tenant();
    $service = app(DatabaseService::class);

    expect($model)->toBeInstanceOf(BaseLandlordModel::class);
    $expectedConnection = $service->createDynamicConnection(
        $service->generateLandlordDatabaseName(),
        ConnectionType::LANDLORD,
    );
    expect($model->getConnectionName())->toBe($expectedConnection);
});

test('domain model extends base landlord model', function () {
    $model = new Domain();
    $service = app(DatabaseService::class);

    expect($model)->toBeInstanceOf(BaseLandlordModel::class);
    $expectedConnection = $service->createDynamicConnection(
        $service->generateLandlordDatabaseName(),
        ConnectionType::LANDLORD,
    );
    expect($model->getConnectionName())->toBe($expectedConnection);
});
