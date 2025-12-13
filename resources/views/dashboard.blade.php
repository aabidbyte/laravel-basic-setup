<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4">
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="card bg-base-200">
                <div class="card-body">
                    <x-placeholder-pattern class="absolute inset-0 size-full opacity-10" />
                </div>
            </div>
            <div class="card bg-base-200">
                <div class="card-body">
                    <x-placeholder-pattern class="absolute inset-0 size-full opacity-10" />
                </div>
            </div>
            <div class="card bg-base-200">
                <div class="card-body">
                    <x-placeholder-pattern class="absolute inset-0 size-full opacity-10" />
                </div>
            </div>
        </div>
        <div class="card bg-base-200 flex-1">
            <div class="card-body">
                <x-placeholder-pattern class="absolute inset-0 size-full opacity-10" />
            </div>
        </div>
    </div>
</x-layouts.app>
