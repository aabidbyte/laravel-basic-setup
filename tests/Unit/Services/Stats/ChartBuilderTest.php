<?php

namespace Tests\Unit\Services\Stats;

use App\Enums\Stats\ChartType;
use App\Services\Stats\ChartBuilder;
use App\Services\Stats\Data\ChartPayload;
use Tests\TestCase;

class ChartBuilderTest extends TestCase
{
    public function test_can_instantiate_builder()
    {
        $builder = ChartBuilder::make();
        $this->assertInstanceOf(ChartBuilder::class, $builder);
    }

    public function test_can_set_type_and_build()
    {
        $payload = ChartBuilder::make()
            ->type(ChartType::BAR)
            ->build();

        $this->assertInstanceOf(ChartPayload::class, $payload);
        $this->assertEquals(ChartType::BAR, $payload->type);
    }

    public function test_can_add_datasets_and_labels()
    {
        $payload = ChartBuilder::make()
            ->type(ChartType::LINE)
            ->labels(['A', 'B'])
            ->dataset('Test Data', [1, 2], ['borderColor' => 'red'])
            ->build();

        $this->assertCount(2, $payload->labels);
        $this->assertCount(1, $payload->datasets);
        $this->assertEquals('Test Data', $payload->datasets[0]->label);
        $this->assertEquals([1, 2], $payload->datasets[0]->data);
        $this->assertEquals('red', $payload->datasets[0]->options['borderColor']);
    }

    public function test_can_merge_options()
    {
        $payload = ChartBuilder::make()
            ->type(ChartType::PIE)
            ->options(['responsive' => false])
            ->title('My Chart')
            ->build();

        $this->assertFalse($payload->options['responsive']);
        $this->assertTrue($payload->options['plugins']['title']['display']);
        $this->assertEquals('My Chart', $payload->options['plugins']['title']['text']);
    }

    public function test_it_serializes_to_normalized_json()
    {
        $payload = ChartBuilder::make()
            ->type(ChartType::BAR)
            ->labels(['A'])
            ->dataset('D1', [10])
            ->build();

        $json = \json_encode($payload);
        $array = \json_decode($json, true);

        $this->assertEquals('bar', $array['type']);
        $this->assertEquals(['A'], $array['data']['labels']);
        $this->assertEquals('D1', $array['data']['datasets'][0]['label']);
    }
}
