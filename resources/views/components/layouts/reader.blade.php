<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Baca Buku' }} - BukuKita</title>

    @vite(['resources/css/app.css'])

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        // Set Worker PDF.js
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    </script>

    <style>
        /* Custom Scrollbar agar cantik */
        .pdf-container::-webkit-scrollbar { width: 8px; }
        .pdf-container::-webkit-scrollbar-track { background: #f1f1f1; }
        .pdf-container::-webkit-scrollbar-thumb { background: #888; border-radius: 4px; }
        .pdf-container::-webkit-scrollbar-thumb:hover { background: #555; }
    </style>
</head>
<body class="bg-gray-900 text-white overflow-hidden">
    {{ $slot }}
</body>
</html>
