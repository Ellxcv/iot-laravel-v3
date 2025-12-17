# Menggunakan Ngrok dengan Laravel

## Masalah yang Diperbaiki

Ketika menggunakan ngrok, stylesheet dan assets tidak termuat karena Laravel masih menggunakan URL `http://localhost:8000` untuk asset URLs, sedangkan ngrok menggunakan HTTPS.

## Perubahan yang Dilakukan

### 1. AppServiceProvider.php

Menambahkan force HTTPS scheme ketika aplikasi diakses melalui proxy (ngrok):

```php
public function boot(): void
{
    // Force HTTPS URLs when using ngrok or in production
    if (config('app.env') !== 'local' || request()->server('HTTP_X_FORWARDED_PROTO') === 'https') {
        \Illuminate\Support\Facades\URL::forceScheme('https');
    }
}
```

### 2. bootstrap/app.php

Konfigurasi middleware untuk trust all proxies:

```php
$middleware->trustProxies(at: '*');
```

### 3. .env.example

Menambahkan panduan konfigurasi ngrok.

## Cara Menggunakan Ngrok

### Langkah 1: Update .env

Ketika menggunakan ngrok, update file `.env` Anda:

```bash
# Dapatkan URL ngrok Anda (misalnya: https://abc123.ngrok-free.app)
APP_URL=https://abc123.ngrok-free.app
ASSET_URL=https://abc123.ngrok-free.app
```

### Langkah 2: Restart Server

Setelah mengubah `.env`, restart Laravel server:

```bash
# Stop server dengan Ctrl+C, lalu start lagi
php artisan serve
```

### Langkah 3: Jalankan Ngrok

Di terminal terpisah:

```bash
ngrok http 8000
```

### Langkah 4: Clear Cache (Opsional)

Jika masih ada masalah, clear cache Laravel:

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## Cara Kerja

1. **Trust Proxies**: Laravel sekarang mempercayai semua proxies, termasuk ngrok
2. **Force HTTPS**: Ketika Laravel mendeteksi `X-Forwarded-Proto: https` header dari ngrok, semua URL di-generate dengan HTTPS scheme
3. **Asset URL**: `ASSET_URL` memastikan semua asset (CSS, JS, images) dimuat dari ngrok URL yang benar

## Troubleshooting

### Stylesheet masih tidak muncul?

1. Pastikan `APP_URL` dan `ASSET_URL` di `.env` sudah benar
2. Clear browser cache (Ctrl+Shift+R atau Cmd+Shift+R)
3. Periksa browser console untuk error
4. Restart Laravel server setelah mengubah `.env`

### Mixed Content Warning?

Pastikan semua asset menggunakan HTTPS. Cek browser console untuk melihat resource mana yang masih HTTP.

### Ngrok "Visit Site" Warning?

Ini normal untuk ngrok free tier. Klik "Visit Site" untuk melanjutkan.
