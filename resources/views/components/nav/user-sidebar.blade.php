<flux:navlist variant="outline">
    <flux:navlist.group :heading="__('Platform')" class="grid">
        <flux:navlist.item icon="home" :href="route('user.dashboard')" :current="request()->routeIs('user.*')" wire:navigate>
            {{ __('Dashboard') }}
        </flux:navlist.item>

        <flux:navlist.item icon="magnifying-glass" href="#" wire:navigate>
            {{ __('Cari Buku') }}
        </flux:navlist.item>
        <flux:navlist.item icon="bookmark" href="#" wire:navigate>
            {{ __('Pinjaman Saya') }}
        </flux:navlist.item>

    </flux:navlist.group>
</flux:navlist>
