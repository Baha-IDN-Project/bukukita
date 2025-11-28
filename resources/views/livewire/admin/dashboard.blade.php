<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Book;
use App\Models\Peminjaman;
use App\Models\Category;
use App\Models\Review;
use Carbon\Carbon;

new class extends Component
{
    public function with(): array
    {
        // --- LOGIKA CHART: Peminjaman 7 Minggu Terakhir ---
        $chartLabels = collect();
        $chartData = collect();

        for ($i = 6; $i >= 0; $i--) {
            $weekDate = now()->subWeeks($i);
            $startOfWeek = $weekDate->clone()->startOfWeek();
            $endOfWeek   = $weekDate->clone()->endOfWeek();

            // Format Label: "12 Nov" (Simpel)
            $chartLabels->push($startOfWeek->format('d M'));

            $count = Peminjaman::whereBetween('created_at', [
                $startOfWeek->format('Y-m-d 00:00:00'),
                $endOfWeek->format('Y-m-d 23:59:59')
            ])->count();

            $chartData->push($count);
        }

        return [
            'totalMembers'    => User::where('role', 'member')->count(),
            'totalBooks'      => Book::count(),
            'totalCategories' => Category::count(),
            'totalReviews'    => Review::count(),
            'totalOnLoan'     => Peminjaman::where('status', 'dipinjam')->count(),

            // Ambil 5 peminjaman terakhir untuk tabel aktivitas
            'recentActivity'  => Peminjaman::with(['user', 'book'])
                                    ->latest()
                                    ->take(5)
                                    ->get(),

            'chartLabels'     => $chartLabels,
            'chartData'       => $chartData,
        ];
    }
}; ?>

<div>
    <main class="flex-1 p-6 lg:p-10 bg-gray-50 dark:bg-gray-900 min-h-screen" wire:poll.30s>

        {{-- HEADER --}}
        <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
                    Dashboard
                </h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Ringkasan aktivitas perpustakaan dan statistik terkini.
                </p>
            </div>
            <div class="mt-4 md:mt-0">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300">
                    {{ now()->format('d F Y') }}
                </span>
            </div>
        </div>

        {{-- STATS GRID --}}
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-5 mb-8">

            <!-- Card 1: Buku -->
            <div class="p-5 bg-white rounded-xl shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide dark:text-gray-400">Total Buku</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalBooks }}</h3>
                    </div>
                    <div class="p-2 bg-blue-50 rounded-lg text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Card 2: Member -->
            <div class="p-5 bg-white rounded-xl shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide dark:text-gray-400">Member</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalMembers }}</h3>
                    </div>
                    <div class="p-2 bg-green-50 rounded-lg text-green-600 dark:bg-green-900/30 dark:text-green-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Card 3: Dipinjam -->
            <div class="p-5 bg-white rounded-xl shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide dark:text-gray-400">Sedang Dipinjam</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalOnLoan }}</h3>
                    </div>
                    <div class="p-2 bg-yellow-50 rounded-lg text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Card 4: Kategori -->
            <div class="p-5 bg-white rounded-xl shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide dark:text-gray-400">Kategori</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalCategories }}</h3>
                    </div>
                    <div class="p-2 bg-purple-50 rounded-lg text-purple-600 dark:bg-purple-900/30 dark:text-purple-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Card 5: Ulasan -->
            <div class="p-5 bg-white rounded-xl shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide dark:text-gray-400">Ulasan</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalReviews }}</h3>
                    </div>
                    <div class="p-2 bg-pink-50 rounded-lg text-pink-600 dark:bg-pink-900/30 dark:text-pink-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- CONTENT SPLIT: CHART & RECENT TABLE --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- CHART SECTION --}}
            <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Tren Peminjaman (7 Minggu Terakhir)</h3>

                <div class="relative w-full h-80"
                     wire:ignore
                     x-data
                     x-init="
                        new Chart($refs.canvas, {
                            type: 'line',
                            data: {
                                labels: @js($chartLabels),
                                datasets: [{
                                    label: 'Jumlah Peminjaman',
                                    data: @js($chartData),
                                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                                    borderColor: '#4f46e5',
                                    borderWidth: 2,
                                    pointBackgroundColor: '#ffffff',
                                    pointBorderColor: '#4f46e5',
                                    pointRadius: 4,
                                    tension: 0.3,
                                    fill: true
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        backgroundColor: '#1f2937',
                                        padding: 10,
                                        cornerRadius: 8,
                                        displayColors: false,
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        grid: { color: 'rgba(156, 163, 175, 0.1)' },
                                        ticks: { precision: 0 }
                                    },
                                    x: {
                                        grid: { display: false }
                                    }
                                }
                            }
                        });
                     ">
                    <canvas x-ref="canvas"></canvas>
                </div>
            </div>

            {{-- RECENT ACTIVITY SECTION --}}
            <div class="lg:col-span-1 bg-white rounded-xl shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700 overflow-hidden">
                <div class="p-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Aktivitas Terbaru</h3>
                </div>

                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($recentActivity as $item)
                        <div class="p-4 flex items-start space-x-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                            {{-- Avatar --}}
                            <div class="flex-shrink-0 h-9 w-9 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 text-xs font-bold dark:bg-indigo-900/50 dark:text-indigo-300">
                                {{ substr($item->user->name ?? '?', 0, 2) }}
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    {{ $item->user->name ?? 'Unknown' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                    Meminjam: <span class="italic text-gray-700 dark:text-gray-300">{{ $item->book->judul ?? 'Buku Dihapus' }}</span>
                                </p>
                                <p class="text-[10px] text-gray-400 mt-1">
                                    {{ $item->created_at->diffForHumans() }}
                                </p>
                            </div>

                            {{-- Status Badge Kecil --}}
                            <div>
                                @if($item->status == 'pending')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">Pending</span>
                                @elseif($item->status == 'dipinjam')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">Dipinjam</span>
                                @elseif($item->status == 'selesai')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">Kembali</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-sm text-gray-500 dark:text-gray-400">
                            Belum ada aktivitas peminjaman.
                        </div>
                    @endforelse
                </div>

                <div class="p-3 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700 text-center">
                    <a href="{{ route('admin.peminjaman') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                        Lihat Semua Aktivitas &rarr;
                    </a>
                </div>
            </div>

        </div>
    </main>
</div>
