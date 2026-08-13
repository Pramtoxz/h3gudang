-- =============================================================
-- ⚠️ BERKAS INI SUDAH TIDAK PERLU DIJALANKAN LAGI.
--
-- Seluruh isinya sudah masuk ke `00-SETUP-PMOV2.sql`, yang juga menambahkan
-- tabel infrastruktur Laravel (sessions/cache/jobs) dan kolom izin per aksi.
-- Untuk schema pmov2 yang baru disalin dari production, jalankan berkas itu.
--
-- Yang di bawah disimpan sebagai rujukan sejarah.
-- =============================================================

-- =============================================================
-- Skema menu dinamis multi-project
-- Dijalankan manual di Navicat. Target: database `tools`, schema `pmov2`
--
-- Model akses (keputusan 2026-08-11):
--   * Hak akses melekat pada PASANGAN (email user DMS, menu) - bukan lewat role.
--   * Kolom `it` di DMS public.users menandai pengelola: melihat seluruh menu
--     di seluruh project tanpa perlu terdaftar, dan satu-satunya yang boleh
--     membuka halaman Kelola Hak Akses.
--   * Project yang muncul di switcher DITURUNKAN dari menu yang dimiliki user.
--     Tidak punya satu pun menu di sebuah project = project itu tidak terlihat.
--   * Tabel `menu_role` tidak dipakai lagi, digantikan `menu_akses`.
--
-- Aman dijalankan berulang (idempoten).
-- =============================================================


-- =============================================================
-- BAGIAN 0 - HAPUS STRUKTUR LAMA
-- Jalankan HANYA kalau Anda memang ingin mulai bersih.
-- Seluruh isi menu dan hak akses lama akan hilang.
-- =============================================================

-- DROP TABLE IF EXISTS pmov2.menu_akses;
-- DROP TABLE IF EXISTS pmov2.menu_role;
-- DROP TABLE IF EXISTS pmov2.menus;
-- DROP TABLE IF EXISTS pmov2.projects;


-- =============================================================
-- BAGIAN 1 - STRUKTUR
-- =============================================================

BEGIN;

CREATE TABLE IF NOT EXISTS pmov2.projects (
    id            bigserial    PRIMARY KEY,
    kode          varchar(30)  NOT NULL,
    nama          varchar(100) NOT NULL,
    keterangan    varchar(255) NULL,
    ikon          varchar(255) NULL,
    urutan        integer      NOT NULL DEFAULT 0,
    aktif         boolean      NOT NULL DEFAULT true,
    created_at    timestamp(0) NULL,
    updated_at    timestamp(0) NULL,
    CONSTRAINT projects_kode_unique UNIQUE (kode)
);

-- project_id NULL berarti menu global: tampil di semua project.
-- Dipakai oleh menu pengaturan yang berlaku lintas project.
CREATE TABLE IF NOT EXISTS pmov2.menus (
    id            bigserial    PRIMARY KEY,
    project_id    bigint       NULL REFERENCES pmov2.projects (id) ON DELETE CASCADE,
    nama_menu     varchar(255) NOT NULL,
    ikon          varchar(255) NULL,
    route         varchar(255) NULL,
    url           varchar(255) NULL,
    parent_id     bigint       NULL REFERENCES pmov2.menus (id) ON DELETE CASCADE,
    urutan        integer      NOT NULL DEFAULT 0,
    status_aktif  boolean      NOT NULL DEFAULT true,
    khusus_it     boolean      NOT NULL DEFAULT false,
    created_at    timestamp(0) NULL,
    updated_at    timestamp(0) NULL
);

CREATE INDEX IF NOT EXISTS menus_project_id_index ON pmov2.menus (project_id);
CREATE INDEX IF NOT EXISTS menus_parent_id_index ON pmov2.menus (parent_id);

-- Kunci ke user DMS memakai email karena hanya kolom itu yang terbukti unik
-- dan selalu terisi di public.users (480/480), sedangkan username dan
-- kd_kariawan punya duplikat. Email juga stabil saat koneksi DMS berpindah
-- dari dms_clone ke dmsv2, sementara id auto-increment belum tentu sama.
CREATE TABLE IF NOT EXISTS pmov2.menu_akses (
    id          bigserial    PRIMARY KEY,
    email       varchar(255) NOT NULL,
    menu_id     bigint       NOT NULL REFERENCES pmov2.menus (id) ON DELETE CASCADE,
    created_at  timestamp(0) NULL,
    updated_at  timestamp(0) NULL,
    CONSTRAINT menu_akses_email_menu_id_unique UNIQUE (email, menu_id)
);

CREATE INDEX IF NOT EXISTS menu_akses_email_index ON pmov2.menu_akses (email);

COMMIT;


-- =============================================================
-- BAGIAN 2 - DAFTAR PROJECT
-- Picking sengaja dibuat nonaktif: strukturnya siap, isinya belum dibangun.
-- =============================================================

BEGIN;

