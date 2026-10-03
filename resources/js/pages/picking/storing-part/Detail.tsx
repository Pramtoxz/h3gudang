import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { detail, updateStatus } from '@/routes/picking/storing-part';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { ArrowLeft, Loader2, RotateCcw } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import { type BarisItemStoring, type DokumenStoring } from './_components/tipe';

interface Props {
    fkDo: string;
    dokumen: DokumenStoring;
    daftarPart: BarisItemStoring[];
    areaOperator: string | null;
    isAdmin: boolean;
    urlKembali: string;
}

export default function StoringPartDetail({
    fkDo,
    dokumen,
    daftarPart,
    isAdmin,
    urlKembali,
}: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Storing Part', href: urlKembali },
        { title: `Dokumen ${fkDo}`, href: detail({ query: { do: fkDo } }).url },
    ];

    const [sedangProses, setSedangProses] = useState(false);

    const tokenCsrf = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    const handleUndoStatus = async (id: number) => {
        setSedangProses(true);
        try {
            const res = await fetch(updateStatus().url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': tokenCsrf(),
                },
                body: JSON.stringify({ id, status: 'waiting' }),
            });

            const data = await res.json();
            if (data.success) {
                toast.success('Status part dikembalikan ke antrean waiting.');
                router.reload();
            } else {
                toast.error(data.message || 'Gagal mengubah status part.');
            }
        } catch (e: unknown) {
            const pesan = e instanceof Error ? e.message : 'Terjadi kesalahan sistem.';
            toast.error(pesan);
        } finally {
            setSedangProses(false);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Detail Storing ${fkDo}`} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <Card>
                    <CardHeader className="flex flex-row items-start justify-between space-y-0">
                        <div className="space-y-2">
                            <CardTitle className="flex items-center gap-2 text-base sm:text-lg">
                                Detail Storing - Dokumen: <span className="font-mono font-bold">{fkDo}</span>
                            </CardTitle>

                            <div className="bg-muted/40 space-y-1 rounded-md border-l-4 border-emerald-500 p-3 text-xs sm:text-sm">
                                <div className="flex flex-wrap gap-x-5 gap-y-1">
                                    <span>
                                        <strong>Gudang:</strong> {dokumen.nm_gudang_part || dokumen.fk_gudang || '-'}
                                    </span>
                                    <span>
                                        <strong>Tanggal Penerimaan:</strong>{' '}
                                        {dokumen.tgl_kartu
                                            ? new Date(dokumen.tgl_kartu).toLocaleDateString('id-ID')
                                            : '-'}
                                    </span>
                                    <span>
                                        <strong>Progres:</strong> {dokumen.done_items} / {dokumen.total_items} Part Selesai
                                    </span>
                                </div>
                            </div>
                        </div>

                        <Button variant="outline" size="sm" onClick={() => router.get(urlKembali)}>
                            <ArrowLeft className="mr-1 h-4 w-4" />
                            Kembali
                        </Button>
                    </CardHeader>

                    <CardContent>
                        <div className="overflow-x-auto rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead className="w-14 text-center">No.</TableHead>
                                        <TableHead>Nomor Part</TableHead>
                                        <TableHead>Deskripsi Part</TableHead>
                                        <TableHead>Nomor Doos</TableHead>
                                        <TableHead className="text-center">Lokasi Rak</TableHead>
                                        <TableHead className="text-right">Qty Diterima</TableHead>
                                        <TableHead className="text-right">Qty Masuk</TableHead>
                                        <TableHead className="text-center">Status</TableHead>
                                        <TableHead className="text-center">Waktu Selesai</TableHead>
                                        <TableHead className="w-36 text-center">Aksi</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {daftarPart.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={10} className="text-muted-foreground h-24 text-center">
                                                Tidak ada part dalam dokumen penerimaan ini di area Anda.
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        daftarPart.map((part, idx) => (
                                            <TableRow key={part.id}>
                                                <TableCell className="text-muted-foreground text-center text-xs tabular-nums">
                                                    {idx + 1}
                                                </TableCell>
                                                <TableCell className="font-mono text-xs font-bold">
                                                    {part.no_part}
                                                </TableCell>
                                                <TableCell className="max-w-56 truncate text-xs">
                                                    {part.nm_part}
                                                </TableCell>
                                                <TableCell className="font-mono text-xs">
                                                    {part.no_doos || '-'}
                                                </TableCell>
                                                <TableCell className="text-center text-xs">
                                                    <Badge variant="outline" className="font-mono font-bold">
                                                        {part.kode_rak}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell className="text-right text-xs tabular-nums font-semibold">
                                                    {part.qty_diterima}
                                                </TableCell>
                                                <TableCell className="text-right text-xs tabular-nums font-bold text-emerald-600">
                                                    {part.qty_masuk !== null ? part.qty_masuk : '-'}
                                                </TableCell>
                                                <TableCell className="text-center">
                                                    {part.status_masuk ? (
                                                        <Badge className="bg-emerald-600 text-white hover:bg-emerald-600">
                                                            Done
                                                        </Badge>
                                                    ) : (
                                                        <Badge variant="destructive">Waiting</Badge>
                                                    )}
                                                </TableCell>
                                                <TableCell className="text-center text-xs tabular-nums text-muted-foreground">
                                                    {part.waktu_done
                                                        ? new Date(part.waktu_done).toLocaleString('id-ID')
                                                        : '-'}
                                                </TableCell>
                                                <TableCell className="text-center">
                                                    {part.status_masuk ? (
                                                        isAdmin ? (
                                                            <Button
                                                                variant="secondary"
                                                                size="sm"
                                                                onClick={() => handleUndoStatus(part.id)}
                                                                disabled={sedangProses}
                                                                title="Admin dapat membatalkan item yang sudah masuk rak"
                                                            >
                                                                {sedangProses && (
                                                                    <Loader2 className="mr-1 h-4 w-4 animate-spin" />
                                                                )}
                                                                <RotateCcw className="mr-1 h-3.5 w-3.5" />
                                                                Undo
                                                            </Button>
                                                        ) : (
                                                            <span className="text-muted-foreground text-xs italic">
                                                                Selesai
                                                            </span>
                                                        )
                                                    ) : (
                                                        <span className="text-muted-foreground text-xs italic">
                                                            Tunggu operator simpan ke rak
                                                        </span>
                                                    )}
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
