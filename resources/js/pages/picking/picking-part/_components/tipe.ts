export interface BarisDo {
    fk_do: string;
    tgl_picking_list_part: string | null;
    no_picking_list_part: string | null;
    nama_channel: string;
    fk_dealer: string | null;
    area: string | null;
    total_items: number;
    total_picking: number;
    done_parts: number;
    status_do: 'Waiting' | 'On Progress' | 'Done';
    is_bundling: boolean;
}

export interface BarisPart {
    id: number;
    fk_do: string;
    tgl_picking_list_part: string | null;
    fk_part: string;
    nm_part: string;
    lokasi_part: string;
    keterangan_picking: string;
    nama_channel: string;
    area: string;
    qty_part: number;
    qty_picking: number;
    status_picking_list: string;
    waktu_done: string | null;
    fk_dealer: string | null;
}

export interface SaringDo {
    area: string | null;
    status: string | null;
    tgl_dari: string | null;
    tgl_sampai: string | null;
    cari: string | null;
}

export const STATUS_BAWAAN = 'default';
