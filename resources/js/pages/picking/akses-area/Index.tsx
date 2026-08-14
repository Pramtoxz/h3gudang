import { DialogKonfirmasi } from '@/components/dialog-konfirmasi';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useIzin } from '@/hooks/use-izin';
import AppLayout from '@/layouts/app-layout';
import { destroy, index } from '@/routes/picking/akses-area';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Plus, Search, TriangleAlert } from 'lucide-react';
import { useMemo, useState } from 'react';
import { DialogAksesArea } from './_components/dialog-akses-area';
import { TabelAkses } from './_components/tabel-akses';
import { type BarisAksesArea, type PilihanUser } from './_components/tipe';

interface Props {
    daftarAkses: BarisAksesArea[];
    daftarUser: PilihanUser[];
    daftarArea: string[];
}

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Master Akses Area', href: index().url }];

export default function AksesAreaIndex({ daftarAkses, daftarUser, daftarArea }: Props) {
    const izin = useIzin();
    const [pencarian, setPencarian] = useState('');
    const [dialogTerbuka, setDialogTerbuka] = useState(false);
    const [sedangDiubah, setSedangDiubah] = useState<BarisAksesArea | null>(null);
    const [akanDihapus, setAkanDihapus] = useState<BarisAksesArea | null>(null);
    const [sedangProses, setSedangProses] = useState(false);

    const hasilSaring = useMemo(() => {
        const kunci = pencarian.trim().toLowerCase();

        if (kunci === '') {
            return daftarAkses;
        }

        return daftarAkses.filter(
            (baris) =>
                baris.email.toLowerCase().includes(kunci) ||
                (baris.username ?? '').toLowerCase().includes(kunci) ||
                (baris.area ?? '').toLowerCase().includes(kunci),
        );
    }, [daftarAkses, pencarian]);

    const tanpaAkun = daftarAkses.filter((baris) => !baris.ada_di_dms).length;

    const hapus = () => {
        if (!akanDihapus) return;

        setSedangProses(true);
        router.delete(destroy(akanDihapus.email).url, {
            preserveScroll: true,
            onFinish: () => {
                setSedangProses(false);
                setAkanDihapus(null);
            },
        });
    };

    const bukaUbah = (baris: BarisAksesArea) => {
        setSedangDiubah(baris);
        setDialogTerbuka(true);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Master Akses Area" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                {tanpaAkun > 0 && (
                    <Alert>
                        <TriangleAlert className="h-4 w-4" />
                        <AlertTitle>{tanpaAkun} baris tanpa akun login</AlertTitle>
                        <AlertDescription>
                            Emailnya tidak terdaftar sebagai pengguna, jadi barisnya tidak pernah
                            terpakai. Hapus, atau ganti emailnya dengan akun yang benar.
                        </AlertDescription>
                    </Alert>
                )}

                <Card>
                    <CardHeader className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <CardTitle className="text-base">Master Akses Area</CardTitle>
                            <p className="text-muted-foreground text-sm">
                                {daftarAkses.length} pengguna diatur aksesnya ke area rak
                            </p>
                        </div>
                        {izin.tambah && (
                            <Button
                                size="sm"
                                onClick={() => {
                                    setSedangDiubah(null);
                                    setDialogTerbuka(true);
                                }}
                            >
                                <Plus className="mr-1 h-4 w-4" />
                                Tambah Akses
                            </Button>
                        )}
                    </CardHeader>

                    <CardContent className="space-y-3">
                        <div className="relative sm:max-w-sm">
                            <Search className="text-muted-foreground absolute top-2.5 left-2.5 h-4 w-4" />
                            <Input
                                value={pencarian}
                                onChange={(event) => setPencarian(event.target.value)}
                                placeholder="Cari email, nama, atau area"
                                className="pl-8"
                            />
                        </div>

                        <TabelAkses
                            daftar={hasilSaring}
                            kosong={daftarAkses.length === 0}
                            bolehUbah={izin.ubah}
                            bolehHapus={izin.hapus}
                            onUbah={bukaUbah}
                            onHapus={setAkanDihapus}
                        />
                    </CardContent>
                </Card>
            </div>

            <DialogAksesArea
                terbuka={dialogTerbuka}
                akses={sedangDiubah}
                daftarUser={daftarUser}
                daftarArea={daftarArea}
                onTutup={() => setDialogTerbuka(false)}
            />

            <DialogKonfirmasi
                terbuka={akanDihapus !== null}
                judul="Hapus Akses Area"
                keterangan={
                    <>
                        Akses area untuk <strong>{akanDihapus?.email}</strong> akan dihapus. Setelah
                        ini pengguna tersebut tidak lagi punya area picking.
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
