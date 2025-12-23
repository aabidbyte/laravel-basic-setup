<?php

use Illuminate\Support\Facades\Blade;

it('renders base modal with theme-aware backdrop classes by default', function () {
    $html = Blade::render(<<<'BLADE'
        <div x-data="{ modalIsOpen: true }">
            <x-ui.base-modal open-state="modalIsOpen" title="Test">
                <div>Body</div>
            </x-ui.base-modal>
        </div>
    BLADE);

    expect($html)->toContain('bg-base-300/60')
        ->and($html)->toContain('backdrop-blur-md');
});

it('uses responsive default placement when placement is not provided', function () {
    $html = Blade::render(<<<'BLADE'
        <div x-data="{ modalIsOpen: true }">
            <x-ui.base-modal open-state="modalIsOpen" title="Test">
                <div>Body</div>
            </x-ui.base-modal>
        </div>
    BLADE);

    expect($html)->toContain('items-end')
        ->and($html)->toContain('sm:items-center');
});

it('supports combined placement values', function () {
    $html = Blade::render(<<<'BLADE'
        <div x-data="{ modalIsOpen: true }">
            <x-ui.base-modal open-state="modalIsOpen" title="Test" placement="top-left">
                <div>Body</div>
            </x-ui.base-modal>
        </div>
    BLADE);

    expect($html)->toContain('items-start')
        ->and($html)->toContain('justify-start')
        ->and($html)->toContain('pt-8');
});
