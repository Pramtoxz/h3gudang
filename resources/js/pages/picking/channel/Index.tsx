import { DialogKonfirmasi } from '@/components/dialog-konfirmasi';
import { PaginasiTabel } from '@/components/paginasi-tabel';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useIzin } from '@/hooks/use-izin';
import AppLayout from '@/layouts/app-layout';
import { destroy, index } from '@/routes/picking/channel';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Pencil, Plus, Search, Trash2 } from 'lucide-react';
import { useMemo, useState } from 'react';
import { type BarisChannel, DialogChannel } from './_components/dialog-channel';

interface Props {
    daftarChannel: BarisChannel[];
    daftarArea: string[];
}

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Master Channel', href: index().url }];

const PER_HALAMAN = 20;
const SEMUA_AREA = 'semua';

export default function ChannelIndex({ daftarChannel, daftarArea }: Props) {
    const izin = useIzin();
    const [pencarian, setPencarian] = useState('');
    const [area, setArea] = useState(SEMUA_AREA);
    const [halaman, setHalaman] = useState(1);
    const [dialogTerbuka, setDialogTerbuka] = useState(false);
    const [sedangDiubah, setSedangDiubah] = useState<BarisChannel | null>(null);
    const [akanDihapus, setAkanDihapus] = useState<BarisChannel | null>(null);
    const [sedangProses, setSedangProses] = useState(false);

    const hasilSaring = useMemo(() => {
        const kunci = pencarian.trim().toLowerCase();

        return daftarChannel.filter((baris) => {
            const cocokArea = area === SEMUA_AREA || baris.area === area;

            if (!cocokArea) {
                return false;
            }

            if (kunci === '') {
                return true;
            }

            return (
                baris.kode_channel.toLowerCase().includes(kunci) ||
                (baris.nama_channel ?? '').toLowerCase().includes(kunci) ||
                (baris.nama_invoice ?? '').toLowerCase().includes(kunci) ||
                baris.area.toLowerCase().includes(kunci)
            );
        });
    }, [daftarChannel, pencarian, area]);

    const totalHalaman = Math.max(1, Math.ceil(hasilSaring.length / PER_HALAMAN));
    const halamanAktif = Math.min(halaman, totalHalaman);
    const awalBaris = (halamanAktif - 1) * PER_HALAMAN;

    const barisHalamanIni = useMemo(
        () => hasilSaring.slice(awalBaris, awalBaris + PER_HALAMAN),
        [hasilSaring, awalBaris],
    );

    const cari = (kunci: string) => {
        setPencarian(kunci);
        setHalaman(1);
    };

    const saringArea = (nilai: string) => {
        setArea(nilai);
        setHalaman(1);
    };

    const bukaTambah = () => {
        setSedangDiubah(null);
        setDialogTerbuka(true);
    };

    const bukaUbah = (baris: BarisChannel) => {
        setSedangDiubah(baris);
        setDialogTerbuka(true);
    };

    const hapus = () => {
        if (!akanDihapus) return;

        setSedangProses(true);
        router.delete(destroy(akanDihapus.id).url, {
            preserveScroll: true,
            onFinish: () => {
                setSedangProses(false);
                setAkanDihapus(null);
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Master Channel" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <Card>
                    <CardHeader className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <CardTitle className="text-base">Master Channel</CardTitle>
                            <p className="text-muted-foreground text-sm">
                                {daftarChannel.length} channel pada {daftarArea.length} area
                            </p>
                        </div>
                        {izin.tambah && (
                            <Button size="sm" onClick={bukaTambah}>
                                <Plus className="mr-1 h-4 w-4" />
                                Tambah Channel
                            </Button>
                        )}
                    </CardHeader>

                    <CardContent className="space-y-3">
                        <div className="flex flex-col gap-2 sm:flex-row">
                            <div className="relative sm:max-w-sm sm:flex-1">
                                <Search className="text-muted-foreground absolute top-2.5 left-2.5 h-4 w-4" />
                                <Input
                                    value={pencarian}
                                    onChange={(event) => cari(event.target.value)}
                                    placeholder="Cari kode, nama channel, atau invoice"
                                    className="pl-8"
                                />
                            </div>
                            <Select value={area} onValueChange={saringArea}>
                                <SelectTrigger className="sm:w-56">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value={SEMUA_AREA}>Semua area</SelectItem>
                                    {daftarArea.map((nama) => (
                                        <SelectItem key={nama} value={nama}>
                                            {nama}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="overflow-x-auto rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead className="w-14 text-center">No.</TableHead>
                                        <TableHead>Area</TableHead>
                                        <TableHead>Kode</TableHead>
                                        <TableHead>Nama Channel</TableHead>
                                        <TableHead>Nama Invoice</TableHead>
                                        <TableHead className="w-24 text-center">Aksi</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {barisHalamanIni.length === 0 ? (
                                        <TableRow>
                                            <TableCell
                                                colSpan={6}
                                                className="text-muted-foreground h-24 text-center"
                                            >
                                                {daftarChannel.length === 0
                                                    ? 'Belum ada data channel.'
                                                    : 'Tidak ada channel yang cocok dengan penyaringan.'}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        barisHalamanIni.map((baris, indeks) => (
                                            <TableRow key={baris.id}>
                                                <TableCell className="text-muted-foreground text-center text-xs tabular-nums">
                                                    {awalBaris + indeks + 1}
                                                </TableCell>
                                                <TableCell>
                                                    <Badge variant="secondary">{baris.area}</Badge>
                                                </TableCell>
                                                <TableCell className="font-mono text-xs">
                                                    {baris.kode_channel}
                                                </TableCell>
                                                <TableCell className="text-sm font-medium">
                                                    {baris.nama_channel || '-'}
                                                </TableCell>
                                                <TableCell className="text-sm">
                                                    {baris.nama_invoice || '-'}
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center justify-center gap-1">
                                                        {izin.ubah && (
                                                            <Button
                                                                variant="ghost"
                                                                size="sm"
                                                                className="h-8 w-8 p-0"
                                                                title="Ubah"
                                                                onClick={() => bukaUbah(baris)}
                                                            >
                                                                <Pencil className="h-4 w-4" />
                                                            </Button>
                                                        )}
                                                        {izin.hapus && (
                                                            <Button
                                                                variant="ghost"
                                                                size="sm"
                                                                className="text-destructive h-8 w-8 p-0"
                                                                title="Hapus"
                                                                onClick={() => setAkanDihapus(baris)}
                                                            >
                                                                <Trash2 className="h-4 w-4" />
                                                            </Button>
                                                        )}
                                                        {!izin.ubah && !izin.hapus && (
                                                            <span className="text-muted-foreground text-xs">
                                                                —
                                                            </span>
                                                        )}
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </div>

                        <PaginasiTabel
                            halaman={halamanAktif}
                            totalHalaman={totalHalaman}
                            totalData={hasilSaring.length}
                            perHalaman={PER_HALAMAN}
                            onPindah={setHalaman}
                        />
                    </CardContent>
                </Card>
            </div>

            <DialogChannel
                terbuka={dialogTerbuka}
                channel={sedangDiubah}
                daftarArea={daftarArea}
                onTutup={() => setDialogTerbuka(false)}
            />

            <DialogKonfirmasi
                terbuka={akanDihapus !== null}
                judul="Hapus Channel"
                keterangan={
                    <>
                        Channel <strong>{akanDihapus?.kode_channel}</strong> (
                        {akanDihapus?.nama_channel}) akan dihapus permanen. DO picking yang menunjuk
                        kode ini tidak ikut terhapus, tetapi nama channelnya tidak lagi tampil.
                    </>
                }
                labelKonfirmasi="Hapus"
                merusak
                sedangProses={sedangProses}
                onBatal={() => setAkanDihapus(null)}
                onKonfirmasi={hapus}
            />
        </AppLayout>
    );
}
