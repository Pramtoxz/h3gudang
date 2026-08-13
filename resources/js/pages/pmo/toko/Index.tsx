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
import { useIzin } from '@/hooks/use-izin';
import AppLayout from '@/layouts/app-layout';
import {
    create,
    destroy,
    edit,
    exportMethod,
    importMethod,
    index,
    resetPin,
    show,
} from '@/routes/pmo/toko';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Eye, FileDown, FileUp, KeyRound, Pencil, Plus, Search, Trash2 } from 'lucide-react';
import { useMemo, useState } from 'react';

import { DialogImportExcel } from '@/components/dialog-import-excel';
import { DialogKonfirmasi } from '@/components/dialog-konfirmasi';

interface BarisToko {
    kd_toko: string;
    toko: string;
    no_telp: string | null;
    toko_active: boolean;
}

interface Props {
    daftarToko: BarisToko[];
}

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Kelola Toko', href: index().url }];

const PER_HALAMAN = 20;

export default function TokoIndex({ daftarToko }: Props) {
    const izin = useIzin();
    const [pencarian, setPencarian] = useState('');
    const [halaman, setHalaman] = useState(1);
    const [dialogImport, setDialogImport] = useState(false);
    const [akanDihapus, setAkanDihapus] = useState<BarisToko | null>(null);
    const [akanResetPin, setAkanResetPin] = useState<BarisToko | null>(null);
    const [sedangProses, setSedangProses] = useState(false);

    const hasilSaring = useMemo(() => {
        const kunci = pencarian.trim().toLowerCase();

        if (kunci === '') {
            return daftarToko;
        }

        return daftarToko.filter(
            (baris) =>
                baris.kd_toko.toLowerCase().includes(kunci) ||
                baris.toko.toLowerCase().includes(kunci) ||
                (baris.no_telp ?? '').toLowerCase().includes(kunci),
        );
    }, [daftarToko, pencarian]);

    const totalHalaman = Math.max(1, Math.ceil(hasilSaring.length / PER_HALAMAN));
    const halamanAktif = Math.min(halaman, totalHalaman);
    const awalBaris = (halamanAktif - 1) * PER_HALAMAN;

    const barisHalamanIni = useMemo(
        () => hasilSaring.slice(awalBaris, awalBaris + PER_HALAMAN),
        [hasilSaring, awalBaris],
    );

    const cariToko = (kunci: string) => {
        setPencarian(kunci);
        setHalaman(1);
    };

    const hapus = () => {
        if (!akanDihapus) return;

        setSedangProses(true);
        router.delete(destroy(akanDihapus.kd_toko).url, {
            onFinish: () => {
                setSedangProses(false);
                setAkanDihapus(null);
            },
        });
    };

    const kirimResetPin = () => {
        if (!akanResetPin) return;

        setSedangProses(true);
        router.post(
            resetPin(akanResetPin.kd_toko).url,
            {},
            {
                onFinish: () => {
                    setSedangProses(false);
                    setAkanResetPin(null);
                },
            },
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Kelola Toko" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <Card>
                    <CardHeader className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <CardTitle className="text-base">Kelola Toko</CardTitle>
                            <p className="text-muted-foreground text-sm">
                                {daftarToko.length} toko terdaftar
                            </p>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            {izin.ubah && (
                                <Button variant="outline" size="sm" onClick={() => setDialogImport(true)}>
                                    <FileUp className="mr-1 h-4 w-4" />
                                    Import Excel
                                </Button>
                            )}
                            <Button variant="outline" size="sm" asChild>
                                <a href={exportMethod.url()}>
                                    <FileDown className="mr-1 h-4 w-4" />
                                    Export Excel
                                </a>
                            </Button>
                            {izin.tambah && (
                                <Button size="sm" asChild>
                                    <Link href={create()}>
                                        <Plus className="mr-1 h-4 w-4" />
                                        Tambah Toko
                                    </Link>
                                </Button>
                            )}
                        </div>
                    </CardHeader>

                    <CardContent className="space-y-3">
                        <div className="relative max-w-sm">
                            <Search className="text-muted-foreground absolute top-2.5 left-2.5 h-4 w-4" />
                            <Input
                                value={pencarian}
                                onChange={(event) => cariToko(event.target.value)}
                                placeholder="Cari kode, nama, atau nomor telepon"
                                className="pl-8"
                            />
                        </div>

                        <div className="overflow-x-auto rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead className="w-14 text-center">No.</TableHead>
                                        <TableHead>Kode Toko</TableHead>
                                        <TableHead>Nama Toko</TableHead>
                                        <TableHead>No. Telepon</TableHead>
                                        <TableHead className="text-center">Status</TableHead>
                                        <TableHead className="text-center">Aksi</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {barisHalamanIni.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={6} className="text-muted-foreground h-24 text-center">
                                                {daftarToko.length === 0
                                                    ? 'Belum ada data toko.'
                                                    : 'Tidak ada toko yang cocok dengan pencarian.'}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        barisHalamanIni.map((baris, indeks) => (
                                            <TableRow key={baris.kd_toko}>
                                                <TableCell className="text-muted-foreground text-center text-xs tabular-nums">
                                                    {awalBaris + indeks + 1}
                                                </TableCell>
                                                <TableCell className="font-mono text-xs font-medium">
                                                    {baris.kd_toko}
                                                </TableCell>
                                                <TableCell className="text-sm">{baris.toko}</TableCell>
                                                <TableCell className="text-sm">{baris.no_telp || '-'}</TableCell>
                                                <TableCell className="text-center">
                                                    <Badge variant={baris.toko_active ? 'default' : 'secondary'}>
                                                        {baris.toko_active ? 'Aktif' : 'Tidak Aktif'}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center justify-center gap-1">
                                                        <Button variant="ghost" size="sm" className="h-8 w-8 p-0" asChild>
                                                            <Link href={show(baris.kd_toko)} title="Detail">
                                                                <Eye className="h-4 w-4" />
                                                            </Link>
                                                        </Button>
                                                        {izin.ubah && (
                                                            <>
                                                                <Button variant="ghost" size="sm" className="h-8 w-8 p-0" asChild>
                                                                    <Link href={edit(baris.kd_toko)} title="Edit">
                                                                        <Pencil className="h-4 w-4" />
                                                                    </Link>
                                                                </Button>
                                                                <Button
                                                                    variant="ghost"
                                                                    size="sm"
                                                                    className="h-8 w-8 p-0 text-amber-600"
                                                                    title="Reset PIN Collection"
                                                                    onClick={() => setAkanResetPin(baris)}
                                                                >
                                                                    <KeyRound className="h-4 w-4" />
                                                                </Button>
                                                            </>
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
                judul="Import Data Toko"
                url={importMethod.url()}
                onTutup={() => setDialogImport(false)}
                keterangan={
                    <>
                        <p className="mb-1 font-semibold">Format berkas</p>
                        <p>
                            Sheet pertama berisi kolom:{' '}
                            <strong>NAMA TOKO, SALESMAN, SPV, KODE, EMAIL, NOHP, STATUS</strong>.
                        </p>
                        <p className="mt-1">
                            Unduh contohnya lewat tombol <strong>Export Excel</strong>. Data diperbarui
                            berdasarkan kolom KODE. STATUS diisi AKTIF atau NONAKTIF (kosong dianggap AKTIF).
                        </p>
                    </>
                }
            />

            <DialogKonfirmasi
                terbuka={akanDihapus !== null}
                judul="Hapus Toko"
                keterangan={
                    <>
                        Toko <strong>{akanDihapus?.toko}</strong> akan dihapus permanen. Toko yang masih
                        punya user terdaftar tidak dapat dihapus.
                    </>
                }
                labelKonfirmasi="Hapus"
                merusak
                sedangProses={sedangProses}
                onBatal={() => setAkanDihapus(null)}
                onKonfirmasi={hapus}
            />

            <DialogKonfirmasi
                terbuka={akanResetPin !== null}
                judul="Reset PIN Collection"
                keterangan={
                    <>
                        PIN Collection untuk toko <strong>{akanResetPin?.toko}</strong> akan dihapus. User
                        toko dapat membuat PIN baru seperti pertama kali.
                    </>
                }
                labelKonfirmasi="Reset PIN"
                sedangProses={sedangProses}
                onBatal={() => setAkanResetPin(null)}
                onKonfirmasi={kirimResetPin}
            />
        </AppLayout>
    );
}
