{{--
    DataTable Row Actions Component
    - datatable: The datatable instance
    - row: The current row object
--}}
@props(['datatable', 'row'])

<x-ui.dropdown placement="end"
               menu
               menuSize="sm"
               teleport>
    <x-slot:trigger>
        <x-ui.button type="button"
                     size="sm"
                     variant="ghost"
                     class="btn-square">
            <x-ui.icon name="ellipsis-vertical"
                       size="sm"></x-ui.icon>
        </x-ui.button>
    </x-slot:trigger>

    @foreach ($datatable->getRowActionsForRow($row) as $action)
        @php
            $actionType = 'button';
            $actionHref = null;
            $actionClick = null;
            $wireClick = null;

            if ($action['hasRoute']) {
                $actionType = 'link';
                $actionHref = $action['route'];
            } elseif ($action['hasModal']) {
                $actionClick = "openModalOptimistically('{$action['key']}', '{$row->uuid}', " . \json_encode($action) . ')';
                $wireClick = null; // Handled by Alpine trigger
            } elseif ($action['confirm']) {
                $actionClick = "executeActionWithConfirmation('{$action['key']}', '{$row->uuid}', false, " . \json_encode($action) . ')';
            } else {
                $wireClick = "executeAction('{$action['key']}', '{$row->uuid}')";
            }
@endphp

        <x-ui.button :href="$actionHref"
                     :type="$actionHref ? null : 'button'"
                     :wire:navigate="$actionHref ? true : null"
                     :@click="$actionClick ?: null"
                     :wire:key="'row-action-' . $row->uuid . '-' . $action['key']"
                     :wire:click="$wireClick ?: null"
                     :variant="$action['variant'] ?? 'ghost'"
                     :color="$action['color'] ?? null"
                     size="sm"
                     class="justify-start">
            @if ($action['icon'])
                <x-ui.icon :name="$action['icon']"
                           size="sm"></x-ui.icon>
            @endif
            {{ $action['label'] }}
        </x-ui.button>
    @endforeach
</x-ui.dropdown>
