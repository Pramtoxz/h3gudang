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
import { detail } from '@/routes/picking/final-check';
import { Link } from '@inertiajs/react';
import { CheckCircle2, ClipboardCheck, Hourglass, TriangleAlert } from 'lucide-react';
import type { BarisDoFinal } from './tipe';

interface TabelDoProps {
    daftar: BarisDoFinal[];
    awalBaris: number;
    penyaringAktif: Record<string, string | number>;
}

function tanggal(nilai: string | null): string {
    if (!nilai) {
        return '-';
    }

    return new Date(nilai.replace(' ', 'T')).toLocaleDateString('id-ID');
}

function BarProgres({ selesai, total }: { selesai: number; total: number }) {
    const persen = total > 0 ? Math.round((selesai / total) * 100) : 0;

    return (
        <div className="bg-muted relative h-5 w-full min-w-24 overflow-hidden rounded-full">
            <div
                className={`h-full transition-all ${persen === 100 ? 'bg-emerald-600' : 'bg-destructive'}`}
                style={{ width: `${persen}%` }}
            />
            <span className="absolute inset-0 flex items-center justify-center text-[11px] font-bold">
                {persen}%
            </span>
        </div>
    );
}

export function TabelDo({ daftar, awalBaris, penyaringAktif }: TabelDoProps) {
    return (
        <div className="overflow-x-auto rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead className="w-14 text-center">No.</TableHead>
                        <TableHead>Nomor DO</TableHead>
                        <TableHead>Tanggal Picking</TableHead>
                        <TableHead>Nama Channel</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead className="text-right">Total Item</TableHead>
                        <TableHead className="text-right">Jumlah Koli</TableHead>
                        <TableHead className="w-32">Progress</TableHead>
                        <TableHead className="w-32 text-center">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {daftar.length === 0 ? (
                        <TableRow>
                            <TableCell colSpan={9} className="text-muted-foreground h-24 text-center">
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
                                    <span className="font-mono text-xs font-medium">{baris.fk_do}</span>
                                    {baris.is_bundling && (
                                        <Badge variant="destructive" className="ml-2">
                                            <TriangleAlert className="mr-1 h-3 w-3" />
                                            URGENT
                                        </Badge>
                                    )}
                                </TableCell>
                                <TableCell className="text-sm">
                                    {tanggal(baris.tgl_picking_list_part)}
                                </TableCell>
                                <TableCell className="text-sm">{baris.nama_channel}</TableCell>
                                <TableCell>
                                    {baris.status_do === 'Final' ? (
                                        <Badge className="bg-emerald-600 text-white hover:bg-emerald-600">
                                            <CheckCircle2 className="mr-1 h-3 w-3" />
                                            Final
                                        </Badge>
                                    ) : (
                                        <Badge variant="destructive">
                                            <Hourglass className="mr-1 h-3 w-3" />
                                            Done
                                        </Badge>
                                    )}
                                </TableCell>
                                <TableCell className="text-right text-sm tabular-nums">
                                    {baris.total_items}
                                </TableCell>
                                <TableCell className="text-right text-sm tabular-nums">
                                    {baris.poli || '-'}
                                </TableCell>
                                <TableCell>
                                    <BarProgres selesai={baris.final_parts} total={baris.total_items} />
                                </TableCell>
                                <TableCell className="text-center">
                                    <Link
                                        href={
                                            detail({
                                                query: { do: baris.fk_do, ...penyaringAktif },
                                            }).url
                                        }
                                    >
                                        <Button variant="outline" size="sm">
                                            <ClipboardCheck className="mr-1 h-4 w-4" />
                                            Final Check
                                        </Button>
                                    </Link>
                                </TableCell>
                            </TableRow>
                        ))
                    )}
                </TableBody>
            </Table>
        </div>
    );
}
