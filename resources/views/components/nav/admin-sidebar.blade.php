<flux:navlist variant="outline">
    <flux:navlist.group :heading="__('Admin Panel')" class="grid">
        <flux:navlist.item icon="home" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate>
            {{ __('Dashboard') }}
        </flux:navlist.item>
        <flux:navlist.item icon="users" :href="route('admin.member')" :current="request()->routeIs('admin.member')" wire:navigate>
            {{ __('Manajemen Member') }}
        </flux:navlist.item>
        <flux:navlist.item icon="book-open-text" :href="route('admin.buku')" :current="request()->routeIs('admin.buku')" wire:navigate>
            {{ __('Manajemen Buku') }}
        </flux:navlist.item>
    </flux:navlist.group>
</flux:navlist>
