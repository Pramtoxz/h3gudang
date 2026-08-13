import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Search } from 'lucide-react';
import { useMemo, useState } from 'react';

export interface UserDms {
    nama: string;
    email: string;
    is_it: boolean;
}

interface DaftarUserProps {
    daftarUser: UserDms[];
    jumlahMenu: Record<string, number>;
    emailTerpilih: string | null;
    onPilih: (email: string) => void;
}

const BATAS_TAMPIL = 50;

export function DaftarUser({ daftarUser, jumlahMenu, emailTerpilih, onPilih }: DaftarUserProps) {
    const [pencarian, setPencarian] = useState('');

    const hasil = useMemo(() => {
        const kunci = pencarian.trim().toLowerCase();

        if (kunci === '') {
            return daftarUser.filter((user) => (jumlahMenu[user.email] ?? 0) > 0 || user.is_it);
        }

        return daftarUser.filter(
            (user) =>
                user.nama.toLowerCase().includes(kunci) || user.email.toLowerCase().includes(kunci),
        );
    }, [daftarUser, jumlahMenu, pencarian]);

    const ditampilkan = hasil.slice(0, BATAS_TAMPIL);

    return (
        <div className="flex h-full flex-col gap-3">
            <div className="relative">
                <Search className="text-muted-foreground absolute top-2.5 left-2.5 h-4 w-4" />
                <Input
                    value={pencarian}
                    onChange={(event) => setPencarian(event.target.value)}
                    placeholder="Cari nama atau email"
                    className="pl-8"
                />
            </div>

            <p className="text-muted-foreground text-xs">
                {pencarian.trim() === ''
                    ? 'Menampilkan user yang sudah punya akses. Ketik untuk mencari user lain.'
                    : `${hasil.length} hasil${hasil.length > BATAS_TAMPIL ? `, ditampilkan ${BATAS_TAMPIL} teratas` : ''}`}
            </p>

            <div className="divide-y overflow-y-auto rounded-md border">
                {ditampilkan.length === 0 ? (
                    <p className="text-muted-foreground p-4 text-center text-sm">
                        Tidak ada user yang cocok.
                    </p>
                ) : (
                    ditampilkan.map((user) => {
                        const jumlah = jumlahMenu[user.email] ?? 0;
                        const terpilih = user.email === emailTerpilih;

                        return (
                            <button
                                key={user.email}
                                type="button"
                                onClick={() => onPilih(user.email)}
                                className={`hover:bg-muted flex w-full items-center justify-between gap-2 px-3 py-2 text-left transition-colors ${
                                    terpilih ? 'bg-muted' : ''
                                }`}
                            >
                                <span className="min-w-0">
                                    <span className="block truncate text-sm font-medium">
                                        {user.nama}
                                    </span>
                                    <span className="text-muted-foreground block truncate text-xs">
                                        {user.email}
                                    </span>
                                </span>
                                <span className="flex shrink-0 items-center gap-1">
                                    {user.is_it && (
                                        <Badge variant="secondary" className="text-[10px]">
                                            IT
                                        </Badge>
                                    )}
                                    {jumlah > 0 && (
                                        <Badge variant="outline" className="text-[10px]">
                                            {jumlah}
                                        </Badge>
                                    )}
                                </span>
                            </button>
                        );
                    })
                )}
            </div>
        </div>
    );
}
