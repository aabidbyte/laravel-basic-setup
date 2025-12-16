<?php

use Livewire\Component;

new class extends Component {
    //
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('ui.settings.appearance.title')" :subheading="__('ui.settings.appearance.description')">
        <div x-data="{
            theme: localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'),
            init() {
                this.applyTheme(this.theme);
                $watch('theme', value => {
                    localStorage.setItem('theme', value);
                    this.applyTheme(value);
                });
            },
            applyTheme(value) {
                document.documentElement.setAttribute('data-theme', value);
            }
        }" class="join join-vertical sm:join-horizontal">
            <input type="radio" name="theme-options" x-model="theme" value="light" class="btn join-item"
                :class="{ 'btn-active': theme === 'light' }" aria-label="{{ __('ui.settings.appearance.light') }}" />
            <input type="radio" name="theme-options" x-model="theme" value="dark" class="btn join-item"
                :class="{ 'btn-active': theme === 'dark' }" aria-label="{{ __('ui.settings.appearance.dark') }}" />
        </div>
    </x-settings.layout>
</section>
