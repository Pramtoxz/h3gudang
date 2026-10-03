-- =============================================================================
-- Baris menu "Storing Part" untuk project Picking
-- =============================================================================
--
-- Dijalankan user di Navicat. Idempoten: dikunci pada kolom `route`, jadi aman
-- dijalankan berulang kali.
--
-- Setelah baris ini ada, hak aksesnya diberikan per user lewat halaman
-- Pengaturan > Kelola Hak Akses. Pengelola IT (`it = 't'`) otomatis melihatnya
-- tanpa perlu diberi akses.
--
-- Urutan 3 menempatkannya di bawah Picking Part (urutan 1) dan Final Check (urutan 2).
-- =============================================================================

BEGIN;

INSERT INTO warehouse.menus
    (project_id, nama_menu, ikon, route, url, parent_id, urutan, status_aktif, khusus_it, created_at, updated_at)
SELECT
    p.id,
    'Storing Part',
    'PackagePlus',
    'picking.storing-part.index',
    '/picking/storing-part',
    NULL,
    3,
    TRUE,
    FALSE,
    NOW(),
    NOW()
FROM warehouse.projects p
WHERE p.kode = 'picking'
  AND NOT EXISTS (
      SELECT 1 FROM warehouse.menus m WHERE m.route = 'picking.storing-part.index'
  );

-- Verifikasi: harus mengembalikan tepat satu baris.
SELECT m.id, m.nama_menu, m.route, m.urutan, m.status_aktif
FROM warehouse.menus m
WHERE m.route = 'picking.storing-part.index';

COMMIT;
