export interface BarisDoFinal {
    fk_do: string;
    tgl_picking_list_part: string | null;
    no_picking_list_part: string | null;
    nama_channel: string;
    fk_dealer: string | null;
    area: string | null;
    poli: number;
    tgl_final: string | null;
    total_items: number;
    final_parts: number;
    status_do: 'Done' | 'Final';
    is_bundling: boolean;
}

export interface BarisPartFinal {
    id: number;
    fk_part: string;
    nm_part: string;
    lokasi_part: string;
    qty_part: number;
    qty_picking: number;
    nomor_kotak: string | null;
    user_cek: string | null;
    tgl_final: string | null;
    status_picking_list: string;
}

export interface InfoDoFinal {
    nama_channel: string;
    fk_dealer: string | null;
    area: string;
    tgl_picking_list_part: string | null;
    keterangan_picking: string;
    keterangan_final: string | null;
    poli: number;
}

export interface SaringFinalCheck {
    area: string | null;
    status: string | null;
    tgl_dari: string | null;
    tgl_sampai: string | null;
    cari: string | null;
}

export const STATUS_BAWAAN = 'Done';

export const SEMUA_AREA = 'semua';