INSERT INTO pmov2.projects (kode, nama, keterangan, ikon, urutan, aktif, created_at, updated_at)
SELECT b.kode, b.nama, b.keterangan, b.ikon, b.urutan, b.aktif, NOW(), NOW()
FROM (VALUES
    ('pmo',     'PMO',     'Pemesanan sparepart oleh toko', 'ShoppingCart', 1, true),
    ('picking', 'Picking', 'Belum dibangun',                'PackageCheck', 2, false)
) AS b (kode, nama, keterangan, ikon, urutan, aktif)
WHERE NOT EXISTS (
    SELECT 1 FROM pmov2.projects p WHERE p.kode = b.kode
);

COMMIT;


-- =============================================================
-- BAGIAN 3 - MENU PROJECT PMO
-- =============================================================

BEGIN;

INSERT INTO pmov2.menus (project_id, nama_menu, ikon, route, url, parent_id, urutan, status_aktif, khusus_it, created_at, updated_at)
SELECT p.id, b.nama_menu, b.ikon, b.route, b.url, NULL, b.urutan, true, false, NOW(), NOW()
FROM pmov2.projects p
CROSS JOIN (VALUES
    ('Dashboard',            'LayoutDashboard', 'pmo.dashboard',            '/pmo/dashboard',       1),
    ('Kelola Toko',          'Store',           'pmo.toko.index',           '/pmo/toko',            2),
    ('Sales Supervisor',     'UsersRound',      'pmo.sales-spv.index',      '/pmo/sales-spv',       3),
    ('Campaign',             'Megaphone',       'pmo.campaigns.index',      '/pmo/campaigns',       4),
    ('Katalog Motor',        'BookOpen',        'pmo.katalog.index',        '/pmo/katalog',         5),
    ('Gambar Kategori Part', 'Images',          'pmo.category-images.index','/pmo/category-images', 6),
    ('Part Populer',         'Star',            'pmo.popular-parts.index',  '/pmo/popular-parts',   7),
    ('Notifikasi',           'Bell',            'pmo.notifications.index',  '/pmo/notifications',   8)
) AS b (nama_menu, ikon, route, url, urutan)
WHERE p.kode = 'pmo'
  AND NOT EXISTS (
      SELECT 1 FROM pmov2.menus m WHERE m.route = b.route
  );

COMMIT;


-- =============================================================
-- BAGIAN 4 - MENU PENGATURAN (global, khusus IT)
-- project_id NULL supaya tampil di project mana pun.
-- khusus_it = true supaya tidak pernah muncul sebagai pilihan yang bisa
-- dicentangkan ke user biasa di halaman Kelola Hak Akses.
-- =============================================================

BEGIN;

INSERT INTO pmov2.menus (project_id, nama_menu, ikon, route, url, parent_id, urutan, status_aktif, khusus_it, created_at, updated_at)
SELECT NULL, 'Pengaturan', 'Settings', NULL, NULL, NULL, 99, true, true, NOW(), NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM pmov2.menus m WHERE m.nama_menu = 'Pengaturan' AND m.parent_id IS NULL
);

INSERT INTO pmov2.menus (project_id, nama_menu, ikon, route, url, parent_id, urutan, status_aktif, khusus_it, created_at, updated_at)
SELECT NULL, b.nama_menu, b.ikon, b.route, b.url, induk.id, b.urutan, true, true, NOW(), NOW()
FROM pmov2.menus induk
CROSS JOIN (VALUES
    ('Kelola Menu',      'List',        'pengaturan.menu.index',      '/pengaturan/menu',      1),
    ('Kelola Hak Akses', 'ShieldCheck', 'pengaturan.hak-akses.index', '/pengaturan/hak-akses', 2)
) AS b (nama_menu, ikon, route, url, urutan)
WHERE induk.nama_menu = 'Pengaturan'
  AND induk.parent_id IS NULL
  AND NOT EXISTS (
      SELECT 1 FROM pmov2.menus m WHERE m.route = b.route
  );

COMMIT;


-- =============================================================
-- BAGIAN 5 - VERIFIKASI
-- =============================================================

-- 5a. Daftar project
SELECT id, kode, nama, urutan, aktif FROM pmov2.projects ORDER BY urutan;

-- 5b. Susunan menu per project
SELECT COALESCE(p.kode, '(global)') AS project,
       CASE WHEN m.parent_id IS NULL THEN m.nama_menu ELSE '   > ' || m.nama_menu END AS menu,
       m.route, m.url, m.urutan, m.khusus_it
FROM pmov2.menus m
LEFT JOIN pmov2.projects p ON p.id = m.project_id
ORDER BY p.urutan NULLS LAST, COALESCE(m.parent_id, m.id), m.parent_id NULLS FIRST, m.urutan;

-- 5c. Menu yang bisa dicentangkan ke user biasa (khusus_it = false)
SELECT p.kode AS project, count(*) AS jumlah_menu
FROM pmov2.menus m
JOIN pmov2.projects p ON p.id = m.project_id
WHERE m.khusus_it = false
GROUP BY p.kode;

-- 5d. Isi hak akses per user (kosong di awal - IT tidak perlu didaftarkan)
SELECT ma.email, p.kode AS project, m.nama_menu
FROM pmov2.menu_akses ma
JOIN pmov2.menus m ON m.id = ma.menu_id
LEFT JOIN pmov2.projects p ON p.id = m.project_id
ORDER BY ma.email, p.urutan, m.urutan;
