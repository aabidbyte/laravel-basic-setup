<?php

declare(strict_types=1);

use App\Models\Base\BaseLandlordModel;
use App\Models\Domain;
use App\Models\Master;
use App\Models\Tenant;

test('master model extends base landlord model', function () {
    $model = new Master();
    $service = app(\App\Services\Database\DatabaseService::class);

    expect($model)->toBeInstanceOf(BaseLandlordModel::class);
    $expectedConnection = $service->createDynamicConnection(
        $service->generateLandlordDatabaseName(),
        \App\Enums\Database\ConnectionType::LANDLORD,
    );
    expect($model->getConnectionName())->toBe($expectedConnection);
});

test('tenant model extends base landlord model', function () {
    $model = new Tenant();
    $service = app(\App\Services\Database\DatabaseService::class);

    expect($model)->toBeInstanceOf(BaseLandlordModel::class);
    $expectedConnection = $service->createDynamicConnection(
        $service->generateLandlordDatabaseName(),
        \App\Enums\Database\ConnectionType::LANDLORD,
    );
    expect($model->getConnectionName())->toBe($expectedConnection);
});

test('domain model extends base landlord model', function () {
    $model = new Domain();
    $service = app(\App\Services\Database\DatabaseService::class);

    expect($model)->toBeInstanceOf(BaseLandlordModel::class);
    $expectedConnection = $service->createDynamicConnection(
        $service->generateLandlordDatabaseName(),
        \App\Enums\Database\ConnectionType::LANDLORD,
    );
    expect($model->getConnectionName())->toBe($expectedConnection);
});
