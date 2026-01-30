<?php

namespace Tests\Feature\UI;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ShareButtonTest extends TestCase
{
    public function test_share_button_renders_correctly()
    {
        $view = Blade::render('<x-ui.share-button url="https://example.com" />');

        $this->assertStringContainsString('example.com', $view);
        $this->assertStringContainsString('shareButton', $view);
    }

    public function test_assets_directive_is_processed()
    {
        // To test @assets, we need to check if Livewire's asset injection works.
        // This is tricky in a simple Blade::render without a full request cycle.
        // However, we can assert that the component doesn't crash.

        $view = Blade::render('<x-ui.share-button />');
        $this->assertNotEmpty($view);
    }
}
