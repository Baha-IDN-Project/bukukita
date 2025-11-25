<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Baca Buku' }} - BukuKita</title>

    {{-- Memuat CSS dan JS utama (Termasuk Alpine.js) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- PDF.js Library --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        // Konfigurasi Worker PDF.js agar render tidak nge-lag
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    </script>

    <style>
        /* Styling Scrollbar Dark Mode */
        .pdf-container::-webkit-scrollbar { width: 8px; }
        .pdf-container::-webkit-scrollbar-track { background: #111827; }
        .pdf-container::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 4px; }
        .pdf-container::-webkit-scrollbar-thumb:hover { background: #6b7280; }
    </style>
</head>
<body class="bg-gray-900 text-white overflow-hidden">
    {{ $slot }}
</body>
</html>
