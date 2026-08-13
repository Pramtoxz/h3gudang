-- =============================================================
-- ⚠️ TIDAK PERLU DIJALANKAN UNTUK SCHEMA YANG BARU DISALIN.
--
-- `00-SETUP-PMOV2.sql` sudah membuat `menu_akses` lengkap dengan keempat kolom
-- izin sejak awal. Berkas ini hanya berguna untuk database lain yang tabel
-- `menu_akses`-nya sudah terlanjur ada tanpa kolom izin.
-- =============================================================

-- =============================================================
-- Izin per aksi pada hak akses menu
-- Dijalankan manual di Navicat. Target: database `tools`, schema `pmov2`
--
-- Sebelum ini, satu baris di `menu_akses` berarti akses penuh: user yang boleh
-- membuka sebuah modul otomatis boleh menambah, mengubah, dan menghapus di
-- dalamnya. Keputusan user 2026-08-13: IT harus bisa menentukan per aksi.
--
-- Penjelasan lengkap: md/07-MULTIPROJECT-DAN-HAK-AKSES.md §2.1
--
-- Aman dijalankan berulang (idempoten).
-- =============================================================


-- =============================================================
-- BAGIAN 1 - TAMBAH KOLOM
--
-- `boleh_lihat` bawaannya true karena baris ini memang dibuat untuk memberi
-- akses. Tiga sisanya bawaannya false supaya izin menulis harus diberikan
-- secara sadar, bukan didapat diam-diam.
--
-- `boleh_lihat = false` sama artinya dengan tidak punya akses: menunya hilang
-- dari sidebar dan seluruh route modulnya ditolak.
-- =============================================================

BEGIN;

ALTER TABLE pmov2.menu_akses
    ADD COLUMN IF NOT EXISTS boleh_lihat  boolean NOT NULL DEFAULT true,
    ADD COLUMN IF NOT EXISTS boleh_tambah boolean NOT NULL DEFAULT false,
    ADD COLUMN IF NOT EXISTS boleh_ubah   boolean NOT NULL DEFAULT false,
    ADD COLUMN IF NOT EXISTS boleh_hapus  boolean NOT NULL DEFAULT false;

COMMIT;


-- =============================================================
-- BAGIAN 2 - PENGISIAN BARIS LAMA
--
-- JANGAN DIJALANKAN DI DATABASE `tools` SEKARANG.
-- Per 2026-08-13 tabel `menu_akses` di sana masih KOSONG (0 baris), jadi tidak
-- ada yang perlu diisi.
--
-- Blok ini disediakan untuk saat skema ini dibawa ke database yang tabelnya
-- SUDAH berisi baris buatan sistem lama. Di sistem lama, keberadaan baris
-- berarti akses penuh - jadi supaya tidak ada user yang mendadak kehilangan
-- kemampuannya, baris lama diisi true keempatnya.
--
-- Hati-hati: kalau dijalankan setelah IT mulai mengatur izin, seluruh
-- pengaturan itu akan tertimpa jadi akses penuh untuk semua orang.
-- =============================================================

-- BEGIN;
--
-- UPDATE pmov2.menu_akses
--    SET boleh_lihat  = true,
--        boleh_tambah = true,
--        boleh_ubah   = true,
--        boleh_hapus  = true,
--        updated_at   = NOW();
--
-- COMMIT;


-- =============================================================
-- BAGIAN 3 - VERIFIKASI
-- =============================================================

-- 3a. Struktur kolom sudah bertambah empat
SELECT column_name, data_type, is_nullable, column_default
FROM information_schema.columns
WHERE table_schema = 'pmov2'
  AND table_name = 'menu_akses'
ORDER BY ordinal_position;

-- 3b. Isi hak akses per user beserta izin tiap aksi
SELECT ma.email,
       COALESCE(p.kode, '(global)') AS project,
       m.nama_menu,
       ma.boleh_lihat  AS lihat,
       ma.boleh_tambah AS tambah,
       ma.boleh_ubah   AS ubah,
       ma.boleh_hapus  AS hapus
FROM pmov2.menu_akses ma
JOIN pmov2.menus m ON m.id = ma.menu_id
LEFT JOIN pmov2.projects p ON p.id = m.project_id
ORDER BY ma.email, p.urutan NULLS LAST, m.urutan;

-- 3c. Ringkasan: berapa user yang punya izin menulis
SELECT count(*) FILTER (WHERE boleh_lihat)  AS bisa_lihat,
       count(*) FILTER (WHERE boleh_tambah) AS bisa_tambah,
       count(*) FILTER (WHERE boleh_ubah)   AS bisa_ubah,
       count(*) FILTER (WHERE boleh_hapus)  AS bisa_hapus,
       count(*)                             AS total_baris
FROM pmov2.menu_akses;
