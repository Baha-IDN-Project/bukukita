<flux:navlist variant="outline">
    <flux:navlist.group :heading="__('Admin Panel')" class="grid">
        <flux:navlist.item icon="home" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate>
            {{ __('Dashboard') }}
        </flux:navlist.item>

        <flux:navlist.item icon="users" :href="route('admin.member')" :current="request()->routeIs('admin.member')" wire:navigate>
            {{ __('Manajemen Member') }}
        </flux:navlist.item>

        <flux:navlist.item icon="users" :href="route('admin.category')" :current="request()->routeIs('admin.category')" wire:navigate>
            {{ __('Manajemen Kategori') }}
        </flux:navlist.item>

        <flux:navlist.item icon="book-open-text" :href="route('admin.buku')" :current="request()->routeIs('admin.buku')" wire:navigate>
            {{ __('Manajemen Buku') }}
        </flux:navlist.item>

        <flux:navlist.item icon="book-open-text" :href="route('admin.peminjaman')" :current="request()->routeIs('admin.peminjaman')" wire:navigate>
            {{ __('Manajemen Peminjaman') }}
        </flux:navlist.item>

        <flux:navlist.item icon="book-open-text" :href="route('admin.stock-monitoring')" :current="request()->routeIs('admin.stock-monitoring')" wire:navigate>
            {{ __('Monitoring Stock') }}
        </flux:navlist.item>

    </flux:navlist.group>
</flux:navlist>
