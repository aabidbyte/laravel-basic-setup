<?php

use Illuminate\Support\Facades\Blade;

it('renders confirm modal with outside click closing enabled by default', function () {
    $html = Blade::render(<<<'BLADE'
        <div x-data="{ confirmOpen: false }">
            <x-ui.confirm-modal id="test_confirm_modal" open-state="confirmOpen">
                <div>Body</div>
            </x-ui.confirm-modal>
        </div>
    BLADE);

    expect($html)->toContain('x-on:click.self="isOpen = false"');
});

it('can render confirm modal with outside click closing disabled', function () {
    $html = Blade::render(<<<'BLADE'
        <div x-data="{ confirmOpen: false }">
            <x-ui.confirm-modal id="test_confirm_modal" open-state="confirmOpen" :close-on-outside-click="false">
                <div>Body</div>
            </x-ui.confirm-modal>
        </div>
    BLADE);

    expect($html)->not->toContain('x-on:click.self="isOpen = false"');
});

it('can render confirm modal with backdrop transition disabled', function () {
    $html = Blade::render(<<<'BLADE'
        <div x-data="{ confirmOpen: false }">
            <x-ui.confirm-modal id="test_confirm_modal" open-state="confirmOpen" :backdrop-transition="false">
                <div>Body</div>
            </x-ui.confirm-modal>
        </div>
    BLADE);

    expect($html)->not->toContain('x-transition:enter="transition ease-out duration-200 motion-reduce:transition-opacity"');
});

it('does not rely on daisyui modal-box class for the dialog container', function () {
    $html = Blade::render(<<<'BLADE'
        <div x-data="{ confirmOpen: false }">
            <x-ui.confirm-modal id="test_confirm_modal" open-state="confirmOpen">
                <div>Body</div>
            </x-ui.confirm-modal>
        </div>
    BLADE);

    expect($html)->not->toContain('modal-box');
});
