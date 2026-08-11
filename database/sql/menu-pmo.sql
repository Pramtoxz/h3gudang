-- =============================================================
-- Menu dinamis PMO - dijalankan manual di Navicat Premium
-- Target: database `tools`, schema `pmov2`
-- Aman dijalankan berulang (idempoten)
-- =============================================================

BEGIN;

CREATE TABLE IF NOT EXISTS pmov2.menus (
    id            bigserial    PRIMARY KEY,
    nama_menu     varchar(255) NOT NULL,
    ikon          varchar(255) NULL,
    route         varchar(255) NULL,
    url           varchar(255) NULL,
    parent_id     bigint       NULL REFERENCES pmov2.menus (id) ON DELETE CASCADE,
    urutan        integer      NOT NULL DEFAULT 0,
    status_aktif  boolean      NOT NULL DEFAULT true,
    created_at    timestamp(0) NULL,
    updated_at    timestamp(0) NULL
);

CREATE TABLE IF NOT EXISTS pmov2.menu_role (
    id          bigserial    PRIMARY KEY,
    menu_id     bigint       NOT NULL REFERENCES pmov2.menus (id) ON DELETE CASCADE,
    role        varchar(10)  NOT NULL,
    created_at  timestamp(0) NULL,
    updated_at  timestamp(0) NULL,
    CONSTRAINT menu_role_menu_id_role_unique UNIQUE (menu_id, role)
);

-- -------------------------------------------------------------
-- Menu utama
-- -------------------------------------------------------------

INSERT INTO pmov2.menus (nama_menu, ikon, route, url, parent_id, urutan, status_aktif, created_at, updated_at)
SELECT b.nama_menu, b.ikon, b.route, b.url, NULL, b.urutan, true, NOW(), NOW()
FROM (VALUES
    ('Dashboard',            'LayoutDashboard', 'admin.dashboard',            '/admin/dashboard',       1),
    ('Kelola Toko',          'Store',           'admin.toko.index',           '/admin/toko',            2),
    ('Sales Supervisor',     'UsersRound',      'admin.sales-spv.index',      '/admin/sales-spv',       3),
    ('Campaign',             'Megaphone',       'admin.campaigns.index',      '/admin/campaigns',       4),
    ('Katalog Motor',        'BookOpen',        'admin.katalog.index',        '/admin/katalog',         5),
    ('Gambar Kategori Part', 'Images',          'admin.category-images.index','/admin/category-images', 6),
    ('Part Populer',         'Star',            'admin.popular-parts.index',  '/admin/popular-parts',   7),
    ('Notifikasi',           'Bell',            'admin.notifications.index',  '/admin/notifications',   8)
) AS b (nama_menu, ikon, route, url, urutan)
WHERE NOT EXISTS (
    SELECT 1 FROM pmov2.menus m WHERE m.route = b.route
);

-- -------------------------------------------------------------
-- Menu induk Pengaturan + anaknya
-- -------------------------------------------------------------

INSERT INTO pmov2.menus (nama_menu, ikon, route, url, parent_id, urutan, status_aktif, created_at, updated_at)
SELECT 'Pengaturan', 'Settings', NULL, NULL, NULL, 9, true, NOW(), NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM pmov2.menus m WHERE m.nama_menu = 'Pengaturan' AND m.parent_id IS NULL
);

INSERT INTO pmov2.menus (nama_menu, ikon, route, url, parent_id, urutan, status_aktif, created_at, updated_at)
SELECT 'Kelola Menu', 'List', 'settings.menus.index', '/settings/menus', p.id, 1, true, NOW(), NOW()
FROM pmov2.menus p
WHERE p.nama_menu = 'Pengaturan'
  AND p.parent_id IS NULL
  AND NOT EXISTS (
      SELECT 1 FROM pmov2.menus m WHERE m.route = 'settings.menus.index'
  );

-- -------------------------------------------------------------
-- Hak akses: seluruh menu untuk role IT
-- (AdminUser::getRoles() mengembalikan ['IT'] dari kolom `it` di DMS)
-- -------------------------------------------------------------

INSERT INTO pmov2.menu_role (menu_id, role, created_at, updated_at)
SELECT m.id, 'IT', NOW(), NOW()
FROM pmov2.menus m
WHERE NOT EXISTS (
    SELECT 1 FROM pmov2.menu_role mr WHERE mr.menu_id = m.id AND mr.role = 'IT'
);

COMMIT;

-- -------------------------------------------------------------
-- Verifikasi hasil
-- -------------------------------------------------------------
-- SELECT m.id, m.parent_id, m.urutan, m.nama_menu, m.route, m.url, mr.role
-- FROM pmov2.menus m
-- LEFT JOIN pmov2.menu_role mr ON mr.menu_id = m.id
-- ORDER BY COALESCE(m.parent_id, m.id), m.parent_id NULLS FIRST, m.urutan;
