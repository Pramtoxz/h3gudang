import { DialogKonfirmasi } from '@/components/dialog-konfirmasi';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { detail, store } from '@/routes/picking/final-check';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { ArrowLeft, Save, TriangleAlert } from 'lucide-react';
import { useMemo, useState } from 'react';
import { TabelPart } from './_components/tabel-part';
import type { BarisPartFinal, InfoDoFinal } from './_components/tipe';

interface Props {
    fkDo: string;
    infoDo: InfoDoFinal;
    daftarPart: BarisPartFinal[];
    isBundling: boolean;
    urlKembali: string;
}

function tanggal(nilai: string | null): string {
    if (!nilai) {
        return '-';
    }

    return new Date(nilai.replace(' ', 'T')).toLocaleDateString('id-ID');
}

export default function FinalCheckDetail({
    fkDo,
    infoDo,
    daftarPart,
    isBundling,
    urlKembali,
}: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Final Check', href: urlKembali },
        { title: `DO ${fkDo}`, href: detail({ query: { do: fkDo } }).url },
    ];

    const [kotak, setKotak] = useState<Record<number, string>>(() =>
        Object.fromEntries(daftarPart.map((part) => [part.id, part.nomor_kotak ?? ''])),
    );
    const [keteranganFinal, setKeteranganFinal] = useState(infoDo.keterangan_final ?? '');
    const [jumlahKoli, setJumlahKoli] = useState(infoDo.poli ? String(infoDo.poli) : '');
    const [konfirmasi, setKonfirmasi] = useState(false);
    const [sedangProses, setSedangProses] = useState(false);

    const kotakTerisi = useMemo(
        () =>
            Object.entries(kotak)
                .map(([id, nomor]) => ({ id: Number(id), nomor_kotak: nomor.trim() }))
                .filter((baris) => baris.nomor_kotak !== ''),
        [kotak],
    );

    const belumFinal = daftarPart.filter((part) => part.status_picking_list !== 'final').length;

    const simpan = () => {
        setSedangProses(true);

        router.post(
            store().url,
            {
                fk_do: fkDo,
                keterangan_final: keteranganFinal,
                poli: jumlahKoli === '' ? null : Number(jumlahKoli),
                kotak: kotakTerisi,
            },
            {
                preserveScroll: true,
                onFinish: () => {
                    setSedangProses(false);
                    setKonfirmasi(false);
                },
            },
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Final Check ${fkDo}`} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <Card>
                    <CardHeader className="flex flex-row items-start justify-between space-y-0">
                        <div className="space-y-2">
                            <CardTitle className="flex items-center gap-2 text-base">
                                Final Check DO: <span className="font-mono">{fkDo}</span>
                                {isBundling && (
                                    <Badge variant="destructive">
                                        <TriangleAlert className="mr-1 h-3 w-3" />
                                        URGENT
                                    </Badge>
                                )}
                            </CardTitle>

                            <div className="bg-muted/40 space-y-1 rounded-md border-l-4 border-red-500 p-3 text-sm">
                                <div className="flex flex-wrap gap-x-4 gap-y-1">
                                    <span>
                                        <strong>Channel:</strong> {infoDo.nama_channel}
                                    </span>
                                    <span>
                                        <strong>Dealer:</strong> {infoDo.fk_dealer ?? '-'}
                                    </span>
                                    <span>
                                        <strong>Area:</strong> {infoDo.area}
                                    </span>
                                    <span>
                                        <strong>Tanggal DO:</strong>{' '}
                                        <span className="font-semibold text-red-600">
                                            {tanggal(infoDo.tgl_picking_list_part)}
                                        </span>
                                    </span>
                                </div>
                                <div className="border-t pt-1">
                                    <strong>Keterangan:</strong>{' '}
                                    <span className="font-semibold text-blue-600">
                                        {infoDo.keterangan_picking}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <Button variant="outline" size="sm" onClick={() => router.get(urlKembali)}>
                            <ArrowLeft className="mr-1 h-4 w-4" />
                            Kembali
                        </Button>
                    </CardHeader>

                    <CardContent className="space-y-3">
                        <TabelPart
                            daftar={daftarPart}
                            kotak={kotak}
                            onKotak={(id, nilai) => setKotak((isi) => ({ ...isi, [id]: nilai }))}
                        />

                        <div className="space-y-2 rounded-md border p-3">
                            <div className="grid gap-2 sm:grid-cols-[1fr_180px_auto] sm:items-end">
                                <div className="space-y-1">
                                    <Label htmlFor="keterangan_final" className="text-xs">
                                        Keterangan Final
                                    </Label>
                                    <Input
                                        id="keterangan_final"
                                        value={keteranganFinal}
                                        onChange={(event) => setKeteranganFinal(event.target.value)}
                                        placeholder="Keterangan Final"
                                    />
                                </div>

                                <div className="space-y-1">
                                    <Label htmlFor="jumlah_koli" className="text-xs">
                                        Jumlah Koli
                                    </Label>
                                    <Input
                                        id="jumlah_koli"
                                        type="number"
                                        min="0"
                                        value={jumlahKoli}
                                        onChange={(event) => setJumlahKoli(event.target.value)}
                                        placeholder="Jumlah Koli"
                                    />
                                </div>

                                <Button
                                    disabled={kotakTerisi.length === 0 || sedangProses}
                                    onClick={() => setKonfirmasi(true)}
                                >
                                    <Save className="mr-1 h-4 w-4" />
                                    Simpan Data
                                </Button>
                            </div>

                            <p className="text-muted-foreground text-xs">
                                Isi nomor kotak di tabel, lalu klik <strong>Simpan Data</strong> untuk
                                menyimpan dan mengubah status menjadi FINAL.
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <DialogKonfirmasi
                terbuka={konfirmasi}
                judul="Konfirmasi Final Check"
                keterangan={
                    <div className="space-y-2">
                        <div className="space-y-1">
                            <p>
                                <strong>DO:</strong> <span className="font-mono">{fkDo}</span>
                            </p>
                            <p>
                                <strong>Keterangan:</strong> {keteranganFinal || '-'}
                            </p>
                            <p>
                                <strong>Jumlah Koli:</strong> {jumlahKoli || '-'}
                            </p>
                            <p>
                                <strong>Part dengan kotak:</strong> {kotakTerisi.length} item
                            </p>
                        </div>
                        {belumFinal > 0 ? (
                            <p className="rounded-md border border-amber-300 bg-amber-50 p-2 text-xs text-amber-800">
                                {belumFinal} part akan diubah statusnya menjadi FINAL dan tidak dapat
                                dikembalikan lewat halaman ini.
                            </p>
                        ) : (
                            <p className="text-muted-foreground text-xs">
                                Seluruh part sudah berstatus Final. Menyimpan hanya memperbarui nomor
                                kotak, keterangan, dan jumlah koli.
                            </p>
                        )}
                        {isBundling && (
                            <p className="rounded-md border border-red-300 bg-red-50 p-2 text-xs text-red-800">
                                DO bundling — notifikasi WhatsApp akan dikirim ke grup invoice setelah
                                finalisasi.
                            </p>
                        )}
                    </div>
                }
                labelKonfirmasi="Ya, Finalkan"
                sedangProses={sedangProses}
                onBatal={() => setKonfirmasi(false)}
                onKonfirmasi={simpan}
            />
        </AppLayout>
    );
}
