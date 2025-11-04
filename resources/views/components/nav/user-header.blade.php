<flux:navbar class="-mb-px max-lg:hidden">
    <flux:navbar.item :href="route('user.dashboard')" :current="request()->routeIs('user.dashboard')" wire:navigate>
        {{ __('Dashboard') }}
    </flux:navbar.item>
    <flux:tooltip :content="__('Buku')" position="bottom">
        <flux:navbar.item
            class="h-10 max-lg:hidden [&>div>svg]:size-5"
            icon="book-open-text"
            href="#" {{-- Ganti ke rute koleksi buku Anda --}}
            label="Buku"
        />
    </flux:tooltip>
</flux:navbar>
