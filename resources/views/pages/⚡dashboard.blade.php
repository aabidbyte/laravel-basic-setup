<?php

use App\Livewire\BasePageComponent;

new class extends BasePageComponent {
    public ?string $pageTitle = 'ui.pages.dashboard';
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-4">
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <div class="card bg-base-200">
            <div class="card-body">
                <x-placeholder-pattern class="absolute inset-0 size-full opacity-10"></x-placeholder-pattern>
            </div>
        </div>
        <div class="card bg-base-200">
            <div class="card-body">
                <x-placeholder-pattern class="absolute inset-0 size-full opacity-10"></x-placeholder-pattern>
            </div>
        </div>
        <div class="card bg-base-200">
            <div class="card-body">
                <x-placeholder-pattern class="absolute inset-0 size-full opacity-10"></x-placeholder-pattern>
            </div>
        </div>
    </div>
    <div class="card bg-base-200 flex-1">
        <div class="card-body">
            <x-placeholder-pattern class="absolute inset-0 size-full opacity-10"></x-placeholder-pattern>
        </div>
    </div>
</div>
