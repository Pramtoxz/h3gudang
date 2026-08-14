import { DialogKonfirmasi } from '@/components/dialog-konfirmasi';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { useIzin } from '@/hooks/use-izin';
import AppLayout from '@/layouts/app-layout';
import { destroy, destroyMassal, index } from '@/routes/picking/lokasi-rak';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Plus, Search } from 'lucide-react';
import { useMemo, useState } from 'react';
import { DialogDetailArea } from './_components/dialog-detail-area';
import { DialogLokasiRak } from './_components/dialog-lokasi-rak';
import { TabelArea } from './_components/tabel-area';
import type {
    BarisLokasiRak,
    BarisRingkasanArea,
    PilihanGudang,
} from './_components/tipe';

interface Props {
    ringkasanArea: BarisRingkasanArea[];
    daftarGudang: PilihanGudang[];
    daftarJenisLokasi: string[];
    detailLokasi?: BarisLokasiRak[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Master Lokasi Rak', href: index().url },
];

export default function LokasiRakIndex({
    ringkasanArea,
    daftarGudang,
    daftarJenisLokasi,
    detailLokasi,
}: Props) {
    const izin = useIzin();
    const [pencarian, setPencarian] = useState('');
    const [kelompokDetail, setKelompokDetail] =
        useState<BarisRingkasanArea | null>(null);
    const [memuatDetail, setMemuatDetail] = useState(false);
    const [dialogForm, setDialogForm] = useState(false);
    const [sedangDiubah, setSedangDiubah] = useState<BarisLokasiRak | null>(
        null,
    );
    const [akanDihapus, setAkanDihapus] = useState<BarisLokasiRak | null>(null);
    const [areaAkanDihapus, setAreaAkanDihapus] =
        useState<BarisRingkasanArea | null>(null);
    const [sedangProses, setSedangProses] = useState(false);

    const hasilSaring = useMemo(() => {
        const kunci = pencarian.trim().toLowerCase();

        if (kunci === '') {
            return ringkasanArea;
        }

        return ringkasanArea.filter(
            (baris) =>
                baris.area_rak.toLowerCase().includes(kunci) ||
                baris.nama_gudang.toLowerCase().includes(kunci),
        );
    }, [ringkasanArea, pencarian]);

    const muatDetail = (kelompok: BarisRingkasanArea) => {
        setMemuatDetail(true);
        router.get(
            index().url,
            { area: kelompok.area_rak, gudang: kelompok.kode_gudang.join(',') },
            {
                only: ['detailLokasi'],
                preserveState: true,
                preserveScroll: true,
                replace: true,
                onFinish: () => setMemuatDetail(false),
            },
        );
    };

    const bukaDetail = (kelompok: BarisRingkasanArea) => {
        setKelompokDetail(kelompok);
        muatDetail(kelompok);
    };

    const segarkanDetail = () => {
        if (kelompokDetail) {
            muatDetail(kelompokDetail);
        }
    };

    const totalLokasi = ringkasanArea.reduce(
        (jumlah, baris) => jumlah + baris.total_lokasi,
        0,
    );

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Master Lokasi Rak" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <Card>
                    <CardHeader className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <CardTitle className="text-base">
                                <SidebarTrigger className="-ml-1" />
                                Master Area/Lokasi Part
                            </CardTitle>
                            <p className="text-sm text-muted-foreground">
                                {ringkasanArea.length} kelompok area–gudang,{' '}
                                {totalLokasi} lokasi
                            </p>
                        </div>
                        {izin.tambah && (
                            <Button
                                size="sm"
                                onClick={() => {
                                    setSedangDiubah(null);
                                    setDialogForm(true);
                                }}
                            >
                                <Plus className="mr-1 h-4 w-4" />
                                Tambah Data
                            </Button>
                        )}
                    </CardHeader>

                    <CardContent className="space-y-3">
                        <div className="relative sm:max-w-sm">
                            <Search className="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground" />
                            <Input
                                value={pencarian}
                                onChange={(event) =>
                                    setPencarian(event.target.value)
                                }
                                placeholder="Cari area atau gudang"
                                className="pl-8"
                            />
                        </div>

                        <TabelArea
                            ringkasan={hasilSaring}
                            kosong={ringkasanArea.length === 0}
                            bolehHapus={izin.hapus}
                            onDetail={bukaDetail}
                            onHapusMassal={setAreaAkanDihapus}
                        />
                    </CardContent>
                </Card>
            </div>

            <DialogDetailArea
                key={
                    kelompokDetail
                        ? `${kelompokDetail.area_rak}-${kelompokDetail.nama_gudang}`
                        : 'kosong'
                }
                terbuka={kelompokDetail !== null}
                kelompok={kelompokDetail}
                daftarLokasi={detailLokasi ?? []}
                sedangMemuat={memuatDetail}
                bolehUbah={izin.ubah}
                bolehHapus={izin.hapus}
                onUbah={(baris) => {
                    setSedangDiubah(baris);
                    setDialogForm(true);
                }}
                onHapus={setAkanDihapus}
                onTutup={() => setKelompokDetail(null)}
            />

            <DialogLokasiRak
                terbuka={dialogForm}
                lokasi={sedangDiubah}
                daftarGudang={daftarGudang}
                daftarJenisLokasi={daftarJenisLokasi}
                onSukses={segarkanDetail}
                onTutup={() => setDialogForm(false)}
            />

            <DialogKonfirmasi
                terbuka={akanDihapus !== null}
                judul="Hapus Lokasi Rak"
                keterangan={
                    <>
                        Lokasi <strong>{akanDihapus?.kd_lokasi}</strong> akan
                        dihapus permanen dari daftar rak.
                    </>
                }
                labelKonfirmasi="Hapus"
                merusak
                sedangProses={sedangProses}
                onBatal={() => setAkanDihapus(null)}
                onKonfirmasi={() => {
                    if (!akanDihapus) return;

                    setSedangProses(true);
                    router.delete(destroy(akanDihapus.kd_lokasi).url, {
                        preserveScroll: true,
                        onSuccess: segarkanDetail,
                        onFinish: () => {
                            setSedangProses(false);
                            setAkanDihapus(null);
                        },
                    });
                }}
            />

            <DialogKonfirmasi
                terbuka={areaAkanDihapus !== null}
                judul="Hapus Seluruh Lokasi pada Area"
                keterangan={
                    <>
                        <strong>{areaAkanDihapus?.total_lokasi} lokasi</strong>{' '}
                        pada area <strong>{areaAkanDihapus?.area_rak}</strong>{' '}
                        di gudang {areaAkanDihapus?.nama_gudang} akan dihapus
                        permanen. Jumlah ini dihitung dengan aturan area yang
                        sama dengan yang tampil di tabel.
                    </>
                }
                labelKonfirmasi="Hapus Semua"
                merusak
                sedangProses={sedangProses}
                onBatal={() => setAreaAkanDihapus(null)}
                onKonfirmasi={() => {
                    if (!areaAkanDihapus) return;

                    setSedangProses(true);
                    router.delete(destroyMassal().url, {
                        preserveScroll: true,
                        data: {
                            area_rak: areaAkanDihapus.area_rak,
                            kode_gudang: areaAkanDihapus.kode_gudang,
                        },
                        onSuccess: () => setKelompokDetail(null),
                        onFinish: () => {
                            setSedangProses(false);
                            setAreaAkanDihapus(null);
                        },
                    });
                }}
            />
        </AppLayout>
    );
}
