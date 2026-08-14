export interface BarisLokasiRak {
    kd_lokasi: string;
    fk_gudang_part: string;
    jenis_lokasi: string | null;
    kapasitas: string | null;
    lokasi_part_active: boolean;
}

export interface BarisRingkasanArea {
    area_rak: string;
    nama_gudang: string;
    kode_gudang: string[];
    total_lokasi: number;
    total_aktif: number;
}

export interface PilihanGudang {
    kode: string;
    nama: string;
    aktif: boolean;
}
