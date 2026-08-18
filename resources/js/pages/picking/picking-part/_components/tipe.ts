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

export interface SaringDo {
    area: string | null;
    status: string | null;
    tgl_dari: string | null;
    tgl_sampai: string | null;
    cari: string | null;
}

export const STATUS_BAWAAN = 'default';
