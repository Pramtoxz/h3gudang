export interface BarisStoring {
    fk_do: string;
    tgl_kartu: string | null;
    fk_gudang: string | null;
    nm_gudang_part: string;
    total_items: number;
    done_items: number;
    total_qty_diterima: number;
    total_qty_masuk: number;
    status_storing: 'Waiting' | 'Done';
}

export interface BarisItemStoring {
    id: number;
    fk_do: string;
    no_part: string;
    nm_part: string;
    kode_rak: string;
    area_rak: string;
    no_doos: string | null;
    qty_diterima: number;
    qty_masuk: number | null;
    status_masuk: boolean;
    fk_gudang: string | null;
    nm_gudang_part: string;
    tgl_kartu: string | null;
    waktu_done: string | null;
}

export interface DokumenStoring {
    fk_do: string;
    fk_gudang: string | null;
    nm_gudang_part: string | null;
    tgl_kartu: string | null;
    total_items: number;
    done_items: number;
}

export interface SaringStoring {
    area: string | null;
    gudang: string | null;
    status: string | null;
    tgl_dari: string | null;
    tgl_sampai: string | null;
    cari: string | null;
}

export interface GudangItem {
    kd_gudang_part: string;
    nm_gudang_part: string;
}

export const STATUS_BAWAAN = 'default';
