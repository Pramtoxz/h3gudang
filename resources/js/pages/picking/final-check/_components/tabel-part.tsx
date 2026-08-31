import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { CheckCircle2, Hourglass } from 'lucide-react';
import type { BarisPartFinal } from './tipe';

interface TabelPartProps {
    daftar: BarisPartFinal[];
    kotak: Record<number, string>;
    onKotak: (id: number, nilai: string) => void;
}

function waktu(nilai: string | null): string {
    if (!nilai) {
        return '-';
    }

    return new Date(nilai.replace(' ', 'T')).toLocaleDateString('id-ID');
}

export function TabelPart({ daftar, kotak, onKotak }: TabelPartProps) {
    return (
        <div className="overflow-x-auto rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead className="w-14 text-center">No.</TableHead>
                        <TableHead>Part Number</TableHead>
                        <TableHead>Deskripsi</TableHead>
                        <TableHead className="text-center">Lokasi Rak</TableHead>
                        <TableHead className="text-center">Qty Pick</TableHead>
                        <TableHead className="w-40 text-center">No. Kotak</TableHead>
                        <TableHead className="text-center">User Cek</TableHead>
                        <TableHead className="text-center">Tgl Final</TableHead>
                        <TableHead className="text-center">Status</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {daftar.length === 0 ? (
                        <TableRow>
                            <TableCell colSpan={9} className="text-muted-foreground h-24 text-center">
                                Tidak ada part yang sudah selesai diambil pada DO ini.
                            </TableCell>
                        </TableRow>
                    ) : (
                        daftar.map((part, indeks) => (
                            <TableRow key={part.id}>
                                <TableCell className="text-muted-foreground text-center text-xs tabular-nums">
                                    {indeks + 1}
                                </TableCell>
                                <TableCell className="font-mono text-sm">{part.fk_part}</TableCell>
                                <TableCell className="max-w-48 truncate text-sm">{part.nm_part}</TableCell>
                                <TableCell className="text-center font-mono text-sm">
                                    {part.lokasi_part}
                                </TableCell>
                                <TableCell className="text-center text-sm tabular-nums">
                                    {part.qty_picking}
                                </TableCell>
                                <TableCell>
                                    <Input
                                        value={kotak[part.id] ?? ''}
                                        onChange={(event) => onKotak(part.id, event.target.value)}
                                        placeholder="Ketik No. Kotak"
                                        className="h-9 text-center font-mono text-sm"
                                    />
                                </TableCell>
                                <TableCell className="text-center text-sm">
                                    {part.user_cek ?? '-'}
                                </TableCell>
                                <TableCell className="text-center text-sm">
                                    {waktu(part.tgl_final)}
                                </TableCell>
                                <TableCell className="text-center">
                                    {part.status_picking_list === 'final' ? (
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
                            </TableRow>
                        ))
                    )}
                </TableBody>
            </Table>
        </div>
    );
}
