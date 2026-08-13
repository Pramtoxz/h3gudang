-- =============================================================
-- SETUP SCHEMA warehouse
--
-- Dijalankan manual di Navicat, di database `tools`.
--
-- KENAPA SCHEMA TERPISAH
-- `pmov2` adalah salinan production dan diperlakukan sebagai cerminan: kita
-- tidak pernah mengubah strukturnya, dan setiap kali disalin ulang isinya
-- tertimpa seluruhnya. Tabel milik aplikasi @new sendiri - session, cache,
-- queue, menu, hak akses - karena itu ditaruh di schema `warehouse`.
--
-- Akibatnya:
--   * Schema pmov2 boleh dihapus dan disalin ulang kapan saja tanpa
--     menghilangkan menu, hak akses, atau session.
--   * `php artisan migrate` boleh dipakai lagi, khusus untuk schema ini.
--
-- YANG PERLU DIJALANKAN DI SINI CUMA BAGIAN 1.
-- Seluruh tabelnya dibuat oleh migration, bukan oleh berkas ini.
-- =============================================================


-- =============================================================
-- BAGIAN 1 - BUAT SCHEMA
-- =============================================================

CREATE SCHEMA IF NOT EXISTS warehouse;


-- =============================================================
-- BAGIAN 2 - LANJUTKAN DARI TERMINAL
--
--     php artisan config:clear
--     php artisan migrate
--     php artisan db:seed
--
-- migrate membuat: sessions, cache, cache_locks, jobs, job_batches,
--                  failed_jobs, projects, menus, menu_akses, migrations
-- db:seed  mengisi: 2 project (PMO, Picking) + 12 menu
--
-- Keduanya idempoten. Seeder memakai updateOrCreate dengan kunci `route`,
-- jadi aman dijalankan berulang.
-- =============================================================


-- =============================================================
-- BAGIAN 3 - PINDAHKAN HAK AKSES LAMA (OPSIONAL, SEKALI SAJA)
--
-- Sebelum 2026-08-13, `menu_akses` masih menumpang di schema pmov2. Blok ini
-- memindahkan isinya ke warehouse. Jalankan SETELAH migrate + db:seed.
--
-- Menu dicocokkan lewat kolom `route`, bukan id, karena id di warehouse dibuat
-- ulang oleh seeder dan belum tentu sama dengan id lama di pmov2.
--
-- Lewati saja kalau lebih gampang mengatur ulang lewat halaman
-- Pengaturan > Kelola Hak Akses. Pengelola IT (`it = 't'`) tidak terpengaruh:
-- mereka melihat semua menu tanpa perlu terdaftar.
-- =============================================================

-- BEGIN;
--
-- INSERT INTO warehouse.menu_akses (email, menu_id, boleh_lihat, boleh_tambah, boleh_ubah, boleh_hapus, created_at, updated_at)
-- SELECT lama.email, baru.id, lama.boleh_lihat, lama.boleh_tambah, lama.boleh_ubah, lama.boleh_hapus, NOW(), NOW()
-- FROM pmov2.menu_akses lama
-- JOIN pmov2.menus  m_lama ON m_lama.id = lama.menu_id
-- JOIN warehouse.menus baru ON baru.route = m_lama.route
-- WHERE NOT EXISTS (
--     SELECT 1 FROM warehouse.menu_akses ada
--     WHERE ada.email = lama.email AND ada.menu_id = baru.id
-- );
--
-- COMMIT;


-- =============================================================
-- BAGIAN 4 - BERSIHKAN SISA DI pmov2 (OPSIONAL, SETELAH YAKIN)
--
-- Tabel di bawah dulu dibuat @new di dalam pmov2. Sekarang tempatnya di
-- warehouse, jadi yang di pmov2 tinggal sampah yang menyesatkan.
--
-- Kalau schema pmov2 memang akan disalin ulang dari production, tidak perlu
-- dijalankan - salinan barunya sudah bersih dengan sendirinya.
--
-- JANGAN dijalankan sebelum Bagian 3 selesai dan hasilnya diperiksa.
-- =============================================================

-- DROP TABLE IF EXISTS pmov2.menu_akses;
-- DROP TABLE IF EXISTS pmov2.menus;
-- DROP TABLE IF EXISTS pmov2.projects;
-- DROP TABLE IF EXISTS pmov2.sessions;
-- DROP TABLE IF EXISTS pmov2.cache;
-- DROP TABLE IF EXISTS pmov2.cache_locks;


-- =============================================================
-- BAGIAN 5 - VERIFIKASI
-- =============================================================

-- 5a. Isi schema warehouse. Harus ada 10 tabel.
SELECT table_name
FROM information_schema.tables
WHERE table_schema = 'warehouse'
ORDER BY table_name;

-- 5b. Project dan menu hasil seeder
SELECT COALESCE(p.kode, '(global)') AS project,
       CASE WHEN m.parent_id IS NULL THEN m.nama_menu ELSE '   > ' || m.nama_menu END AS menu,
       m.route, m.urutan, m.khusus_it
FROM warehouse.menus m
LEFT JOIN warehouse.projects p ON p.id = m.project_id
ORDER BY p.urutan NULLS LAST, COALESCE(m.parent_id, m.id), m.parent_id NULLS FIRST, m.urutan;

-- 5c. Hak akses per user
SELECT ma.email, m.nama_menu,
       ma.boleh_lihat AS lihat, ma.boleh_tambah AS tambah,
       ma.boleh_ubah AS ubah, ma.boleh_hapus AS hapus
FROM warehouse.menu_akses ma
JOIN warehouse.menus m ON m.id = ma.menu_id
ORDER BY ma.email, m.urutan;

-- 5d. Pastikan pmov2 tidak lagi dipakai untuk tabel sistem.
--     Hasil yang diharapkan setelah Bagian 4: KOSONG.
SELECT table_name AS sisa_di_pmov2
FROM information_schema.tables
WHERE table_schema = 'pmov2'
  AND table_name IN ('projects', 'menus', 'menu_akses', 'sessions', 'cache', 'cache_locks')
ORDER BY table_name;
