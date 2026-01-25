<?php

use App\Constants\Auth\Permissions;
use App\Livewire\Bases\BasePageComponent;
use App\Models\ErrorLog;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Support\Facades\Auth;

new class extends BasePageComponent {
    public ?string $pageTitle = null;

    public ?string $pageSubtitle = 'errors.management.description';

    protected string $placeholderType = 'card';

    protected int $placeholderRows = 4;

    public ?ErrorLog $errorLog = null;

    public bool $showResolveModal = false;

    public string $resolveNotes = '';

    /**
     * Mount the component and authorize access.
     */
    public function mount(ErrorLog $errorLog): void
    {
        $this->authorize(Permissions::VIEW_ERROR_LOGS());

        $this->errorLog = $errorLog->load('user');
    }

    public function getPageTitle(): string
    {
        return __('errors.reference', ['id' => $this->errorLog->reference_id]);
    }

    /**
     * Open the resolve modal.
     */
    public function openResolveModal(): void
    {
        $this->authorize(Permissions::RESOLVE_ERROR_LOGS());
        $this->showResolveModal = true;
    }

    /**
     * Resolve the error with optional notes.
     */
    public function resolveError(): void
    {
        $this->authorize(Permissions::RESOLVE_ERROR_LOGS());

        $this->errorLog->resolve([
            'resolver_id' => Auth::id(),
            'resolver_name' => Auth::user()?->name,
            'notes' => $this->resolveNotes ?: null,
        ]);

        $this->errorLog = $this->errorLog->fresh();
        $this->showResolveModal = false;
        $this->resolveNotes = '';

        NotificationBuilder::make()->title(__('errors.management.resolve_success'))->success()->send();
    }

    /**
     * Delete the error log.
     */
    public function deleteError(): void
    {
        $this->authorize(Permissions::DELETE_ERROR_LOGS());

        $this->errorLog->delete();

        NotificationBuilder::make()->title(__('errors.management.deleted_successfully'))->success()->send();

        $this->redirect(route('admin.errors.index'), navigate: true);
    }

    /**
     * Close the resolve modal.
     */
    public function closeResolveModal(): void
    {
        $this->showResolveModal = false;
        $this->resolveNotes = '';
    }

    /**
     * Extract file and line from stack trace.
     */
    public function getFileLineFromStackTrace(): ?string
    {
        if (!$this->errorLog->stack_trace) {
            return null;
        }

        // Extract first line of stack trace which usually contains file:line
        $firstLine = explode("\n", $this->errorLog->stack_trace)[0] ?? '';
        if (preg_match('/in ([^ ]+):(\d+)/', $firstLine, $matches)) {
            return basename($matches[1]) . ':' . $matches[2];
        }

        return null;
    }
}; ?>

