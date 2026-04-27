<?php

use App\Enums\Stats\ChartType;
use App\Services\Stats\ChartBuilder;
use App\Services\Stats\Data\ChartPayload;

test('can instantiate builder', function () {
    $builder = ChartBuilder::make();
    expect($builder)->toBeInstanceOf(ChartBuilder::class);
});

test('can set type and build', function () {
    $payload = ChartBuilder::make()
        ->type(ChartType::BAR)
        ->build();

    expect($payload)->toBeInstanceOf(ChartPayload::class);
    expect($payload->type)->toBe(ChartType::BAR);
});

test('can add datasets and labels', function () {
    $payload = ChartBuilder::make()
        ->type(ChartType::LINE)
        ->labels(['A', 'B'])
        ->dataset('Test Data', [1, 2], ['borderColor' => 'red'])
        ->build();

    expect($payload->labels)->toHaveCount(2);
    expect($payload->datasets)->toHaveCount(1);
    expect($payload->datasets[0]->label)->toBe('Test Data');
    expect($payload->datasets[0]->data)->toBe([1, 2]);
    expect($payload->datasets[0]->options['borderColor'])->toBe('red');
});

test('can merge options', function () {
    $payload = ChartBuilder::make()
        ->type(ChartType::PIE)
        ->options(['responsive' => false])
        ->title('My Chart')
        ->build();

    expect($payload->options['responsive'])->toBeFalse();
    expect($payload->options['plugins']['title']['display'])->toBeTrue();
    expect($payload->options['plugins']['title']['text'])->toBe('My Chart');
});

test('it serializes to normalized json', function () {
    $payload = ChartBuilder::make()
        ->type(ChartType::BAR)
        ->labels(['A'])
        ->dataset('D1', [10])
        ->build();

    $json = \json_encode($payload);
    $array = \json_decode($json, true);

    expect($array['type'])->toBe('bar');
    expect($array['data']['labels'])->toBe(['A']);
    expect($array['data']['datasets'][0]['label'])->toBe('D1');
});
