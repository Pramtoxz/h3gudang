-- =============================================================
-- Pembersihan akun sales/supervisor di pmov2.users
-- Dijalankan manual di Navicat. Target: database `tools`, schema `pmov2`
--
-- Prinsip: sales & supervisor HANYA hidup di tabel pmov2.sales_supervisor.
-- Relasi toko -> sales berjalan lewat tbltoko.fk_sales / fk_spv -> kode_npk.
-- Akun di pmov2.users tidak berperan apa pun dalam relasi itu.
--
-- Akun bermasalah terbagi tiga kelompok:
--   A. 19 akun  -> sales/spv resmi (nama ada di master). Seharusnya tidak punya akun. HAPUS.
--   B. 18 akun  -> user toko yang role-nya salah tertulis sales/supervisor. UBAH jadi dealer.
--   C.  2 akun  -> tidak cocok ke mana pun. TINJAU MANUAL, tidak disentuh skrip ini.
--
-- Penyebab: di @old, import Toko dan import Sales SPV sama-sama updateOrInsert
-- ke pmov2.users berdasarkan email. Import Sales SPV menimpa kolom `role`
-- tanpa menyentuh `fk_toko`, sehingga akun toko berubah role jadi 'sales'.
-- Di @new penyebabnya sudah ditutup: import Sales SPV tidak menyentuh users.
--
-- URUTAN PAKAI: jalankan BAGIAN 1 dulu, tinjau hasilnya, baru lanjut.
-- =============================================================


-- =============================================================
-- BAGIAN 1 - AUDIT (hanya SELECT, aman dijalankan kapan saja)
-- =============================================================

-- 1a. Kelompok A: akun sales/spv resmi yang akan DIHAPUS
SELECT u.id, u.name, u.email, u.role, u.fk_toko,
       (u.collection_pin IS NOT NULL AND u.collection_pin <> '') AS punya_pin_aktif
FROM pmov2.users u
WHERE u.role IN ('sales', 'supervisor')
  AND EXISTS (SELECT 1 FROM pmov2.sales_supervisor ss WHERE upper(ss.nama) = upper(u.name))
ORDER BY u.name;

-- 1b. Kelompok B: user toko yang role-nya akan DIBETULKAN jadi 'dealer'
SELECT u.id, u.name, u.email, u.role, u.fk_toko, t.toko AS nama_toko,
       (u.collection_pin IS NOT NULL AND u.collection_pin <> '') AS punya_pin_aktif
FROM pmov2.users u
JOIN pmov2.tbltoko t ON t.kd_toko = u.fk_toko
WHERE u.role IN ('sales', 'supervisor')
  AND NOT EXISTS (SELECT 1 FROM pmov2.sales_supervisor ss WHERE upper(ss.nama) = upper(u.name))
ORDER BY t.toko;

-- 1c. Kelompok C: perlu ditinjau manual, TIDAK disentuh skrip ini
SELECT u.id, u.name, u.email, u.role, u.fk_toko
FROM pmov2.users u
WHERE u.role IN ('sales', 'supervisor')
  AND NOT EXISTS (SELECT 1 FROM pmov2.sales_supervisor ss WHERE upper(ss.nama) = upper(u.name))
  AND NOT EXISTS (SELECT 1 FROM pmov2.tbltoko t WHERE t.kd_toko = u.fk_toko);

-- 1d. Data yang ikut terhapus bersama kelompok A
--     keranjang punya ON DELETE CASCADE (item_keranjang ikut otomatis),
--     personal_access_tokens TIDAK punya foreign key sehingga dihapus manual di BAGIAN 4.
SELECT 'keranjang' AS tabel, count(*) AS jumlah
FROM pmov2.keranjang k
WHERE k.user_id IN (
    SELECT u.id FROM pmov2.users u
    WHERE u.role IN ('sales', 'supervisor')
      AND EXISTS (SELECT 1 FROM pmov2.sales_supervisor ss WHERE upper(ss.nama) = upper(u.name))
)
UNION ALL
SELECT 'personal_access_tokens', count(*)
FROM pmov2.personal_access_tokens p
WHERE p.tokenable_id IN (
    SELECT u.id FROM pmov2.users u
    WHERE u.role IN ('sales', 'supervisor')
      AND EXISTS (SELECT 1 FROM pmov2.sales_supervisor ss WHERE upper(ss.nama) = upper(u.name))
);

-- 1e. Relasi toko yang menunjuk NPK tidak terdaftar di master
SELECT t.kd_toko, t.toko, t.fk_sales, t.fk_spv
FROM pmov2.tbltoko t
WHERE (t.fk_sales IS NOT NULL AND t.fk_sales <> ''
       AND NOT EXISTS (SELECT 1 FROM pmov2.sales_supervisor ss WHERE ss.kode_npk = t.fk_sales))
   OR (t.fk_spv IS NOT NULL AND t.fk_spv <> ''
       AND NOT EXISTS (SELECT 1 FROM pmov2.sales_supervisor ss WHERE ss.kode_npk = t.fk_spv));


