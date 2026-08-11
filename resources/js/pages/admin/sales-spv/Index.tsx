import { DialogImportExcel } from '@/components/dialog-import-excel';
import { DialogKonfirmasi } from '@/components/dialog-konfirmasi';
import { PaginasiTabel } from '@/components/paginasi-tabel';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Eye, FileDown, FileUp, Search, Trash2 } from 'lucide-react';
import { useMemo, useState } from 'react';

interface BarisSalesSpv {
    id: number;
    nama: string;
    kode_npk: string | null;
    jabatan: string;
    no_hp: string | null;
    aktif: boolean;
}

interface Props {
    daftarSalesSpv: BarisSalesSpv[];
}

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Sales & Supervisor', href: '/admin/sales-spv' }];

const PER_HALAMAN = 20;

export default function SalesSpvIndex({ daftarSalesSpv }: Props) {
    const [pencarian, setPencarian] = useState('');
    const [halaman, setHalaman] = useState(1);
    const [dialogImport, setDialogImport] = useState(false);
    const [akanDihapus, setAkanDihapus] = useState<BarisSalesSpv | null>(null);
    const [sedangProses, setSedangProses] = useState(false);

    const hasilSaring = useMemo(() => {
        const kunci = pencarian.trim().toLowerCase();

        if (kunci === '') {
            return daftarSalesSpv;
        }

        return daftarSalesSpv.filter(
            (baris) =>
                baris.nama.toLowerCase().includes(kunci) ||
                (baris.kode_npk ?? '').toLowerCase().includes(kunci) ||
                baris.jabatan.toLowerCase().includes(kunci) ||
                (baris.no_hp ?? '').toLowerCase().includes(kunci),
        );
    }, [daftarSalesSpv, pencarian]);

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

    const hapus = () => {
        if (!akanDihapus) return;

        setSedangProses(true);
        router.delete(`/admin/sales-spv/${akanDihapus.id}`, {
            onFinish: () => {
                setSedangProses(false);
                setAkanDihapus(null);
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Sales & Supervisor" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <Card>
                    <CardHeader className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <CardTitle className="text-base">Sales &amp; Supervisor</CardTitle>
                            <p className="text-muted-foreground text-sm">
                                {daftarSalesSpv.length} data terdaftar
                            </p>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <Button variant="outline" size="sm" onClick={() => setDialogImport(true)}>
                                <FileUp className="mr-1 h-4 w-4" />
                                Import Excel
                            </Button>
                            <Button variant="outline" size="sm" asChild>
                                <a href="/admin/sales-spv/export">
                                    <FileDown className="mr-1 h-4 w-4" />
                                    Export Excel
                                </a>
                            </Button>
                        </div>
                    </CardHeader>

                    <CardContent className="space-y-3">
                        <div className="relative max-w-sm">
                            <Search className="text-muted-foreground absolute top-2.5 left-2.5 h-4 w-4" />
                            <Input
                                value={pencarian}
                                onChange={(event) => cari(event.target.value)}
                                placeholder="Cari nama, NPK, atau jabatan"
                                className="pl-8"
                            />
                        </div>

                        <div className="overflow-x-auto rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead className="w-14 text-center">No.</TableHead>
                                        <TableHead>Nama</TableHead>
                                        <TableHead>Kode NPK</TableHead>
                                        <TableHead>Jabatan</TableHead>
                                        <TableHead>No. HP</TableHead>
                                        <TableHead className="text-center">Status</TableHead>
                                        <TableHead className="text-center">Aksi</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {barisHalamanIni.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={7} className="text-muted-foreground h-24 text-center">
                                                {daftarSalesSpv.length === 0
                                                    ? 'Belum ada data sales & supervisor.'
                                                    : 'Tidak ada data yang cocok dengan pencarian.'}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        barisHalamanIni.map((baris, indeks) => (
                                            <TableRow key={baris.id}>
                                                <TableCell className="text-muted-foreground text-center text-xs tabular-nums">
                                                    {awalBaris + indeks + 1}
                                                </TableCell>
                                                <TableCell className="text-sm font-medium">{baris.nama}</TableCell>
                                                <TableCell className="font-mono text-xs">
                                                    {baris.kode_npk || '-'}
                                                </TableCell>
                                                <TableCell>
                                                    <Badge variant={baris.jabatan === 'spv' ? 'default' : 'secondary'}>
                                                        {baris.jabatan.toUpperCase()}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell className="text-sm">{baris.no_hp || '-'}</TableCell>
                                                <TableCell className="text-center">
                                                    <Badge variant={baris.aktif ? 'default' : 'secondary'}>
                                                        {baris.aktif ? 'Aktif' : 'Tidak Aktif'}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center justify-center gap-1">
                                                        <Button variant="ghost" size="sm" className="h-8 w-8 p-0" asChild>
                                                            <Link href={`/admin/sales-spv/${baris.id}`} title="Detail">
                                                                <Eye className="h-4 w-4" />
                                                            </Link>
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            className="text-destructive h-8 w-8 p-0"
                                                            title="Hapus"
                                                            onClick={() => setAkanDihapus(baris)}
                                                        >
                                                            <Trash2 className="h-4 w-4" />
                                                        </Button>
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

            <DialogImportExcel
                terbuka={dialogImport}
                judul="Import Data Sales & Supervisor"
                url="/admin/sales-spv/import"
                onTutup={() => setDialogImport(false)}
                keterangan={
                    <>
                        <p className="mb-1 font-semibold">Format berkas</p>
                        <p>
                            Kolom: <strong>NAMA, JABATAN, NOHP, KODE</strong>. Data diperbarui berdasarkan
                            kolom KODE, jabatan diisi <strong>spv</strong> atau <strong>salesman</strong>.
                        </p>
                        <p className="mt-1">
                            Import hanya memperbarui data sales &amp; supervisor — akun login tidak dibuat
                            maupun diubah.
                        </p>
                    </>
                }
            />

            <DialogKonfirmasi
                terbuka={akanDihapus !== null}
                judul="Hapus Data"
                keterangan={
                    <>
                        Data <strong>{akanDihapus?.nama}</strong> akan dihapus permanen. Toko yang menunjuk
                        NPK ini tidak ikut terhapus, tetapi relasinya jadi kosong.
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
