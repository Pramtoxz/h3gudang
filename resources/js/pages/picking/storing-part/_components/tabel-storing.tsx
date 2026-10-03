import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { detail } from '@/routes/picking/storing-part';
import { CheckCircle2, Eye, Hourglass } from 'lucide-react';
import { Link } from '@inertiajs/react';
import type { BarisStoring } from './tipe';

interface TabelStoringProps {
    daftar: BarisStoring[];
    awalBaris: number;
    penyaringAktif: Record<string, string | number>;
}

function LencanaStatus({ status }: { status: BarisStoring['status_storing'] }) {
    if (status === 'Done') {
        return (
            <Badge className="bg-emerald-600 text-white hover:bg-emerald-600">
                <CheckCircle2 className="mr-1 h-3 w-3" />
                Done
            </Badge>
        );
    }

    return (
        <Badge variant="destructive">
            <Hourglass className="mr-1 h-3 w-3" />
            Waiting
        </Badge>
    );
}

function BarProgres({ selesai, total }: { selesai: number; total: number }) {
    const persen = total > 0 ? Math.round((selesai / total) * 100) : 0;
    const warna = persen === 100 ? 'bg-emerald-600' : persen > 0 ? 'bg-amber-500' : 'bg-destructive';

    return (
        <div className="bg-muted relative h-5 w-full min-w-24 overflow-hidden rounded-full">
            <div className={`h-full ${warna} transition-all`} style={{ width: `${persen}%` }} />
            <span className="absolute inset-0 flex items-center justify-center text-[11px] font-bold">
                {persen}% ({selesai}/{total})
            </span>
        </div>
    );
}

export function TabelStoring({ daftar, awalBaris, penyaringAktif }: TabelStoringProps) {
    return (
        <div className="overflow-x-auto rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead className="w-14 text-center">No.</TableHead>
                        <TableHead>No. Dokumen Penerimaan</TableHead>
                        <TableHead>Gudang</TableHead>
                        <TableHead>Tgl. Dokumen</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead className="text-right">Total Item</TableHead>
                        <TableHead className="text-right">Qty Diterima</TableHead>
                        <TableHead className="text-right">Qty Masuk</TableHead>
                        <TableHead className="w-36">Progres Rak</TableHead>
                        <TableHead className="w-20 text-center">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {daftar.length === 0 ? (
                        <TableRow>
                            <TableCell colSpan={10} className="text-muted-foreground h-24 text-center">
                                Tidak ada dokumen storing yang cocok dengan penyaringan.
                            </TableCell>
                        </TableRow>
                    ) : (
                        daftar.map((baris, indeks) => (
                            <TableRow key={baris.fk_do}>
                                <TableCell className="text-muted-foreground text-center text-xs tabular-nums">
                                    {awalBaris + indeks + 1}
                                </TableCell>
                                <TableCell className="font-mono text-xs font-bold">
                                    {baris.fk_do}
                                </TableCell>
                                <TableCell className="text-xs">
                                    <Badge variant="outline">{baris.nm_gudang_part}</Badge>
                                </TableCell>
                                <TableCell className="text-xs tabular-nums text-muted-foreground">
                                    {baris.tgl_kartu
                                        ? new Date(baris.tgl_kartu).toLocaleDateString('id-ID')
                                        : '-'}
                                </TableCell>
                                <TableCell>
                                    <LencanaStatus status={baris.status_storing} />
                                </TableCell>
                                <TableCell className="text-right text-xs tabular-nums font-semibold">
                                    {baris.total_items}
                                </TableCell>
                                <TableCell className="text-right text-xs tabular-nums">
                                    {baris.total_qty_diterima}
                                </TableCell>
                                <TableCell className="text-right text-xs tabular-nums font-bold text-emerald-600">
                                    {baris.total_qty_masuk}
                                </TableCell>
                                <TableCell>
                                    <BarProgres selesai={baris.done_items} total={baris.total_items} />
                                </TableCell>
                                <TableCell className="text-center">
                                    <Button asChild variant="outline" size="sm">
                                        <Link
                                            href={detail({
                                                query: {
                                                    do: baris.fk_do,
                                                    dari:
                                                        Object.keys(penyaringAktif).length > 0
                                                            ? `/picking/storing-part?${new URLSearchParams(
                                                                  penyaringAktif as Record<string, string>,
                                                              ).toString()}`
                                                            : '/picking/storing-part',
                                                },
                                            }).url}
                                        >
                                            <Eye className="h-4 w-4" />
                                        </Link>
                                    </Button>
                                </TableCell>
                            </TableRow>
                        ))
                    )}
                </TableBody>
            </Table>
        </div>
    );
}
