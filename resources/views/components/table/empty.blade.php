@props([
    'columnsCount' => 1,
    'message' => null,
    'icon' => 'user-group',
])

<tr>
    <td colspan="{{ $columnsCount }}" class="text-center py-12">
        <div class="flex flex-col items-center gap-4">
            <x-ui.icon :name="$icon" class="h-12 w-12 opacity-30"></x-ui.icon>
            <p class="text-base-content/60">{{ $message ?? __('ui.table.empty') }}</p>
        </div>
    </td>
</tr>

