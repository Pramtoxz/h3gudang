import { type IzinAksi, type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';

const TANPA_IZIN: IzinAksi = {
    lihat: false,
    tambah: false,
    ubah: false,
    hapus: false,
};

/**
 * Izin per aksi untuk modul yang sedang dibuka, dikirim server lewat ShareMenus.
 * Dipakai untuk menyembunyikan tombol — bukan sebagai pengaman. Yang menahan
 * permintaan tetap middleware CheckMenuAccess di server.
 */
export function useIzin(): IzinAksi {
    const { izin } = usePage<SharedData>().props;

    return izin ?? TANPA_IZIN;
}
