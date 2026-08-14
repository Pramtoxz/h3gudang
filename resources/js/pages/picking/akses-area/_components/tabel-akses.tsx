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
import { Pencil, Trash2 } from 'lucide-react';
import { LEVEL_ADMIN, type BarisAksesArea } from './tipe';

interface TabelAksesProps {
    daftar: BarisAksesArea[];
    kosong: boolean;
    bolehUbah: boolean;
    bolehHapus: boolean;
    onUbah: (baris: BarisAksesArea) => void;
    onHapus: (baris: BarisAksesArea) => void;
}

export function TabelAkses({
    daftar,
    kosong,
    bolehUbah,
    bolehHapus,
    onUbah,
    onHapus,
}: TabelAksesProps) {
    return (
        <div className="overflow-x-auto rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead className="w-14 text-center">No.</TableHead>
                        <TableHead>Email</TableHead>
                        <TableHead>Nama Pengguna</TableHead>
                        <TableHead>Area Rak</TableHead>
                        <TableHead>Level Akses</TableHead>
                        <TableHead className="w-24 text-center">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {daftar.length === 0 ? (
                        <TableRow>
                            <TableCell colSpan={6} className="text-muted-foreground h-24 text-center">
                                {kosong
                                    ? 'Belum ada akses area yang diatur.'
                                    : 'Tidak ada baris yang cocok dengan pencarian.'}
                            </TableCell>
                        </TableRow>
                    ) : (
                        daftar.map((baris, indeks) => (
                            <TableRow key={baris.email}>
                                <TableCell className="text-muted-foreground text-center text-xs tabular-nums">
                                    {indeks + 1}
                                </TableCell>
                                <TableCell className="text-sm">
                                    {baris.email}
                                    {!baris.ada_di_dms && (
                                        <Badge
                                            variant="outline"
                                            className="text-destructive border-destructive/40 ml-2"
                                        >
                                            tanpa akun
                                        </Badge>
                                    )}
                                </TableCell>
                                <TableCell className="text-sm font-medium">
                                    {baris.nama_dms || baris.username || '-'}
                                </TableCell>
                                <TableCell>
                                    {baris.level === LEVEL_ADMIN ? (
                                        <span className="text-muted-foreground text-sm">
                                            Semua area
                                        </span>
                                    ) : (
                                        <Badge variant="secondary">{baris.area || '-'}</Badge>
                                    )}
                                </TableCell>
                                <TableCell>
                                    {baris.level === LEVEL_ADMIN ? (
                                        <Badge className="bg-sky-600 text-white hover:bg-sky-600">
                                            Admin
                                        </Badge>
                                    ) : (
                                        <Badge className="bg-emerald-600 text-white hover:bg-emerald-600">
                                            PIC
                                        </Badge>
                                    )}
                                </TableCell>
                                <TableCell>
                                    <div className="flex items-center justify-center gap-1">
                                        {bolehUbah && (
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                className="h-8 w-8 p-0"
                                                title="Ubah"
                                                onClick={() => onUbah(baris)}
                                            >
                                                <Pencil className="h-4 w-4" />
                                            </Button>
                                        )}
                                        {bolehHapus && (
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                className="text-destructive h-8 w-8 p-0"
                                                title="Hapus"
                                                onClick={() => onHapus(baris)}
                                            >
                                                <Trash2 className="h-4 w-4" />
                                            </Button>
                                        )}
                                        {!bolehUbah && !bolehHapus && (
                                            <span className="text-muted-foreground text-xs">—</span>
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
