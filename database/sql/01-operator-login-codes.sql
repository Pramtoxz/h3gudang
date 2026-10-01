-- =============================================================================
-- Tabel & Baris Menu: Kode Akses Operator Lapangan
-- Database: tools | Schema: warehouse (koneksi pgsql bawaan)
-- =============================================================================
--
-- Dijalankan user di Navicat. Idempoten: aman dijalankan berulang kali.
--
-- 1. Membuat tabel `warehouse.operator_login_codes` untuk menyimpan kode OTP
--    login 6 digit operator lapangan (masa aktif 5 menit, one-time use).
-- 2. Menambahkan menu "Kode Akses Lapangan" ke project Picking (urutan 3).
-- =============================================================================

BEGIN;

-- 1. Buat tabel kode akses jika belum ada
CREATE TABLE IF NOT EXISTS warehouse.operator_login_codes (
    id BIGSERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    kode VARCHAR(10) NOT NULL,
    created_by_email VARCHAR(255) NOT NULL,
    expires_at TIMESTAMPTZ NOT NULL,
    used_at TIMESTAMPTZ NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_operator_login_codes_kode ON warehouse.operator_login_codes(kode);
CREATE INDEX IF NOT EXISTS idx_operator_login_codes_email ON warehouse.operator_login_codes(email);
CREATE INDEX IF NOT EXISTS idx_operator_login_codes_active ON warehouse.operator_login_codes(expires_at) WHERE used_at IS NULL;

-- 2. Daftarkan menu "Kode Akses Lapangan" ke project Picking
INSERT INTO warehouse.menus
    (project_id, nama_menu, ikon, route, url, parent_id, urutan, status_aktif, khusus_it, created_at, updated_at)
SELECT
    p.id,
    'Kode Akses Lapangan',
    'KeyRound',
    'picking.kode-akses.index',
    '/picking/kode-akses',
    NULL,
    3,
    TRUE,
    FALSE,
    NOW(),
    NOW()
FROM warehouse.projects p
WHERE p.kode = 'picking'
  AND NOT EXISTS (
      SELECT 1 FROM warehouse.menus m WHERE m.route = 'picking.kode-akses.index'
  );

-- Geser urutan menu lain di bawahnya jika diperlukan (Final Check tetap urutan 2, Master dsb mengikuti)
-- Verifikasi:
SELECT m.id, m.nama_menu, m.route, m.urutan, m.status_aktif
FROM warehouse.menus m
WHERE m.route = 'picking.kode-akses.index';

SELECT table_name, column_name, data_type 
FROM information_schema.columns 
WHERE table_schema = 'warehouse' AND table_name = 'operator_login_codes'
ORDER BY ordinal_position;

COMMIT;
