-- =============================================================================
-- Perbaikan sequence H3 di dms_clone (development)
-- =============================================================================
--
-- MASALAH
-- Sequence `tbl_picking_inoma_id_seq` dan `kartustok_id_seq` tertinggal jauh di
-- belakang MAX(id) tabelnya, sehingga setiap INSERT baru langsung ditolak:
--
--     SQLSTATE[23505] duplicate key value violates unique constraint
--     "tbl_picking_inoma_pkey"  DETAIL: Key (id)=(616) already exists.
--
-- Keadaan saat ditemukan (2026-08-14):
--     tbl_picking_inoma   MAX(id) = 56.533   sequence.last_value = 616
--     kartustok           MAX(id) = 61.683   sequence.last_value = 2
--
-- PENYEBAB
-- Schema H3 di `dms_clone` adalah salinan beku: baris terbaru di
-- `tbl_picking_inoma` bertanggal 2026-02-14, sedangkan `data_part` berisi data
-- hari ini. Saat data disalin, sequence-nya tidak ikut disetel ulang.
--
-- Aplikasi Picking lama berjalan di production dan menulis ke `dmsv2`, bukan ke
-- salinan ini — itu sebabnya masalah ini tidak pernah muncul di sana.
--
-- ⚠️ SEBELUM CUTOVER: jalankan blok VERIFIKASI di bawah pada `dmsv2` juga.
--    Kalau di sana sequence-nya juga tertinggal, INSERT dari @new akan gagal
--    persis seperti di dev.
--
-- Berkas ini AMAN diulang: nilainya dihitung dari MAX(id) saat dijalankan.
-- `setval` tidak ikut ter-rollback, jadi jalankan saat tidak ada sync berjalan.
-- =============================================================================

BEGIN;

SELECT setval(
    '"H3".tbl_picking_inoma_id_seq',
    COALESCE((SELECT MAX(id) FROM "H3".tbl_picking_inoma), 0) + 1,
    false
);

SELECT setval(
    '"H3".kartustok_id_seq',
    COALESCE((SELECT MAX(id) FROM "H3".kartustok), 0) + 1,
    false
);

COMMIT;

-- =============================================================================
-- VERIFIKASI — semua baris harus bernilai AMAN
-- =============================================================================

SELECT
    'tbl_picking_inoma' AS tabel,
    (SELECT MAX(id) FROM "H3".tbl_picking_inoma) AS max_id,
    (SELECT last_value FROM "H3".tbl_picking_inoma_id_seq) AS sequence_berikutnya,
    CASE
        WHEN (SELECT last_value FROM "H3".tbl_picking_inoma_id_seq)
             > COALESCE((SELECT MAX(id) FROM "H3".tbl_picking_inoma), 0)
        THEN 'AMAN'
        ELSE 'MASIH TERTINGGAL'
    END AS status

UNION ALL

SELECT
    'kartustok',
    (SELECT MAX(id) FROM "H3".kartustok),
    (SELECT last_value FROM "H3".kartustok_id_seq),
    CASE
        WHEN (SELECT last_value FROM "H3".kartustok_id_seq)
             > COALESCE((SELECT MAX(id) FROM "H3".kartustok), 0)
        THEN 'AMAN'
        ELSE 'MASIH TERTINGGAL'
    END;

-- =============================================================================
-- Pemeriksaan menyeluruh: sequence lain di schema H3 yang juga tertinggal
-- =============================================================================

SELECT
    s.sequencename,
    s.last_value,
    'jalankan setval bila kolom id tabelnya sudah melewati nilai ini' AS catatan
FROM pg_sequences s
WHERE s.schemaname = 'H3'
ORDER BY s.sequencename;
