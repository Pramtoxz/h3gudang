# Berkas SQL Manual

Sejak 2026-08-13, sebagian besar struktur database **tidak lagi diurus lewat berkas SQL**
melainkan lewat migration Laravel. Folder ini tinggal menyimpan yang benar-benar tidak bisa
ditangani migration.

---

## Pembagian schema

| Koneksi | Tujuan | Isi | Migration |
|---|---|---|---|
| `pgsql` *(bawaan)* | `tools` → schema **`warehouse`** | tabel sistem: `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `personal_access_tokens`, `projects`, `menus`, `menu_akses`, `migrations` | ✅ **hanya di sini** |
| `pgsql_pmo` | `menara_agung_live` → schema `pmov2` | data bisnis PMO: `users`, `tbltoko`, `keranjang`, `notifikasi`, `kampanye`, `otp_codes`, dst | ❌ jangan pernah |
| `pgsql_dms` | `dms_clone` → `public`, `data_part`, `data_fa`, `H3` | DMS | ❌ jangan pernah |

Pemisahan ini yang membuat `php artisan migrate` boleh dipakai lagi: schema `warehouse`
milik kita sepenuhnya, sedangkan `pmov2` diperlakukan sebagai cerminan yang boleh dihapus
dan disalin ulang kapan saja tanpa menghilangkan menu, hak akses, atau session.

> ⚠️ Aturannya: **migrasi hanya boleh `tools.warehouse`.** Karena `pgsql` sudah jadi koneksi
> bawaan, `php artisan migrate` tanpa opsi apa pun sudah menunjuk ke sana. Setiap berkas
> migration juga mencantumkan `protected $connection = 'pgsql'` supaya tidak bisa nyasar
> meski dijalankan dengan `--database` lain.

---

## Menyiapkan dari nol

```bash
# 1. Di Navicat, jalankan sekali:
#    database/sql/00-SETUP-WAREHOUSE.sql   (isinya cuma CREATE SCHEMA)

# 2. Dari terminal:
php artisan config:clear
php artisan migrate
php artisan db:seed
```

`db:seed` mengisi 2 project (PMO, Picking) dan 12 menu. Keduanya idempoten — seeder memakai
`updateOrCreate` dengan kunci `route`, jadi aman diulang.

---

## Isi folder

| Berkas | Kapan dipakai |
|---|---|
| [`00-SETUP-WAREHOUSE.sql`](00-SETUP-WAREHOUSE.sql) | Membuat schema `warehouse`. Juga memuat blok opsional untuk memindahkan `menu_akses` lama dari `pmov2` dan membersihkan sisa tabel sistem di sana |
| [`pembersihan-akun-sales.sql`](pembersihan-akun-sales.sql) | Sekali saja, membersihkan anomali 39 akun sales/supervisor di `pmov2.users`. **Belum pernah dijalankan.** Dari 39 akun bermasalah, 18 di antaranya JANGAN dihapus — itu user toko yang rolenya salah dan sedang aktif belanja, cukup diubah jadi dealer |
| ~~`menu-pmo.sql`~~ | **Jangan dijalankan.** Digantikan migration. Disimpan sebagai rujukan sejarah |
| ~~`izin-per-aksi.sql`~~ | **Jangan dijalankan.** Kolom izinnya sudah masuk migration `create_menu_akses_table` |

---

## Aturan menulis berkas baru

Sebelum menulis SQL manual, tanyakan dulu: **apakah ini bisa jadi migration?** Kalau
targetnya `warehouse`, jawabannya hampir selalu ya.

SQL manual hanya untuk yang di luar jangkauan migration:

1. Perubahan pada `pmov2` atau schema DMS.
2. Pembersihan data sekali jalan.
3. Pembuatan schema itu sendiri.

Kalau memang harus SQL:

- Idempoten — `IF NOT EXISTS` / `WHERE NOT EXISTS`, hindari `DROP` yang tidak dijaga.
- Bungkus tiap bagian dengan `BEGIN` … `COMMIT`.
- Sertakan blok verifikasi di akhir.
- Tulis nama schema secara penuh (`warehouse.menus`); Navicat belum tentu memakai
  `search_path` yang sama dengan aplikasi.
- Rujuk menu lewat kolom `route`, bukan `id` — id dibuat ulang oleh seeder.
