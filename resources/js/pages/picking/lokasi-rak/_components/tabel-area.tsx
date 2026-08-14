import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Eye, Trash2 } from 'lucide-react';
import { LencanaStatus } from './lencana-status';
import type { BarisRingkasanArea } from './tipe';

interface TabelAreaProps {
    ringkasan: BarisRingkasanArea[];
    kosong: boolean;
    bolehHapus: boolean;
    onDetail: (baris: BarisRingkasanArea) => void;
    onHapusMassal: (baris: BarisRingkasanArea) => void;
}

export function TabelArea({
    ringkasan,
    kosong,
    bolehHapus,
    onDetail,
    onHapusMassal,
}: TabelAreaProps) {
    return (
        <div className="overflow-x-auto rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead className="w-14 text-center">No.</TableHead>
                        <TableHead>Area Lokasi</TableHead>
                        <TableHead>Gudang Part</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead className="w-24 text-center">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {ringkasan.length === 0 ? (
                        <TableRow>
                            <TableCell colSpan={5} className="text-muted-foreground h-20 text-center">
                                {kosong
                                    ? 'Belum ada lokasi rak.'
                                    : 'Tidak ada area yang cocok dengan pencarian.'}
                            </TableCell>
                        </TableRow>
                    ) : (
                        ringkasan.map((baris, indeks) => (
                            <TableRow key={`${baris.area_rak}-${baris.nama_gudang}`}>
                                <TableCell className="text-muted-foreground text-center text-xs tabular-nums">
                                    {indeks + 1}
                                </TableCell>
                                <TableCell>
                                    <div className="font-medium">{baris.area_rak}</div>
                                    <div className="text-muted-foreground text-xs">
                                        ({baris.total_lokasi} lokasi)
                                    </div>
                                </TableCell>
                                <TableCell className="text-sm">{baris.nama_gudang}</TableCell>
                                <TableCell>
                                    <LencanaStatus
                                        total={baris.total_lokasi}
                                        aktif={baris.total_aktif}
                                    />
                                </TableCell>
                                <TableCell>
                                    <div className="flex items-center justify-center gap-1">
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            className="h-8 w-8 p-0"
                                            title="Detail"
                                            onClick={() => onDetail(baris)}
                                        >
                                            <Eye className="h-4 w-4" />
                                        </Button>
                                        {bolehHapus && (
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                className="text-destructive h-8 w-8 p-0"
                                                title="Hapus seluruh lokasi pada area ini"
                                                onClick={() => onHapusMassal(baris)}
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
