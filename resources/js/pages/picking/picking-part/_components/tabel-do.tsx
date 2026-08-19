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
import { detail, index } from '@/routes/picking/picking-part';
import { CheckCircle2, Clock, Eye, Hourglass, Trash2, TriangleAlert } from 'lucide-react';
import { Link } from '@inertiajs/react';
import type { BarisDo } from './tipe';

interface TabelDoProps {
    daftar: BarisDo[];
    awalBaris: number;
    bolehHapus: boolean;
    onHapus: (baris: BarisDo) => void;
}

function LencanaStatus({ status }: { status: BarisDo['status_do'] }) {
    if (status === 'Done') {
        return (
            <Badge className="bg-emerald-600 text-white hover:bg-emerald-600">
                <CheckCircle2 className="mr-1 h-3 w-3" />
                Done
            </Badge>
        );
    }

    if (status === 'On Progress') {
        return (
            <Badge className="bg-amber-500 text-white hover:bg-amber-500">
                <Clock className="mr-1 h-3 w-3" />
                On Progress
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
                {persen}%
            </span>
        </div>
    );
}

export function TabelDo({ daftar, awalBaris, bolehHapus, onHapus }: TabelDoProps) {
    return (
        <div className="overflow-x-auto rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead className="w-14 text-center">No.</TableHead>
                        <TableHead>Nomor DO</TableHead>
                        <TableHead>Nama Channel</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead className="text-right">Total Item</TableHead>
                        <TableHead className="text-right">Total Picking</TableHead>
                        <TableHead className="w-32">Progress</TableHead>
                        <TableHead className="w-24 text-center">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {daftar.length === 0 ? (
                        <TableRow>
                            <TableCell colSpan={8} className="text-muted-foreground h-24 text-center">
                                Tidak ada DO yang cocok dengan penyaringan.
                            </TableCell>
                        </TableRow>
                    ) : (
                        daftar.map((baris, indeks) => (
                            <TableRow key={baris.fk_do}>
                                <TableCell className="text-muted-foreground text-center text-xs tabular-nums">
                                    {awalBaris + indeks + 1}
                                </TableCell>
                                <TableCell>
                                    <span className="font-mono text-xs font-medium">
                                        {baris.fk_do}
                                    </span>
                                    {baris.is_bundling && (
                                        <Badge variant="destructive" className="ml-2 animate-pulse">
                                            <TriangleAlert className="mr-1 h-3 w-3" />
                                            URGENT
                                        </Badge>
                                    )}
                                </TableCell>
                                <TableCell className="text-sm">{baris.nama_channel}</TableCell>
                                <TableCell>
                                    <LencanaStatus status={baris.status_do} />
                                </TableCell>
                                <TableCell className="text-right text-sm tabular-nums">
                                    {baris.total_items}
                                </TableCell>
                                <TableCell className="text-right text-sm tabular-nums">
                                    {baris.total_picking}
                                </TableCell>
                                <TableCell>
                                    <BarProgres
                                        selesai={baris.done_parts}
                                        total={baris.total_items}
                                    />
                                </TableCell>
                                <TableCell>
                                    <div className="flex items-center justify-center gap-1">
                                        <Link href={detail({ do: baris.fk_do })}>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                className="h-8 w-8 p-0"
                                                title="Lihat detail DO"
                                            >
                                                <Eye className="h-4 w-4" />
                                            </Button>
                                        </Link>
                                        {bolehHapus && (
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                className="text-destructive h-8 w-8 p-0"
                                                title="Hapus seluruh item DO ini"
                                                onClick={() => onHapus(baris)}
                                            >
                                                <Trash2 className="h-4 w-4" />
                                            </Button>
                                        )}
                                    </div>
                                </TableCell>
                            </TableRow>
                        ))
                    )}
                </TableBody>
            </Table>
        </div>
    );
}
