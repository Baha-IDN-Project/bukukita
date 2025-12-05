# Laravel Livewire Starter Kit - Sistem Perpustakaan Digital

Aplikasi perpustakaan digital berbasis Laravel dengan Livewire, Volt, dan Flux UI. Sistem ini mendukung manajemen buku, peminjaman, kategori, dan pembacaan ebook dengan fitur role-based access untuk Admin dan Member.

## 🚀 Fitur Utama

### Admin
- Dashboard dengan statistik perpustakaan
- Manajemen member (CRUD pengguna)
- Manajemen kategori buku
- Manajemen buku dan ebook
- Monitoring peminjaman
- Monitoring stok buku
- Manajemen review buku

### Member
- Dashboard personal
- Koleksi buku pribadi
- Browse buku berdasarkan kategori
- Rak buku virtual
- Detail buku dengan review
- Pembaca ebook terintegrasi (PDF.js)

### Umum
- Autentikasi dengan Laravel Fortify
- Pengaturan profil pengguna
- Pengaturan password
- Pengaturan tampilan (appearance)
- Responsive design dengan Tailwind CSS

## 📋 Persyaratan Sistem

- PHP >= 8.2
- Composer
- Node.js & NPM
- SQLite (default) atau MySQL/PostgreSQL

## 🛠️ Instalasi

### 1. Clone Repository

```bash
git clone <repository-url>
cd <project-folder>
```

### 2. Setup Otomatis

Jalankan perintah setup yang sudah dikonfigurasi:

```bash
composer setup
```

Perintah ini akan:
- Install dependencies PHP
- Copy file `.env.example` ke `.env`
- Generate application key
- Menjalankan migrasi database
- Install dependencies Node.js
- Build assets frontend

### 3. Setup Manual (Alternatif)

Jika ingin setup manual:

```bash
# Install dependencies
composer install
npm install

# Setup environment
copy .env.example .env
php artisan key:generate

# Setup database
php artisan migrate

# Build assets
npm run build
```

### 4. Konfigurasi Database

Secara default menggunakan SQLite. File database sudah tersedia di `database/database.sqlite`.

Untuk menggunakan MySQL/PostgreSQL, edit file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=username
DB_PASSWORD=password
```

### 5. Seed Data (Opsional)

```bash
php artisan db:seed
```

## 🚀 Menjalankan Aplikasi

### Development Mode

Jalankan server development dengan satu perintah:

```bash
composer dev
```

Perintah ini akan menjalankan secara bersamaan:
- Laravel development server (http://localhost:8000)
- Queue worker
- Vite dev server (hot reload)

### Manual Development

Atau jalankan secara terpisah di terminal berbeda:

```bash
# Terminal 1 - Laravel Server
php artisan serve

# Terminal 2 - Queue Worker
php artisan queue:listen

# Terminal 3 - Vite Dev Server
npm run dev
```

### Production Build

```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🧪 Testing

Jalankan test suite:

```bash
composer test
```

Atau langsung dengan artisan:

```bash
php artisan test
```

## 📁 Struktur Proyek

```
├── app/
│   ├── Actions/          # Action classes
│   ├── Http/
│   │   └── Controllers/  # Controllers (EbookController, dll)
│   ├── Livewire/         # Livewire components
│   ├── Models/           # Eloquent models
│   └── Providers/        # Service providers
├── database/
│   ├── migrations/       # Database migrations
│   ├── seeders/          # Database seeders
│   └── factories/        # Model factories
├── resources/
│   └── views/            # Blade & Volt views
├── routes/
│   ├── web.php          # Web routes
│   └── console.php      # Console routes
└── public/              # Public assets
```

## 🔐 Role & Permissions

Aplikasi memiliki 2 role utama:

1. **Admin** - Akses penuh ke semua fitur manajemen
2. **Member** - Akses ke fitur perpustakaan dan pembacaan

## 🎨 Tech Stack

- **Backend**: Laravel 12
- **Frontend**: Livewire 3, Volt, Flux UI
- **Styling**: Tailwind CSS 4
- **Database**: SQLite (default), MySQL, PostgreSQL
- **Authentication**: Laravel Fortify
- **Build Tool**: Vite
- **Testing**: Pest PHP
- **Charts**: Chart.js

## 📚 Dokumentasi

- [Laravel Documentation](https://laravel.com/docs)
- [Livewire Documentation](https://livewire.laravel.com/docs)
- [Volt Documentation](https://livewire.laravel.com/docs/volt)
- [Tailwind CSS](https://tailwindcss.com/docs)

## 🤝 Kontribusi

Kontribusi selalu diterima! Silakan buat pull request atau laporkan issue.

## 📝 License

Proyek ini menggunakan lisensi MIT. Lihat file `LICENSE` untuk detail lebih lanjut.

## 👥 Credits

Dibuat dengan Laravel Livewire Starter Kit.
