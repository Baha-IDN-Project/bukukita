<?php

use Livewire\Volt\Component;
use App\Models\Book;
use App\Models\Peminjaman;
use Livewire\Attributes\Layout;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

new #[Layout('components.layouts.reader')] class extends Component {
    public Book $book;
    public $pdfUrl;

    public function mount(Book $book)
    {
        $this->book = $book;

        // Validasi Akses (Logic kamu sudah benar, saya pertahankan)
        $hasAccess = Peminjaman::where('user_id', Auth::id())
            ->where('book_id', $this->book->id)
            ->where('status', 'dipinjam')
            ->where(function ($query) {
                $query->whereNull('tanggal_harus_kembali')
                      ->orWhereDate('tanggal_harus_kembali', '>=', Carbon::today());
            })
            ->exists();

        if (Auth::user()->role === 'admin') {
            $hasAccess = true;
        }

        if (!$hasAccess) {
            session()->flash('error', 'Akses ditolak atau masa pinjam habis.');
            return redirect()->route('user.rak');
        }

        $this->pdfUrl = route('book.stream', ['book' => $this->book->slug]);
    }
};
?>

{{-- Ganti seluruh <div> sampai </script> paling bawah dengan kode ini --}}

<div class="flex flex-col h-screen bg-gray-900" x-data="pdfViewer()">

    {{-- 1. Header Navigation --}}
    <div class="flex items-center justify-between px-4 py-3 bg-gray-800 border-b border-gray-700 shadow-md z-30 relative">
        <div class="flex items-center gap-4">
            <a href="{{ route('user.rak') }}" class="text-gray-300 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h1 class="text-sm md:text-lg font-semibold text-gray-100 truncate max-w-[200px] md:max-w-md">
                {{ $book->judul }}
            </h1>
        </div>
        <div class="bg-gray-700 rounded-lg px-3 py-1 text-sm font-mono text-white shadow-inner">
            Halaman <span x-text="currentPage"></span> / <span x-text="totalPage || '...'"></span>
        </div>
    </div>

    {{-- 2. Area Scroll PDF --}}
    <div class="flex-1 overflow-y-auto relative bg-gray-900 scroll-smooth" id="pdf-scroll-container">

        {{-- Loading --}}
        <div x-show="loading" class="absolute inset-0 flex items-center justify-center z-20 h-full pt-20 pointer-events-none">
            <div class="flex flex-col items-center bg-gray-800 p-4 rounded-lg shadow-lg">
                <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500 mb-2"></div>
                <span class="text-gray-300 text-sm">Memuat PDF...</span>
            </div>
        </div>

        {{-- Error --}}
        <div x-show="error" style="display: none;" class="absolute inset-0 flex flex-col items-center justify-center text-red-400 z-20 p-4 pt-20">
             <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
             <p x-text="errorMessage" class="text-center font-semibold"></p>
             <p class="text-sm text-gray-500 mt-2">Cek Console (F12) untuk detail error.</p>
        </div>

        {{-- Container Halaman --}}
        <div id="pages-container" class="flex flex-col items-center pb-20 pt-4 gap-4 min-h-screen">
            </div>
    </div>

    {{-- IMPORT PENTING: Pastikan versi Library & Worker SAMA --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script>
        // Set Worker secara global SEBELUM Alpine jalan
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
    </script>

    <script>
        function pdfViewer() {
            let pdfDoc = null;
            let renderObserver = null;
            let pageObserver = null;

            return {
                url: '{{ $pdfUrl }}',
                bookId: '{{ $book->id }}',
                currentPage: 1,
                totalPage: 0,
                loading: true,
                error: false,
                errorMessage: '',

                init() {
                    // Validasi Library
                    if (typeof pdfjsLib === 'undefined') {
                        this.handleError('Library PDF.js gagal dimuat. Cek koneksi internet.');
                        return;
                    }

                    console.log("Memulai load PDF dari:", this.url);

                    // Load Dokumen dengan parameter rangeChunkSize agar lebih stabil
                    const loadingTask = pdfjsLib.getDocument({
                        url: this.url,
                        rangeChunkSize: 65536,
                        disableAutoFetch: false,
                    });

                    loadingTask.promise.then((pdfDoc_) => {
                        console.log("PDF Berhasil dimuat. Total halaman:", pdfDoc_.numPages);
                        pdfDoc = pdfDoc_;
                        this.totalPage = pdfDoc.numPages;
                        this.loading = false;

                        this.generatePagePlaceholders();
                        this.restoreReadingPosition();

                    }).catch((err) => {
                        console.error('Error Detail:', err);

                        // Deteksi Error Umum
                        let msg = 'Gagal memuat dokumen PDF.';
                        if (err.name === 'MissingPDFException') msg = 'File PDF tidak ditemukan.';
                        if (err.name === 'InvalidPDFException') msg = 'File PDF rusak atau format salah.';

                        this.handleError(msg + ' ' + (err.message || ''));
                    });
                },

                generatePagePlaceholders() {
                    const container = document.getElementById('pages-container');
                    if(!container) return;

                    this.setupObservers();

                    for (let pageNum = 1; pageNum <= this.totalPage; pageNum++) {
                        const pageDiv = document.createElement('div');
                        // Tinggi min-h-[800px] penting agar observer bekerja sebelum canvas dirender
                        pageDiv.className = 'relative bg-white shadow-lg min-h-[600px] w-full max-w-3xl mb-4';
                        pageDiv.setAttribute('data-page-number', pageNum);
                        pageDiv.id = `page-wrapper-${pageNum}`;

                        const canvas = document.createElement('canvas');
                        canvas.id = `page-${pageNum}`;
                        canvas.className = 'w-full h-auto block';

                        // Loader Text Sederhana
                        const loadingText = document.createElement('div');
                        loadingText.className = 'absolute inset-0 flex items-center justify-center text-gray-400 text-xs z-10';
                        loadingText.innerText = `Halaman ${pageNum}`;
                        loadingText.id = `loader-${pageNum}`;

                        pageDiv.appendChild(loadingText);
                        pageDiv.appendChild(canvas);
                        container.appendChild(pageDiv);

                        renderObserver.observe(pageDiv);
                        pageObserver.observe(pageDiv);
                    }
                },

                setupObservers() {
                    // Observer 1: Render saat terlihat
                    renderObserver = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                const pageNum = parseInt(entry.target.getAttribute('data-page-number'));
                                this.renderPage(pageNum);
                                renderObserver.unobserve(entry.target); // Stop observe setelah render
                            }
                        });
                    }, { root: null, rootMargin: '500px' }); // Pre-load 500px sebelumnya

                    // Observer 2: Update nomor halaman saat scroll
                    pageObserver = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                const pageNum = parseInt(entry.target.getAttribute('data-page-number'));
                                this.currentPage = pageNum;
                                localStorage.setItem(`book_progress_${this.bookId}`, pageNum);
                            }
                        });
                    }, { threshold: 0.1 });
                },

                renderPage(num) {
                    if(!pdfDoc) return;

                    pdfDoc.getPage(num).then((page) => {
                        const canvas = document.getElementById(`page-${num}`);
                        const loader = document.getElementById(`loader-${num}`);
                        if(!canvas) return;

                        const ctx = canvas.getContext('2d');
                        const wrapper = document.getElementById(`page-wrapper-${num}`);

                        // Kalkulasi Scale Responsif
                        const containerWidth = wrapper ? wrapper.clientWidth : 600;
                        const originalViewport = page.getViewport({ scale: 1 });
                        const scale = (containerWidth / originalViewport.width);

                        const viewport = page.getViewport({ scale: scale > 2 ? 2 : scale }); // Batasi max scale agar tidak pecah memori

                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        // Hapus min-height placeholder setelah ukuran asli diketahui
                        if(wrapper) wrapper.style.minHeight = 'auto';

                        const renderContext = {
                            canvasContext: ctx,
                            viewport: viewport
                        };

                        page.render(renderContext).promise.then(() => {
                            if(loader) loader.remove();
                        });
                    }).catch(err => console.error(`Error render page ${num}:`, err));
                },

                restoreReadingPosition() {
                    const savedPage = localStorage.getItem(`book_progress_${this.bookId}`);
                    if (savedPage) {
                        setTimeout(() => {
                            const target = document.getElementById(`page-wrapper-${savedPage}`);
                            if (target) target.scrollIntoView({ behavior: 'auto', block: 'start' });
                        }, 800);
                    }
                },

                handleError(msg) {
                    this.loading = false;
                    this.error = true;
                    this.errorMessage = msg;
                }
            }
        }
    </script>
</div>
