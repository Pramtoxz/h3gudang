import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { store, update } from '@/routes/picking/channel';
import { useForm } from '@inertiajs/react';
import { Loader2 } from 'lucide-react';
import { useEffect } from 'react';

export interface BarisChannel {
    id: number;
    area: string;
    kode_channel: string;
    nama_channel: string | null;
    nama_invoice: string | null;
}

interface DialogChannelProps {
    terbuka: boolean;
    channel: BarisChannel | null;
    daftarArea: string[];
    onTutup: () => void;
}

export function DialogChannel({ terbuka, channel, daftarArea, onTutup }: DialogChannelProps) {
    const { data, setData, post, put, processing, errors, reset, clearErrors } = useForm({
        area: '',
        kode_channel: '',
        nama_channel: '',
        nama_invoice: '',
    });

    useEffect(() => {
        if (!terbuka) return;

        clearErrors();
        setData({
            area: channel?.area ?? '',
            kode_channel: channel?.kode_channel ?? '',
            nama_channel: channel?.nama_channel ?? '',
            nama_invoice: channel?.nama_invoice ?? '',
        });
    }, [terbuka, channel]);

    const simpan = () => {
        const opsi = {
            onSuccess: () => {
                reset();
                onTutup();
            },
            preserveScroll: true,
        };

        if (channel) {
            put(update(channel.id).url, opsi);

            return;
        }

        post(store().url, opsi);
    };

    return (
        <Dialog open={terbuka} onOpenChange={(status) => !status && onTutup()}>
            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>{channel ? 'Edit Channel' : 'Tambah Channel'}</DialogTitle>
                </DialogHeader>

                <div className="grid gap-3">
                    <div className="space-y-1">
                        <Label htmlFor="area">
                            Area <span className="text-destructive">*</span>
                        </Label>
                        <Input
                            id="area"
                            list="daftar-area-channel"
                            value={data.area}
                            placeholder="Contoh: Padang"
                            onChange={(event) => setData('area', event.target.value)}
                        />
                        <datalist id="daftar-area-channel">
                            {daftarArea.map((area) => (
                                <option key={area} value={area} />
                            ))}
                        </datalist>
                        <p className="text-muted-foreground text-xs">
                            Pilih dari daftar yang sudah ada, atau ketik area baru.
                        </p>
                        {errors.area && <p className="text-destructive text-xs">{errors.area}</p>}
                    </div>

                    <div className="space-y-1">
                        <Label htmlFor="kode_channel">
                            Kode Channel <span className="text-destructive">*</span>
                        </Label>
                        <Input
                            id="kode_channel"
                            value={data.kode_channel}
                            placeholder="Contoh: MTO1"
                            className="font-mono"
                            onChange={(event) => setData('kode_channel', event.target.value)}
                        />
                        {errors.kode_channel && (
                            <p className="text-destructive text-xs">{errors.kode_channel}</p>
                        )}
                    </div>

                    <div className="space-y-1">
                        <Label htmlFor="nama_channel">
                            Nama Channel <span className="text-destructive">*</span>
                        </Label>
                        <Input
                            id="nama_channel"
                            value={data.nama_channel}
                            onChange={(event) => setData('nama_channel', event.target.value)}
                        />
                        {errors.nama_channel && (
                            <p className="text-destructive text-xs">{errors.nama_channel}</p>
                        )}
                    </div>

                    <div className="space-y-1">
                        <Label htmlFor="nama_invoice">
                            Nama Invoice <span className="text-destructive">*</span>
                        </Label>
                        <Input
                            id="nama_invoice"
                            value={data.nama_invoice}
                            onChange={(event) => setData('nama_invoice', event.target.value)}
                        />
                        {errors.nama_invoice && (
                            <p className="text-destructive text-xs">{errors.nama_invoice}</p>
                        )}
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
