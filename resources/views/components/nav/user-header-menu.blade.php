<flux:navbar class="-mb-px max-lg:hidden">
    <flux:navbar.item icon="home" :href="route('user.dashboard')" :current="request()->routeIs('user.dashboard')" wire:navigate>
        {{ __('Beranda') }}
    </flux:navbar.item>
    <flux:navbar.item icon="book-open-text" href="#" wire:navigate>
        {{ __('Koleksi Buku') }}
    </flux:navbar.item>
</flux:navbar>
