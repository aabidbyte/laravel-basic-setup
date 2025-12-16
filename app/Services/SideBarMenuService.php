<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Navigation\NavigationBuilder;
use App\Services\Navigation\NavigationItem;

class SideBarMenuService
{
    /**
     * Get top navigation menus (Platform section).
     * Returns ready-to-render arrays (no logic in Blade).
     * Security filtering is handled internally by NavigationBuilder.
     */
    public function getTopMenus(): array
    {
        return NavigationBuilder::make()
            ->title(__('ui.navigation.platform'))
            ->items(
                NavigationItem::make()
                    ->title(__('ui.navigation.dashboard'))
                    ->route('dashboard')
                    ->icon('home')
            )
            ->toArray();
    }

    /**
     * Get bottom navigation menus (Resources section).
     * Returns ready-to-render arrays (no logic in Blade).
     * Security filtering is handled internally by NavigationBuilder.
     */
    public function getBottomMenus(): array
    {
        return NavigationBuilder::make()
            ->title(__('Resources'))
            ->items(
                NavigationItem::make()
                    ->title(__('Repository'))
                    ->url('https://github.com/laravel/livewire-starter-kit')
                    ->external()
                    ->icon('folder'),

                NavigationItem::make()
                    ->title(__('Documentation'))
                    ->url('https://laravel.com/docs/starter-kits#livewire')
                    ->external()
                    ->icon('book-open')
            )
            ->toArray();
    }

    /**
     * Get user dropdown menu items.
     * Returns ready-to-render arrays (no logic in Blade).
     * Security filtering is handled internally by NavigationBuilder.
     */
    public function getUserMenus(): array
    {
        return NavigationBuilder::make()
            ->title(__('ui.navigation.user'))
            ->items(
                NavigationItem::make()
                    ->title(__('ui.navigation.settings'))
                    ->route('profile.edit')
            )
            ->toArray();
    }
}
