<?php

use Livewire\Volt\Component;
use App\Models\Book;
use App\Models\Peminjaman;
use Livewire\Attributes\Layout; // <--- PENTING
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

// PENTING: Ganti layout ke layout 'reader' yang baru kita buat
new #[Layout('components.layouts.reader')] class extends Component {
    public Book $book;
    public $pdfUrl;

    public function mount(Book $book)
    {
        $this->book = $book;

        // Validasi Akses (Tetap Sama)
        $hasAccess = Peminjaman::where('user_id', Auth::id())
            ->where('book_id', $this->book->id)
            ->where('status', 'dipinjam')
            ->where(function ($query) {
                $query->whereNull('tanggal_harus_kembali')
                      ->orWhereDate('tanggal_harus_kembali', '>=', Carbon::today());
            })
            ->exists();

        if (!$hasAccess) {
            session()->flash('error', 'Akses ditolak.');
            return redirect()->route('user.dashboard');
        }

        // Generate URL ke Controller Stream
        $this->pdfUrl = route('book.stream', ['book' => $this->book->slug]);
    }
};
?>

<div class="flex flex-col h-screen" x-data="pdfViewer()">

    {{-- Header Reader --}}
    <div class="flex items-center justify-between px-4 py-3 bg-gray-800 border-b border-gray-700 shadow-md z-10">
        <div class="flex items-center gap-4">
            {{-- Tombol Kembali (Langsung ke Dashboard, Full Reload agar Script Flux Refresh) --}}
            <a href="{{ route('user.rak') }}" class="text-gray-300 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h1 class="text-sm md:text-lg font-semibold text-gray-100 truncate max-w-[200px] md:max-w-md">
                {{ $book->judul }}
            </h1>
        </div>

        {{-- Kontrol Halaman --}}
        <div class="flex items-center gap-2 md:gap-4 bg-gray-700 rounded-lg px-2 py-1">
            <button @click="prevPage" class="p-1 hover:bg-gray-600 rounded disabled:opacity-50" :disabled="pageNum <= 1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </button>

            <span class="text-sm font-mono">
                <span x-text="pageNum"></span> / <span x-text="totalPage || '...'"></span>
            </span>

            <button @click="nextPage" class="p-1 hover:bg-gray-600 rounded disabled:opacity-50" :disabled="pageNum >= totalPage">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </button>
        </div>
    </div>

    {{-- Area Baca (Canvas PDF) --}}
    <div class="flex-1 overflow-auto bg-gray-900 relative flex justify-center p-4 md:p-8 pdf-container" id="main-scroll">

        {{-- Loading State --}}
        <div x-show="loading" class="absolute inset-0 flex items-center justify-center bg-gray-900 z-20">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-white"></div>
        </div>

        {{-- Error State --}}
        <div x-show="error" style="display: none;" class="absolute inset-0 flex flex-col items-center justify-center text-red-400 z-20">
            <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <p x-text="errorMessage"></p>
        </div>

        {{-- Canvas PDF --}}
        <canvas id="the-canvas" class="shadow-2xl"></canvas>
    </div>

    {{-- Script Javascript Logic --}}
    <script>
        function pdfViewer() {
            // --- PERBAIKAN UTAMA ---
            // Simpan objek PDF di variabel lokal (Closure), BUKAN di dalam return object Alpine.
            // Ini mencegah Alpine mengubahnya menjadi Proxy yang menyebabkan error.
            let pdfDoc = null;

            return {
                url: '{{ $pdfUrl }}',
                // pdfDoc: null,  <-- HAPUS BARIS INI (Jangan taruh di sini)
                pageNum: 1,
                totalPage: 0,
                pageRendering: false,
                pageNumPending: null,
                scale: 1.5,
                canvas: null,
                ctx: null,
                loading: true,
                error: false,
                errorMessage: '',

                init() {
                    this.canvas = document.getElementById('the-canvas');
                    this.ctx = this.canvas.getContext('2d');

                    // Load Dokumen
                    pdfjsLib.getDocument(this.url).promise.then((pdfDoc_) => {
                        // Simpan ke variabel lokal (Raw Object)
                        pdfDoc = pdfDoc_;

                        // Update UI (Hanya ambil angkanya saja yang reaktif)
                        this.totalPage = pdfDoc.numPages;
                        this.loading = false;
                        this.renderPage(this.pageNum);
                    }).catch((err) => {
                        console.error('Error loading PDF:', err);
                        this.loading = false;
                        this.error = true;
                        this.errorMessage = 'Gagal memuat dokumen. File mungkin rusak atau tidak ditemukan.';
                    });
                },

                renderPage(num) {
                    this.pageRendering = true;

                    // Panggil dari variabel lokal pdfDoc
                    pdfDoc.getPage(num).then((page) => {
                        const containerWidth = document.getElementById('main-scroll').clientWidth - 40;
                        const viewportOriginal = page.getViewport({scale: 1});

                        let responsiveScale = this.scale;
                        if (viewportOriginal.width > containerWidth) {
                            responsiveScale = containerWidth / viewportOriginal.width;
                        }

                        const viewport = page.getViewport({scale: responsiveScale});
                        this.canvas.height = viewport.height;
                        this.canvas.width = viewport.width;

                        const renderContext = {
                            canvasContext: this.ctx,
                            viewport: viewport
                        };

                        const renderTask = page.render(renderContext);

                        renderTask.promise.then(() => {
                            this.pageRendering = false;
                            if (this.pageNumPending !== null) {
                                this.renderPage(this.pageNumPending);
                                this.pageNumPending = null;
                            }
                        });
                    });

                    document.getElementById('main-scroll').scrollTop = 0;
                },

                queueRenderPage(num) {
                    if (this.pageRendering) {
                        this.pageNumPending = num;
                    } else {
                        this.renderPage(num);
                    }
                },

                prevPage() {
                    if (this.pageNum <= 1) return;
                    this.pageNum--;
                    this.queueRenderPage(this.pageNum);
                },

                nextPage() {
                    // Gunakan variabel lokal pdfDoc untuk cek numPages jika perlu,
                    // tapi this.totalPage sudah kita simpan sebelumnya.
                    if (this.pageNum >= this.totalPage) return;
                    this.pageNum++;
                    this.queueRenderPage(this.pageNum);
                }
            }
        }
    </script>
</div>
