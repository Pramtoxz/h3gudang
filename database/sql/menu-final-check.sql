-- =============================================================================
-- Baris menu "Final Check" untuk project Picking
-- =============================================================================
--
-- Dijalankan user di Navicat. Idempoten: dikunci pada kolom `route`, jadi aman
-- dijalankan berulang kali.
--
-- Setelah baris ini ada, hak aksesnya diberikan per user lewat halaman
-- Pengaturan > Kelola Hak Akses. Pengelola IT (`it = 't'`) otomatis melihatnya
-- tanpa perlu diberi akses.
--
-- Urutan 2 menempatkannya tepat di bawah Picking Part (urutan 1), karena alur
-- kerjanya memang berurutan: operator mengambil part, lalu petugas final check
-- memeriksa dan menutup DO-nya.
-- =============================================================================

BEGIN;

INSERT INTO warehouse.menus
    (project_id, nama_menu, ikon, route, url, parent_id, urutan, status_aktif, khusus_it, created_at, updated_at)
SELECT
    p.id,
    'Final Check',
    'ClipboardCheck',
    'picking.final-check.index',
    '/picking/final-check',
    NULL,
    2,
    TRUE,
    FALSE,
    NOW(),
    NOW()
FROM warehouse.projects p
WHERE p.kode = 'picking'
  AND NOT EXISTS (
      SELECT 1 FROM warehouse.menus m WHERE m.route = 'picking.final-check.index'
  );

-- Verifikasi: harus mengembalikan tepat satu baris.
SELECT m.id, m.nama_menu, m.route, m.urutan, m.status_aktif
FROM warehouse.menus m
WHERE m.route = 'picking.final-check.index';

COMMIT;
