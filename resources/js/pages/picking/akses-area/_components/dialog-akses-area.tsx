import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { store, update } from '@/routes/picking/akses-area';
import { useForm } from '@inertiajs/react';
import { Loader2 } from 'lucide-react';
import { useEffect } from 'react';
import { LEVEL_ADMIN, LEVEL_PIC, type BarisAksesArea, type PilihanUser } from './tipe';

interface DialogAksesAreaProps {
    terbuka: boolean;
    akses: BarisAksesArea | null;
    daftarUser: PilihanUser[];
    daftarArea: string[];
    onTutup: () => void;
}

export function DialogAksesArea({
    terbuka,
    akses,
    daftarUser,
    daftarArea,
    onTutup,
}: DialogAksesAreaProps) {
    const { data, setData, post, put, processing, errors, reset, clearErrors } = useForm({
        email: '',
        level: String(LEVEL_PIC),
        area: '',
    });

    useEffect(() => {
        if (!terbuka) return;

        clearErrors();
        setData({
            email: akses?.email ?? '',
            level: String(akses?.level ?? LEVEL_PIC),
            area: akses?.area ?? '',
        });
    }, [terbuka, akses]);

    const gantiLevel = (nilai: string) => {
        setData((sebelumnya) => ({
            ...sebelumnya,
            level: nilai,
            area: nilai === String(LEVEL_ADMIN) ? '' : sebelumnya.area,
        }));
    };

    const simpan = () => {
        const opsi = {
            onSuccess: () => {
                reset();
                onTutup();
            },
            preserveScroll: true,
        };

        if (akses) {
            put(update(akses.email).url, opsi);

            return;
        }

        post(store().url, opsi);
    };

    const levelAdmin = data.level === String(LEVEL_ADMIN);

    return (
        <Dialog open={terbuka} onOpenChange={(status) => !status && onTutup()}>
            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>{akses ? 'Ubah Akses Area' : 'Tambah Akses Area'}</DialogTitle>
                    <DialogDescription>
                        Menentukan baris mana yang boleh dilihat operator di layar picking. Hak
                        akses menu diatur terpisah di Pengaturan.
                    </DialogDescription>
                </DialogHeader>

                <div className="grid gap-3">
                    <div className="space-y-1">
                        <Label htmlFor="email">
                            Pengguna <span className="text-destructive">*</span>
                        </Label>

                        {akses ? (
                            <Input id="email" value={akses.email} disabled />
                        ) : (
                            <Select
                                value={data.email}
                                onValueChange={(nilai) => setData('email', nilai)}
                            >
                                <SelectTrigger id="email" className="w-full">
                                    <SelectValue placeholder="Pilih pengguna" />
                                </SelectTrigger>
                                <SelectContent>
                                    {daftarUser.map((user) => (
                                        <SelectItem key={user.email} value={user.email}>
                                            {user.nama} — {user.email}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        )}

                        {akses === null && (
                            <p
                                className={
                                    daftarUser.length === 0
                                        ? 'text-destructive text-xs'
                                        : 'text-muted-foreground text-xs'
                                }
                            >
                                {daftarUser.length === 0
                                    ? 'Belum ada pengguna yang bisa dipilih. IT perlu memberi hak akses menu lebih dulu di Pengaturan → Kelola Hak Akses.'
                                    : 'Hanya pengguna yang sudah diberi hak akses menu oleh IT di Pengaturan yang bisa dipilih.'}
                            </p>
                        )}
                        {errors.email && <p className="text-destructive text-xs">{errors.email}</p>}
                    </div>

                    <div className="grid gap-3 sm:grid-cols-2">
                        <div className="space-y-1">
                            <Label htmlFor="level">
                                Level Akses <span className="text-destructive">*</span>
                            </Label>
                            <Select value={data.level} onValueChange={gantiLevel}>
                                <SelectTrigger id="level" className="w-full">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value={String(LEVEL_ADMIN)}>
                                        Admin — semua area
                                    </SelectItem>
                                    <SelectItem value={String(LEVEL_PIC)}>
                                        PIC — satu area
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            {errors.level && (
                                <p className="text-destructive text-xs">{errors.level}</p>
                            )}
                        </div>

                        <div className="space-y-1">
                            <Label htmlFor="area">
                                Area Rak {!levelAdmin && <span className="text-destructive">*</span>}
                            </Label>
                            <Select
                                value={data.area}
                                disabled={levelAdmin}
                                onValueChange={(nilai) => setData('area', nilai)}
                            >
                                <SelectTrigger id="area" className="w-full">
                                    <SelectValue
                                        placeholder={levelAdmin ? 'Semua area' : 'Pilih area'}
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    {daftarArea.map((nama) => (
                                        <SelectItem key={nama} value={nama}>
                                            {nama}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.area && <p className="text-destructive text-xs">{errors.area}</p>}
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" onClick={onTutup} disabled={processing}>
                        Batal
                    </Button>
                    <Button
                        onClick={simpan}
                        disabled={processing || (akses === null && daftarUser.length === 0)}
                    >
                        {processing && <Loader2 className="mr-1 h-4 w-4 animate-spin" />}
                        Simpan
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
