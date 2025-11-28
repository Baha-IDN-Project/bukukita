<flux:navbar class="-mb-px max-lg:hidden gap-6">

    <flux:navbar.item
        icon="home"
        :href="route('user.dashboard')"
        :current="request()->routeIs('user.dashboard')"
        wire:navigate
    >
        {{ __('Beranda') }}
    </flux:navbar.item>

    <flux:navbar.item
        icon="book-open-text"
        :href="route('user.koleksi')"
        :current="request()->routeIs('user.koleksi')"
        wire:navigate
    >
        {{ __('Koleksi Buku') }}
    </flux:navbar.item>

    <flux:navbar.item
        icon="book-open"
        :href="route('user.rak')"
        :current="request()->routeIs('user.rak')"
        wire:navigate
        class="relative"
    >
        {{ __('Rak Pinjam') }}

    </flux:navbar.item>

</flux:navbar>