<x-layouts.page backHref="{{ route('admin.errors.index') }}">
    <section class="mx-auto w-full max-w-4xl space-y-6"
             @confirm-delete-error-log.window="$wire.deleteError()">
        @if ($errorLog)
            {{-- Header Card --}}
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    {{-- Header with actions --}}
                    <div class="mb-4 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-2">
                                <code class="font-mono text-xl font-bold">{{ $errorLog->reference_id }}</code>
                                <x-ui.copy-button :text="$errorLog->reference_id"
                                                  size="xs"></x-ui.copy-button>
                            </div>
                            <x-ui.badge :color="$errorLog->isResolved() ? 'success' : 'error'"
                                        size="lg">
                                {{ $errorLog->isResolved() ? __('errors.management.resolved') : __('errors.management.unresolved') }}
                            </x-ui.badge>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @if (!$errorLog->isResolved())
                                @can(Permissions::RESOLVE_ERROR_LOGS())
                                    <x-ui.button wire:click="openResolveModal"
                                                 color="success"
                                                 size="sm">
                                        <x-ui.icon name="check"
                                                   size="sm"></x-ui.icon>
                                        {{ __('errors.management.resolve_confirm') }}
                                    </x-ui.button>
                                @endcan
                            @endif

                            @can(Permissions::DELETE_ERROR_LOGS())
                                <x-ui.button @click="$dispatch('confirm-modal', {
                                             title: '{{ __('actions.delete') }}',
                                             message: '{{ __('errors.management.confirm_delete') }}',
                                             confirmColor: 'error',
                                             confirmEvent: 'confirm-delete-error-log'
                                         })"
                                             color="error"
                                             variant="ghost"
                                             size="sm">
                                    <x-ui.icon name="trash"
                                               size="sm"></x-ui.icon>
                                    {{ __('actions.delete') }}
                                </x-ui.button>
                            @endcan
                        </div>
                    </div>

                    {{-- Created timestamp --}}
                    <p class="text-base-content/60 text-sm">
                        {{ $errorLog->created_at->format('Y-m-d H:i:s') }}
                        ({{ $errorLog->created_at->diffForHumans() }})
                    </p>
                </div>
            </div>

            {{-- Exception Info --}}
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <x-ui.title level="3"
                                class="mb-4 border-b pb-2">{{ __('errors.management.exception_info') }}</x-ui.title>

                    <div class="space-y-4">
                        <div>
                            <span class="text-base-content/60 text-sm">{{ __('errors.management.exception') }}</span>
                            <p class="break-all font-mono text-sm">{{ $errorLog->exception_class }}</p>
                        </div>

                        <div>
                            <span class="text-base-content/60 text-sm">{{ __('errors.management.message') }}</span>
                            <p class="break-all font-medium">{{ $errorLog->message }}</p>
                        </div>

                        @if ($fileLine = $this->getFileLineFromStackTrace())
                            <div>
                                <span
                                      class="text-base-content/60 text-sm">{{ __('errors.management.file_line') }}</span>
                                <p class="font-mono text-sm">{{ $fileLine }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Request Info --}}
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <x-ui.title level="3"
                                class="mb-4 border-b pb-2">{{ __('errors.management.request_info') }}</x-ui.title>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <span class="text-base-content/60 text-sm">{{ __('errors.management.url') }}</span>
                            <p class="break-all font-mono text-sm">
                                @if ($errorLog->method)
                                    <x-ui.badge variant="ghost"
                                                size="xs"
                                                class="mr-1">{{ $errorLog->method }}</x-ui.badge>
                                @endif
                                {{ $errorLog->url ?? '-' }}
                            </p>
                        </div>

                        <div>
                            <span class="text-base-content/60 text-sm">{{ __('errors.management.user') }}</span>
                            <p class="font-medium">
                                @if ($errorLog->user)
                                    <x-ui.link href="{{ route('users.show', $errorLog->user->uuid) }}"
                                               wire:navigate>{{ $errorLog->user->name }}</x-ui.link>
                                @else
                                    {{ __('errors.management.guest') }}
                                @endif
                            </p>
                        </div>

                        <div>
                            <span class="text-base-content/60 text-sm">{{ __('errors.management.ip_address') }}</span>
                            <p class="font-mono text-sm">{{ $errorLog->ip ?? '-' }}</p>
                        </div>

                        <div>
                            <span class="text-base-content/60 text-sm">{{ __('errors.management.user_agent') }}</span>
                            <p class="text-base-content/80 break-all text-sm">{{ $errorLog->user_agent ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Context (Collapsible) --}}
            <div x-data="{ open: false }"
                 class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <button @click="open = !open"
                            class="flex w-full items-center justify-between text-left">
                        <x-ui.title level="3"
                                    class="border-b pb-2">{{ __('errors.management.context') }}</x-ui.title>
                        <x-ui.icon name="chevron-down"
                                   size="sm"
                                   class="transition-transform duration-200"
                                   ::class="open ? 'rotate-180' : ''"></x-ui.icon>
                    </button>

                    <div x-show="open"
                         x-collapse
                         class="mt-4">
                        @if ($errorLog->context)
                            <pre class="bg-base-200 overflow-x-auto rounded-lg p-4 font-mono text-sm">{{ json_encode($errorLog->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                        @else
                            <p class="text-base-content/60 italic">{{ __('errors.management.no_context') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Stack Trace (Collapsible) --}}
            <div x-data="{ open: false }"
                 class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <button @click="open = !open"
                            class="flex w-full items-center justify-between text-left">
                        <x-ui.title level="3"
                                    class="border-b pb-2">{{ __('errors.management.stack_trace') }}</x-ui.title>
                        <x-ui.icon name="chevron-down"
                                   size="sm"
                                   class="transition-transform duration-200"
                                   ::class="open ? 'rotate-180' : ''"></x-ui.icon>
                    </button>

                    <div x-show="open"
                         x-collapse
                         class="mt-4">
                        @if ($errorLog->stack_trace)
                            <pre class="bg-base-200 max-h-96 overflow-x-auto overflow-y-auto rounded-lg p-4 font-mono text-xs">{{ $errorLog->stack_trace }}</pre>
                        @else
                            <p class="text-base-content/60 italic">{{ __('errors.management.no_stack_trace') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Resolution Info (if resolved) --}}
            @if ($errorLog->isResolved())
                <div class="card bg-success/10 border-success/30 border shadow-xl">
                    <div class="card-body">
                        <x-ui.title level="3"
                                    class="border-success/30 mb-4 border-b pb-2">{{ __('errors.management.resolution') }}</x-ui.title>

                        <div class="space-y-3">
                            <div>
                                <span
                                      class="text-base-content/60 text-sm">{{ __('errors.management.resolved_at') }}</span>
                                <p class="font-medium">
                                    {{ $errorLog->resolved_at->format('Y-m-d H:i:s') }}
                                    ({{ $errorLog->resolved_at->diffForHumans() }})
                                </p>
                            </div>

                            @if ($errorLog->resolved_data)
                                @if (isset($errorLog->resolved_data['resolver_name']))
                                    <div>
                                        <span
                                              class="text-base-content/60 text-sm">{{ __('errors.management.resolved_by') }}</span>
                                        <p class="font-medium">{{ $errorLog->resolved_data['resolver_name'] }}</p>
                                    </div>
                                @endif

                                @if (isset($errorLog->resolved_data['notes']))
                                    <div>
                                        <span
                                              class="text-base-content/60 text-sm">{{ __('errors.management.resolution_notes') }}</span>
                                        <p class="font-medium">{{ $errorLog->resolved_data['notes'] }}</p>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
            @endif
        @else
            <div class="alert alert-error">
                <x-ui.icon name="exclamation-triangle"
                           size="sm"></x-ui.icon>
                <span>{{ __('errors.not_found') }}</span>
            </div>
        @endif
    </section>
</x-layouts.page>
