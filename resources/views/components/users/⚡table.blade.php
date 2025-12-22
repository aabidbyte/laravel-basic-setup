<?php

declare(strict_types=1);

use App\Constants\Permissions;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use AuthorizesRequests, WithPagination;

    #[Url(as: 'search', history: true, keep: false)]
    public string $search = '';

    #[Url(as: 'sort', history: true, keep: false)]
    public string $sortBy = 'name';

    #[Url(as: 'direction', history: true, keep: false)]
    public string $sortDirection = 'asc';

    #[Url(as: 'per_page', history: true, keep: false)]
    public int $perPage = 15;

    #[Url(as: 'verified', history: true, keep: false)]
    public ?bool $verified = null;

    #[Url(as: 'role', history: true, keep: false)]
    public ?string $role = null;

    #[Url(as: 'created_from', history: true, keep: false)]
    public ?string $createdFrom = null;

    #[Url(as: 'created_to', history: true, keep: false)]
    public ?string $createdTo = null;

    public array $selected = [];

    public bool $selectPage = false;

    public bool $selectAll = false;

    /**
     * Mount the component and authorize access.
     */
    public function mount(): void
    {
        $this->authorize(Permissions::VIEW_USERS);
    }

    /**
     * Get column configuration.
     *
     * @return array<int, array{key: string, label: string, sortable: bool}>
     */
    public function getColumns(): array
    {
        return [
            ['key' => 'name', 'label' => __('ui.table.users.name'), 'sortable' => true],
            ['key' => 'email', 'label' => __('ui.table.users.email'), 'sortable' => true],
            ['key' => 'email_verified_at', 'label' => __('ui.table.users.verified'), 'sortable' => true],
            ['key' => 'created_at', 'label' => __('ui.table.users.created_at'), 'sortable' => true],
        ];
    }

    /**
     * Get row actions configuration.
     *
     * @return array<int, array{key: string, label: string, variant: string, icon: string|null}>
     */
    public function getRowActions(): array
    {
        return [
            ['key' => 'view', 'label' => __('ui.actions.view'), 'variant' => 'ghost', 'icon' => 'eye'],
            ['key' => 'edit', 'label' => __('ui.actions.edit'), 'variant' => 'ghost', 'icon' => 'pencil'],
            ['key' => 'delete', 'label' => __('ui.actions.delete'), 'variant' => 'ghost', 'color' => 'error', 'icon' => 'trash'],
        ];
    }

    /**
     * Get bulk actions configuration.
     *
     * @return array<int, array{key: string, label: string, variant: string, color: string|null}>
     */
    public function getBulkActions(): array
    {
        return [
            ['key' => 'delete', 'label' => __('ui.actions.delete_selected'), 'variant' => 'ghost', 'color' => 'error'],
        ];
    }

    /**
     * Get paginated users.
     */
    #[Computed]
    public function rows(): LengthAwarePaginator
    {
        $query = User::query();

        // Search
        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        // Filters
        if ($this->verified !== null) {
            if ($this->verified) {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        if (! empty($this->role)) {
            $query->role($this->role);
        }

        if (! empty($this->createdFrom)) {
            $query->whereDate('created_at', '>=', $this->createdFrom);
        }

        if (! empty($this->createdTo)) {
            $query->whereDate('created_at', '<=', $this->createdTo);
        }

        // Sorting
        $allowedSortColumns = ['name', 'email', 'email_verified_at', 'created_at'];
        $sortBy = in_array($this->sortBy, $allowedSortColumns) ? $this->sortBy : 'name';
        $sortDirection = in_array($this->sortDirection, ['asc', 'desc']) ? $this->sortDirection : 'asc';

        $query->orderBy($sortBy, $sortDirection);

        return $query->paginate($this->perPage);
    }

    /**
     * Sort by column.
     */
    public function sortBy(string $key): void
    {
        if ($this->sortBy === $key) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $key;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    /**
     * Reset page when search changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    /**
     * Reset page when filters change.
     */
    public function updatedVerified(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedRole(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedCreatedFrom(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedCreatedTo(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    /**
     * Toggle select all on current page.
     */
    public function toggleSelectPage(): void
    {
        $this->selectPage = ! $this->selectPage;

        if ($this->selectPage) {
            $this->selected = $this->rows->pluck('uuid')->toArray();
        } else {
            $this->selected = [];
        }
    }

    /**
     * Toggle select all across all pages.
     */
    public function toggleSelectAll(): void
    {
        $this->selectAll = ! $this->selectAll;
        $this->selectPage = false;

        if ($this->selectAll) {
            // Get all user UUIDs matching current filters
            $query = User::query();

            if (! empty($this->search)) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            }

            if ($this->verified !== null) {
                if ($this->verified) {
                    $query->whereNotNull('email_verified_at');
                } else {
                    $query->whereNull('email_verified_at');
                }
            }

            if (! empty($this->role)) {
                $query->role($this->role);
            }

            if (! empty($this->createdFrom)) {
                $query->whereDate('created_at', '>=', $this->createdFrom);
            }

            if (! empty($this->createdTo)) {
                $query->whereDate('created_at', '<=', $this->createdTo);
            }

            $this->selected = $query->pluck('uuid')->toArray();
        } else {
            $this->selected = [];
        }
    }

    /**
     * Clear selection.
     */
    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectPage = false;
        $this->selectAll = false;
    }

    /**
     * Handle row action.
     */
    public function handleRowAction(string $action, string $userUuid): void
    {
        $user = User::where('uuid', $userUuid)->first();

        if (! $user) {
            return;
        }

        match ($action) {
            'view' => $this->dispatch('user-view', userUuid: $userUuid),
            'edit' => $this->dispatch('user-edit', userUuid: $userUuid),
            'delete' => $this->deleteUser($userUuid),
            default => null,
        };
    }

    /**
     * Handle bulk action.
     */
    public function handleBulkAction(string $action): void
    {
        if (empty($this->selected)) {
            return;
        }

        match ($action) {
            'delete' => $this->deleteUsers($this->selected),
            default => null,
        };

        $this->clearSelection();
    }

    /**
     * Handle row click.
     */
    public function rowClicked(string $userUuid): void
    {
        $this->dispatch('user-view', userUuid: $userUuid);
    }

    /**
     * Delete a single user.
     */
    protected function deleteUser(string $userUuid): void
    {
        $user = User::where('uuid', $userUuid)->first();

        if ($user) {
            $user->delete();
            $this->dispatch('user-deleted', userUuid: $userUuid);
        }
    }

    /**
     * Delete multiple users.
     */
    protected function deleteUsers(array $userUuids): void
    {
        User::whereIn('uuid', $userUuids)->delete();
        $this->dispatch('users-deleted', userUuids: $userUuids);
    }
}; ?>

<div>
    {{-- Search and Filters --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-1 gap-2">
            <x-ui.input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('ui.table.search_placeholder') }}"
                class="max-w-xs"
            />
        </div>
    </div>

    {{-- Bulk Actions Bar --}}
    @if (count($selected) > 0)
        <x-table.bulk
            :selected-count="count($selected)"
            :bulk-actions="$this->getBulkActions()"
        />
    @endif

    {{-- Table --}}
    <x-table>
        <x-table.header
            :columns="$this->getColumns()"
            :sort-by="$sortBy"
            :sort-direction="$sortDirection"
            :show-bulk="true"
            :select-page="$selectPage"
            :select-all="$selectAll"
        />

        <x-table.body>
            @forelse ($this->rows as $user)
                <x-table.row
                    wire:key="user-{{ $user->uuid }}"
                    :selected="in_array($user->uuid, $selected)"
                    wire:click="rowClicked('{{ $user->uuid }}')"
                    class="cursor-pointer hover:bg-base-200 {{ in_array($user->uuid, $selected) ? 'bg-base-200' : '' }}"
                >
                    <x-table.cell>
                        <input
                            type="checkbox"
                            wire:model="selected"
                            value="{{ $user->uuid }}"
                            wire:click.stop
                            class="checkbox checkbox-sm"
                        />
                    </x-table.cell>

                    <x-table.cell>
                        <div class="font-medium">{{ $user->name }}</div>
                    </x-table.cell>

                    <x-table.cell>
                        <div class="text-sm text-base-content/70">{{ $user->email }}</div>
                    </x-table.cell>

                    <x-table.cell>
                        @if ($user->email_verified_at)
                            <x-ui.badge color="success" size="sm">{{ __('ui.table.users.verified_yes') }}</x-ui.badge>
                        @else
                            <x-ui.badge color="warning" size="sm">{{ __('ui.table.users.verified_no') }}</x-ui.badge>
                        @endif
                    </x-table.cell>

                    <x-table.cell>
                        <div class="text-sm text-base-content/70">{{ $user->created_at->format('Y-m-d') }}</div>
                    </x-table.cell>

                    <x-table.cell>
                        <x-table.actions
                            :actions="$this->getRowActions()"
                            :item-uuid="$user->uuid"
                        />
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.empty :columns-count="count($this->getColumns()) + 2" />
            @endforelse
        </x-table.body>
    </x-table>

    {{-- Pagination --}}
    @if ($this->rows->hasPages())
        <div class="mt-6">
            <x-table.pagination :paginator="$this->rows" />
        </div>
    @endif
</div>

