import { PaginasiTabel } from '@/components/paginasi-tabel';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Loader2, Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { LencanaAktif } from './lencana-status';
import type { BarisLokasiRak, BarisRingkasanArea } from './tipe';

interface DialogDetailAreaProps {
    terbuka: boolean;
    kelompok: BarisRingkasanArea | null;
    daftarLokasi: BarisLokasiRak[];
    sedangMemuat: boolean;
    bolehUbah: boolean;
    bolehHapus: boolean;
    onUbah: (baris: BarisLokasiRak) => void;
    onHapus: (baris: BarisLokasiRak) => void;
    onTutup: () => void;
}

const PER_HALAMAN = 10;

export function DialogDetailArea({
    terbuka,
    kelompok,
    daftarLokasi,
    sedangMemuat,
    bolehUbah,
    bolehHapus,
    onUbah,
    onHapus,
    onTutup,
}: DialogDetailAreaProps) {
    const [halaman, setHalaman] = useState(1);

    const totalHalaman = Math.max(1, Math.ceil(daftarLokasi.length / PER_HALAMAN));
    const halamanAktif = Math.min(halaman, totalHalaman);
    const awalBaris = (halamanAktif - 1) * PER_HALAMAN;
    const barisHalamanIni = daftarLokasi.slice(awalBaris, awalBaris + PER_HALAMAN);

    return (
        <Dialog open={terbuka} onOpenChange={(status) => !status && onTutup()}>
            <DialogContent className="sm:max-w-3xl">
                <DialogHeader>
                    <DialogTitle>Detail Area Lokasi</DialogTitle>
                    <DialogDescription>
                        Area <strong>{kelompok?.area_rak}</strong> — gudang{' '}
                        <strong>{kelompok?.nama_gudang}</strong> — {kelompok?.total_lokasi} lokasi
                    </DialogDescription>
                </DialogHeader>

                {sedangMemuat ? (
                    <div className="text-muted-foreground flex h-40 items-center justify-center gap-2 text-sm">
                        <Loader2 className="h-4 w-4 animate-spin" />
                        Memuat lokasi…
                    </div>
                ) : (
                    <div className="space-y-3">
                        <div className="max-h-[50vh] overflow-auto rounded-md border">
                            <Table>
                                <TableHeader className="bg-background sticky top-0">
                                    <TableRow>
                                        <TableHead className="w-14 text-center">No.</TableHead>
                                        <TableHead>Kode Lokasi</TableHead>
                                        <TableHead>Jenis</TableHead>
                                        <TableHead className="text-right">Kapasitas</TableHead>
                                        <TableHead>Status</TableHead>
                                        {(bolehUbah || bolehHapus) && (
                                            <TableHead className="w-24 text-center">Aksi</TableHead>
                                        )}
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {barisHalamanIni.length === 0 ? (
                                        <TableRow>
                                            <TableCell
                                                colSpan={6}
                                                className="text-muted-foreground h-20 text-center"
                                            >
                                                Tidak ada lokasi pada kelompok ini.
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        barisHalamanIni.map((baris, indeks) => (
                                            <TableRow key={baris.kd_lokasi}>
                                                <TableCell className="text-muted-foreground text-center text-xs tabular-nums">
                                                    {awalBaris + indeks + 1}
                                                </TableCell>
                                                <TableCell className="font-mono text-xs font-medium">
                                                    {baris.kd_lokasi}
                                                </TableCell>
                                                <TableCell className="text-sm">
                                                    {baris.jenis_lokasi || '-'}
                                                </TableCell>
                                                <TableCell className="text-right text-sm tabular-nums">
                                                    {baris.kapasitas ?? '-'}
                                                </TableCell>
                                                <TableCell>
                                                    <LencanaAktif
                                                        aktif={baris.lokasi_part_active}
                                                    />
                                                </TableCell>
                                                {(bolehUbah || bolehHapus) && (
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
                                                        </div>
                                                    </TableCell>
                                                )}
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </div>

                        <PaginasiTabel
                            halaman={halamanAktif}
                            totalHalaman={totalHalaman}
                            totalData={daftarLokasi.length}
                            perHalaman={PER_HALAMAN}
                            onPindah={setHalaman}
                        />
                    </div>
                )}

                <DialogFooter>
                    <Button variant="outline" onClick={onTutup}>
                        Tutup
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
