export const LEVEL_ADMIN = 1;
export const LEVEL_PIC = 2;

export interface BarisAksesArea {
    email: string;
    username: string | null;
    area: string | null;
    level: number | null;
    nama_dms: string | null;
    ada_di_dms: boolean;
}

export interface PilihanUser {
    nama: string;
    email: string;
}
