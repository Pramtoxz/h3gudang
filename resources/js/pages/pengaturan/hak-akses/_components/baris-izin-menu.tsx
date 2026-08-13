import { Checkbox } from '@/components/ui/checkbox';
import { type IzinAksi } from '@/types';

export const AKSI: { kunci: keyof IzinAksi; label: string }[] = [
    { kunci: 'lihat', label: 'Lihat' },
    { kunci: 'tambah', label: 'Tambah' },
    { kunci: 'ubah', label: 'Ubah' },
    { kunci: 'hapus', label: 'Hapus' },
];

export const TANPA_IZIN: IzinAksi = {
    lihat: false,
    tambah: false,
    ubah: false,
    hapus: false,
};

interface BarisIzinMenuProps {
    namaMenu: string;
    izin: IzinAksi;
    onUbah: (aksi: keyof IzinAksi) => void;
}

export function BarisIzinMenu({ namaMenu, izin, onUbah }: BarisIzinMenuProps) {
    return (
        <div className="grid grid-cols-[1fr_repeat(4,3.5rem)] items-center gap-1 border-b py-1.5 last:border-b-0">
            <span className={izin.lihat ? 'text-sm' : 'text-muted-foreground text-sm'}>
                {namaMenu}
            </span>

            {AKSI.map(({ kunci, label }) => (
                <label
                    key={kunci}
                    className="flex cursor-pointer items-center justify-center"
                    title={`${label} — ${namaMenu}`}
                >
                    <Checkbox
                        checked={izin[kunci]}
                        disabled={kunci !== 'lihat' && !izin.lihat}
                        onCheckedChange={() => onUbah(kunci)}
                    />
                </label>
            ))}
        </div>
    );
}

export function KepalaIzin() {
    return (
        <div className="text-muted-foreground grid grid-cols-[1fr_repeat(4,3.5rem)] items-center gap-1 border-b pb-1.5 text-[11px] font-medium">
            <span>Menu</span>
            {AKSI.map(({ kunci, label }) => (
                <span key={kunci} className="text-center">
                    {label}
                </span>
            ))}
        </div>
    );
}
