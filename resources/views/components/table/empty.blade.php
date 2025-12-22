@props([
    'columnsCount' => 1,
])

<tr>
    <td colspan="{{ $columnsCount }}" class="text-center py-12">
        <div class="flex flex-col items-center gap-4">
            <x-ui.icon name="user-group" class="h-12 w-12 opacity-30" />
            <p class="text-base-content/60">{{ __('ui.table.empty') }}</p>
        </div>
    </td>
</tr>

