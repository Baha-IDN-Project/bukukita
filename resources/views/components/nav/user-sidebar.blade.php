<flux:navlist variant="outline">
    <flux:navlist.group :heading="__('Platform')" class="grid">
        <flux:navlist.item icon="home" :href="route('user.dashboard')" :current="request()->routeIs('user.*')" wire:navigate>
            {{ __('Dashboard') }}
        </flux:navlist.item>

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

    </flux:navlist.group>
</flux:navlist>
