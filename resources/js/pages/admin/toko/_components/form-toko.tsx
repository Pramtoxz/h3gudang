import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { Link, useForm } from '@inertiajs/react';
import { Loader2, Save } from 'lucide-react';
import type { FormEvent } from 'react';

export interface NilaiFormToko {
    kd_toko: string;
    toko: string;
    no_telp: string;
    npwp: string;
    alamat: string;
    kategori: string;
    kd_ahm: string;
    toko_active: boolean;
}

interface FormTokoProps {
    nilaiAwal: NilaiFormToko;
    mode: 'tambah' | 'ubah';
}

export function FormToko({ nilaiAwal, mode }: FormTokoProps) {
    const { data, setData, post, put, processing, errors } = useForm<NilaiFormToko>(nilaiAwal);

    const kirim = (event: FormEvent) => {
        event.preventDefault();

        if (mode === 'tambah') {
            post('/admin/toko');

            return;
        }

        put(`/admin/toko/${nilaiAwal.kd_toko}`);
    };

    return (
        <form onSubmit={kirim} className="space-y-4">
            <div className="grid gap-4 md:grid-cols-2">
                <div className="space-y-1">
                    <Label htmlFor="kd_toko">
                        Kode Toko {mode === 'tambah' && <span className="text-destructive">*</span>}
                    </Label>
                    <Input
                        id="kd_toko"
                        value={data.kd_toko}
                        maxLength={10}
                        disabled={mode === 'ubah'}
                        onChange={(event) => setData('kd_toko', event.target.value)}
                    />
                    {mode === 'ubah' && (
                        <p className="text-muted-foreground text-xs">Kode toko tidak dapat diubah.</p>
                    )}
                    {errors.kd_toko && <p className="text-destructive text-xs">{errors.kd_toko}</p>}
                </div>

                <div className="space-y-1">
                    <Label htmlFor="toko">
                        Nama Toko <span className="text-destructive">*</span>
                    </Label>
                    <Input
                        id="toko"
                        value={data.toko}
                        maxLength={255}
                        onChange={(event) => setData('toko', event.target.value)}
                    />
                    {errors.toko && <p className="text-destructive text-xs">{errors.toko}</p>}
                </div>

                <div className="space-y-1">
                    <Label htmlFor="no_telp">No. Telepon</Label>
                    <Input
                        id="no_telp"
                        value={data.no_telp}
                        maxLength={20}
                        placeholder="08123456789"
                        onChange={(event) => setData('no_telp', event.target.value)}
                    />
                    {errors.no_telp && <p className="text-destructive text-xs">{errors.no_telp}</p>}
                </div>

                <div className="space-y-1">
                    <Label htmlFor="npwp">NPWP</Label>
                    <Input
                        id="npwp"
                        value={data.npwp}
                        maxLength={20}
                        placeholder="00.000.000.0-000.000"
                        onChange={(event) => setData('npwp', event.target.value)}
                    />
                    {errors.npwp && <p className="text-destructive text-xs">{errors.npwp}</p>}
                </div>

                <div className="space-y-1">
                    <Label htmlFor="kategori">Kategori</Label>
                    <Input
                        id="kategori"
                        value={data.kategori}
                        maxLength={50}
                        onChange={(event) => setData('kategori', event.target.value)}
                    />
                    {errors.kategori && <p className="text-destructive text-xs">{errors.kategori}</p>}
                </div>

                <div className="space-y-1">
                    <Label htmlFor="kd_ahm">Kode AHM</Label>
                    <Input
                        id="kd_ahm"
                        value={data.kd_ahm}
                        maxLength={10}
                        onChange={(event) => setData('kd_ahm', event.target.value)}
                    />
                    {errors.kd_ahm && <p className="text-destructive text-xs">{errors.kd_ahm}</p>}
                </div>
            </div>

            <div className="space-y-1">
                <Label htmlFor="alamat">Alamat</Label>
                <Textarea
                    id="alamat"
                    rows={3}
                    value={data.alamat}
                    placeholder="Alamat lengkap toko"
                    onChange={(event) => setData('alamat', event.target.value)}
                />
                {errors.alamat && <p className="text-destructive text-xs">{errors.alamat}</p>}
            </div>

            <div className="flex items-center gap-3 rounded-lg border p-3">
                <Switch
                    id="toko_active"
                    checked={data.toko_active}
                    onCheckedChange={(status) => setData('toko_active', status)}
                />
                <div>
                    <Label htmlFor="toko_active">Toko Aktif</Label>
                    <p className="text-muted-foreground text-xs">
                        Nonaktifkan toko jika tidak ingin menghapus datanya.
                    </p>
                </div>
            </div>

            <div className="flex gap-2">
                <Button type="submit" disabled={processing}>
                    {processing ? (
                        <Loader2 className="mr-1 h-4 w-4 animate-spin" />
                    ) : (
                        <Save className="mr-1 h-4 w-4" />
                    )}
                    {mode === 'tambah' ? 'Simpan' : 'Perbarui'}
                </Button>
                <Button type="button" variant="outline" asChild>
                    <Link href="/admin/toko">Batal</Link>
                </Button>
            </div>
        </form>
    );
}
