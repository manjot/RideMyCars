<x-filament-panels::page>
    <x-filament-panels::form wire:submit="save">
        {{ $this->form }}

        <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-white/10">
            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                <x-filament::icon icon="heroicon-o-shield-check" class="w-4 h-4 text-emerald-500" />
                <span>All changes are automatically cached and synchronized to runtime services upon saving.</span>
            </div>

            <x-filament::button type="submit" size="lg" icon="heroicon-o-check-circle" color="primary">
                Save All Settings
            </x-filament::button>
        </div>
    </x-filament-panels::form>
</x-filament-panels::page>
