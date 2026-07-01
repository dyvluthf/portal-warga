# CHECKLIST RELEASE — Portal Warga

> Checklist wajib sebelum submit ke Play Store / App Store.

## Backend (Laravel)

- [ ] Domain & SSL sudah aktif (`https://api.portalwarga.com`)
- [ ] `.env` production sudah diisi (DB, Redis, FCM, Midtrans key)
- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] `APP_URL` sesuai domain
- [ ] Database migrated: `php artisan migrate --force`
- [ ] Cache config/routes/views: `php artisan config:cache && php artisan route:cache && php artisan view:cache`
- [ ] Queue worker berjalan via Supervisor
- [ ] Scheduler cron sudah diset: `* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1`
- [ ] Storage link: `php artisan storage:link`
- [ ] CORS `allowed_origins` berisi domain frontend

## Web Admin (React)

- [ ] `.env.production` dengan `VITE_API_BASE_URL` yang benar
- [ ] Build produksi: `npm run build`
- [ ] File `dist/` di-deploy ke static hosting / Nginx
- [ ] Nginx SPA fallback ke `index.html` untuk client-side routing
- [ ] HTTPS aktif di domain `admin.portalwarga.com`

## Mobile App (Flutter — Android)

- [ ] **App Icon**: sudah di-generate (`flutter pub run flutter_launcher_icons`)
- [ ] **Splash Screen**: sudah di-generate (`flutter pub run flutter_native_splash:create`)
- [ ] **Keystore**: file `android/keystore/release.keystore` sudah ada
- [ ] **Keystore credential**: env `KEYSTORE_PASSWORD`, `KEY_ALIAS`, `KEY_PASSWORD` sudah diset
- [ ] **versionCode & versionName** di `build.gradle.kts` sudah sesuai (naikkan tiap rilis)
- [ ] **Privacy Policy URL**: sudah disiapkan dan diisi di Play Console
- [ ] **Permission kamera**: hanya request saat dibutuhkan (image_picker)
- [ ] **Permission lokasi**: hanya request saat buat pengaduan (jika digunakan)
- [ ] **Permission storage**: minimal (gunakan scoped storage / media store)
- [ ] **API Base URL** di `api_service.dart` sudah指向 production (`https://api.portalwarga.com/api/v1`)
- [ ] **Firebase google-services.json**: sudah ada di `android/app/`
- [ ] **Build APK / AAB**: `flutter build appbundle --release`
- [ ] **Test di device fisik**: login, buat pengaduan, ajukan surat, upload foto
- [ ] **Test offline**: pastikan error handling tidak force close

## Play Store Submission

- [ ] Listing detail aplikasi (deskripsi, screenshot, kategori) sudah diisi
- [ ] Content rating sudah diisi
- [ ] App Bundles (.aab) sudah diupload
- [ ] Harga & distribusi sudah diset (gratis / berbayar)
- [ ] Iklan: pastikan deklarasi iklan sesuai (jika tidak ada, pilih "No")

## Catatan Penting

- Jangan commit `.env` atau file dengan credential ke repo publik
- Simpan `release.keystore` di tempat aman (jangan di repo jika publik)
- Backup database produksi secara berkala
- Pantau error via log (`storage/logs/laravel.log`) dan Sentry jika dipasang
