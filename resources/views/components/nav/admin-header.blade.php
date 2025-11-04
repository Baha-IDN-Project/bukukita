<flux:navbar class="-mb-px max-lg:hidden">
    <flux:navbar.item :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate>
        {{ __('Dashboard') }}
    </flux:navbar.item>

    <flux:navbar.item href="#" wire:navigate>
        {{ __('Laporan') }}
    </flux:navbar.item>
</flux:navbar>
