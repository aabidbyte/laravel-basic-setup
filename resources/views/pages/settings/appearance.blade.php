<?php

use Livewire\Component;

new class extends Component {
    //
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Appearance')" :subheading="__('Update the appearance settings for your account')">
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
                :class="{ 'btn-active': theme === 'light' }" aria-label="{{ __('Light') }}" />
            <input type="radio" name="theme-options" x-model="theme" value="dark" class="btn join-item"
                :class="{ 'btn-active': theme === 'dark' }" aria-label="{{ __('Dark') }}" />
        </div>
    </x-settings.layout>
</section>
