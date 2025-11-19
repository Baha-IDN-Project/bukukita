<flux:navlist variant="outline">
    <flux:navlist.group :heading="__('Admin Panel')" class="grid">
        <flux:navlist.item icon="home" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate>
            {{ __('Dashboard') }}
        </flux:navlist.item>

        <flux:navlist.item icon="users" :href="route('admin.member')" :current="request()->routeIs('admin.member')" wire:navigate>
            {{ __('Member') }}
        </flux:navlist.item>

        <flux:navlist.item icon="clipboard" :href="route('admin.category')" :current="request()->routeIs('admin.category')" wire:navigate>
            {{ __('Kategori') }}
        </flux:navlist.item>

        <flux:navlist.item icon="book-open" :href="route('admin.buku')" :current="request()->routeIs('admin.buku')" wire:navigate>
            {{ __('Buku') }}
        </flux:navlist.item>

        <flux:navlist.item icon="folder-plus" :href="route('admin.peminjaman')" :current="request()->routeIs('admin.peminjaman')" wire:navigate>
            {{ __('Peminjaman') }}
        </flux:navlist.item>

        <flux:navlist.item icon="tv" :href="route('admin.stock-monitoring')" :current="request()->routeIs('admin.stock-monitoring')" wire:navigate>
            {{ __('Stok Buku') }}
        </flux:navlist.item>

        <flux:navlist.item icon="book-open-text" :href="route('admin.review')" :current="request()->routeIs('admin.review')" wire:navigate>
            {{ __('Review') }}
        </flux:navlist.item>

    </flux:navlist.group>
</flux:navlist>
