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
import { Switch } from '@/components/ui/switch';
import { store, update } from '@/routes/picking/lokasi-rak';
import { useForm } from '@inertiajs/react';
import { Loader2 } from 'lucide-react';
import { useEffect } from 'react';
import type { BarisLokasiRak, PilihanGudang } from './tipe';

interface DialogLokasiRakProps {
    terbuka: boolean;
    lokasi: BarisLokasiRak | null;
    daftarGudang: PilihanGudang[];
    daftarJenisLokasi: string[];
    onSukses: () => void;
    onTutup: () => void;
}

export function DialogLokasiRak({
    terbuka,
    lokasi,
    daftarGudang,
    daftarJenisLokasi,
    onSukses,
    onTutup,
}: DialogLokasiRakProps) {
    const { data, setData, post, put, processing, errors, reset, clearErrors } = useForm({
        kd_lokasi: '',
        fk_gudang_part: '',
        jenis_lokasi: '',
        kapasitas: '0',
        lokasi_part_active: true,
    });

    useEffect(() => {
        if (!terbuka) return;

        clearErrors();
        setData({
            kd_lokasi: lokasi?.kd_lokasi ?? '',
            fk_gudang_part: lokasi?.fk_gudang_part ?? '',
            jenis_lokasi: lokasi?.jenis_lokasi ?? '',
            kapasitas: lokasi?.kapasitas ?? '0',
            lokasi_part_active: lokasi?.lokasi_part_active ?? true,
        });
    }, [terbuka, lokasi]);

    const simpan = () => {
        const opsi = {
            onSuccess: () => {
                reset();
                onTutup();
                onSukses();
            },
            preserveScroll: true,
        };

        if (lokasi) {
            put(update(lokasi.kd_lokasi).url, opsi);

            return;
        }

        post(store().url, opsi);
    };

    return (
        <Dialog open={terbuka} onOpenChange={(status) => !status && onTutup()}>
            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>{lokasi ? 'Ubah Lokasi Rak' : 'Tambah Lokasi Rak'}</DialogTitle>
                    <DialogDescription>
                        Area rak tidak diisi manual — ia ditentukan dari kode lokasi.
                    </DialogDescription>
                </DialogHeader>

                <div className="grid gap-3">
                    <div className="space-y-1">
                        <Label htmlFor="kd_lokasi">
                            Kode Lokasi <span className="text-destructive">*</span>
                        </Label>
                        <Input
                            id="kd_lokasi"
                            value={data.kd_lokasi}
                            placeholder="Contoh: A1.01.3"
                            className="font-mono"
                            disabled={lokasi !== null}
                            onChange={(event) => setData('kd_lokasi', event.target.value)}
                        />
                        {lokasi !== null && (
                            <p className="text-muted-foreground text-xs">
                                Kode lokasi adalah kunci baris, jadi tidak bisa diubah. Hapus lalu
                                buat baru bila kodenya memang salah.
                            </p>
                        )}
                        {errors.kd_lokasi && (
                            <p className="text-destructive text-xs">{errors.kd_lokasi}</p>
                        )}
                    </div>

                    <div className="space-y-1">
                        <Label htmlFor="fk_gudang_part">
                            Gudang Part <span className="text-destructive">*</span>
                        </Label>
                        <Select
                            value={data.fk_gudang_part}
                            onValueChange={(nilai) => setData('fk_gudang_part', nilai)}
                        >
                            <SelectTrigger id="fk_gudang_part" className="w-full">
                                <SelectValue placeholder="Pilih gudang part" />
                            </SelectTrigger>
                            <SelectContent>
                                {daftarGudang.map((gudang) => (
                                    <SelectItem key={gudang.kode} value={gudang.kode}>
                                        {gudang.nama} ({gudang.kode})
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {errors.fk_gudang_part && (
                            <p className="text-destructive text-xs">{errors.fk_gudang_part}</p>
                        )}
                    </div>

                    <div className="grid gap-3 sm:grid-cols-2">
                        <div className="space-y-1">
                            <Label htmlFor="jenis_lokasi">Jenis Lokasi</Label>
                            <Input
                                id="jenis_lokasi"
                                list="daftar-jenis-lokasi"
                                value={data.jenis_lokasi}
                                placeholder="Contoh: REGULAR"
                                onChange={(event) => setData('jenis_lokasi', event.target.value)}
                            />
                            <datalist id="daftar-jenis-lokasi">
                                {daftarJenisLokasi.map((jenis) => (
                                    <option key={jenis} value={jenis} />
                                ))}
                            </datalist>
                            {errors.jenis_lokasi && (
                                <p className="text-destructive text-xs">{errors.jenis_lokasi}</p>
                            )}
                        </div>

                        <div className="space-y-1">
                            <Label htmlFor="kapasitas">
                                Kapasitas <span className="text-destructive">*</span>
                            </Label>
                            <Input
                                id="kapasitas"
                                type="number"
                                min={0}
                                value={data.kapasitas}
                                onChange={(event) => setData('kapasitas', event.target.value)}
                            />
                            {errors.kapasitas && (
                                <p className="text-destructive text-xs">{errors.kapasitas}</p>
                            )}
                        </div>
                    </div>

                    <div className="flex items-center justify-between rounded-md border p-3">
                        <div>
                            <Label htmlFor="lokasi_part_active">Lokasi aktif</Label>
                            <p className="text-muted-foreground text-xs">
                                Lokasi tidak aktif tetap tersimpan, tetapi tidak dipakai lagi.
                            </p>
                        </div>
                        <Switch
                            id="lokasi_part_active"
                            checked={data.lokasi_part_active}
                            onCheckedChange={(nilai) => setData('lokasi_part_active', nilai)}
                        />
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" onClick={onTutup} disabled={processing}>
                        Batal
                    </Button>
                    <Button onClick={simpan} disabled={processing}>
                        {processing && <Loader2 className="mr-1 h-4 w-4 animate-spin" />}
                        Simpan
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