-- =============================================================
-- BAGIAN 2 - BETULKAN ROLE USER TOKO (kelompok B, 18 akun)
-- Aman dan reversibel. Tidak menghapus data apa pun.
-- =============================================================

BEGIN;

UPDATE pmov2.users u
SET role = 'dealer',
    updated_at = NOW()
WHERE u.role IN ('sales', 'supervisor')
  AND NOT EXISTS (SELECT 1 FROM pmov2.sales_supervisor ss WHERE upper(ss.nama) = upper(u.name))
  AND EXISTS (SELECT 1 FROM pmov2.tbltoko t WHERE t.kd_toko = u.fk_toko);

-- Harus melaporkan 18 baris. Kalau angkanya jauh berbeda, ROLLBACK dan periksa lagi.
COMMIT;


-- =============================================================
-- BAGIAN 3 - BETULKAN RELASI TOKO YANG NYASAR
-- MTO1 (NUSANTARA OLI) menunjuk fk_sales 'DIV', sedangkan master berisi 'DIVA'.
-- Jalankan HANYA kalau BAGIAN 1e memang menunjukkan baris itu dan Anda setuju.
-- =============================================================

BEGIN;

UPDATE pmov2.tbltoko
SET fk_sales = 'DIVA'
WHERE kd_toko = 'MTO1'
  AND fk_sales = 'DIV';

COMMIT;


-- =============================================================
-- BAGIAN 4 - HAPUS AKUN SALES/SPV RESMI (kelompok A, 19 akun)
--
-- ⚠️ TIDAK BISA DIBATALKAN setelah COMMIT.
-- ⚠️ Periksa hasil BAGIAN 1a dulu: ada akun yang punya PIN aktif, artinya
--    orangnya benar-benar pernah memakai aplikasi mobile. Menghapus akunnya
--    membuat dia tidak bisa login lagi.
-- ⚠️ Keranjang miliknya ikut terhapus otomatis (ON DELETE CASCADE).
-- =============================================================

BEGIN;

CREATE TEMP TABLE akun_sales_dihapus AS
SELECT u.id
FROM pmov2.users u
WHERE u.role IN ('sales', 'supervisor')
  AND EXISTS (SELECT 1 FROM pmov2.sales_supervisor ss WHERE upper(ss.nama) = upper(u.name));

DELETE FROM pmov2.personal_access_tokens
WHERE tokenable_id IN (SELECT id FROM akun_sales_dihapus);

DELETE FROM pmov2.users
WHERE id IN (SELECT id FROM akun_sales_dihapus);

DROP TABLE akun_sales_dihapus;

-- Periksa jumlah baris yang terhapus sebelum melanjutkan.
-- Kalau sesuai harapan: COMMIT. Kalau tidak: ROLLBACK.
COMMIT;


-- =============================================================
-- BAGIAN 5 - VERIFIKASI SETELAH PEMBERSIHAN
-- =============================================================

-- 5a. Tidak boleh ada lagi akun sales/spv yang namanya ada di master
SELECT count(*) AS sisa_akun_sales_resmi
FROM pmov2.users u
WHERE u.role IN ('sales', 'supervisor')
  AND EXISTS (SELECT 1 FROM pmov2.sales_supervisor ss WHERE upper(ss.nama) = upper(u.name));

-- 5b. Sebaran role setelah pembersihan
SELECT role, count(*) AS jumlah,
       count(*) FILTER (WHERE collection_pin IS NOT NULL AND collection_pin <> '') AS punya_pin
FROM pmov2.users
GROUP BY role
ORDER BY jumlah DESC;

-- 5c. Setiap toko tetap punya minimal satu akun
SELECT count(*) AS toko_tanpa_akun
FROM pmov2.tbltoko t
WHERE NOT EXISTS (SELECT 1 FROM pmov2.users u WHERE u.fk_toko = t.kd_toko);

-- 5d. Relasi toko -> master sales sudah bersih
SELECT count(*) AS relasi_nyasar
FROM pmov2.tbltoko t
WHERE (t.fk_sales IS NOT NULL AND t.fk_sales <> ''
       AND NOT EXISTS (SELECT 1 FROM pmov2.sales_supervisor ss WHERE ss.kode_npk = t.fk_sales))
   OR (t.fk_spv IS NOT NULL AND t.fk_spv <> ''
       AND NOT EXISTS (SELECT 1 FROM pmov2.sales_supervisor ss WHERE ss.kode_npk = t.fk_spv));
