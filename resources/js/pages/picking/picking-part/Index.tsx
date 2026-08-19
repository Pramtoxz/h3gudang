import { DialogKonfirmasi } from '@/components/dialog-konfirmasi';
import { PaginasiTabel } from '@/components/paginasi-tabel';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useIzin } from '@/hooks/use-izin';
import AppLayout from '@/layouts/app-layout';
import { destroy, index } from '@/routes/picking/picking-part';
import { type BreadcrumbItem, type HalamanData } from '@/types';
import { Head, router, usePoll } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { PenyaringDo, SEMUA_AREA } from './_components/penyaring-do';
import { TabelDo } from './_components/tabel-do';
import { STATUS_BAWAAN, type BarisDo, type SaringDo } from './_components/tipe';

interface Props {
    daftarDo: HalamanData<BarisDo>;
    daftarAreaChannel: string[];
    areaOperator: string | null;
    saring: SaringDo;
}

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Picking Part', href: index().url }];

const INTERVAL_REFRESH_MS = 30000;

export default function PickingPartIndex({
    daftarDo,
    daftarAreaChannel,
    areaOperator,
    saring,
}: Props) {
    const izin = useIzin();
    const [kunci, setKunci] = useState(saring.cari ?? '');
    const [akanDihapus, setAkanDihapus] = useState<BarisDo | null>(null);
    const [sedangProses, setSedangProses] = useState(false);

    /*
     * Data di layar selalu hasil sinkronisasi cron aaPanel. usePoll mengambil
     * ulang props pada URL yang sedang dibuka — termasuk query string penyaring
     * dan halaman — sehingga operator tidak pernah perlu me-refresh browser.
     * preserveState dan preserveScroll sudah bawaan dari router.reload.
     */
    usePoll(INTERVAL_REFRESH_MS);

    const pindah = (perubahan: Record<string, string | number>) => {
        const nilai: Record<string, string | number> = {
            cari: saring.cari ?? '',
            area: saring.area ?? '',
            status: saring.status ?? '',
            tgl_dari: saring.tgl_dari ?? '',
            tgl_sampai: saring.tgl_sampai ?? '',
            page: daftarDo.current_page,
            ...perubahan,
        };

        router.get(
            index().url,
            Object.fromEntries(Object.entries(nilai).filter(([, isi]) => isi !== '')),
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    useEffect(() => {
        if (kunci === (saring.cari ?? '')) return;

        const penunda = setTimeout(() => pindah({ cari: kunci, page: 1 }), 400);

        return () => clearTimeout(penunda);
    }, [kunci]);

    const reset = () => {
        setKunci('');
        router.get(index().url, {}, { preserveState: true, preserveScroll: true, replace: true });
    };

    const adaPenyaring = Boolean(
        saring.cari || saring.area || saring.status || saring.tgl_dari || saring.tgl_sampai,
    );
    const awalBaris = (daftarDo.current_page - 1) * daftarDo.per_page;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Picking Part" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <Card>
                    <CardHeader>
                        <div className="space-y-1">
                            <CardTitle className="text-base">Data Picking Part</CardTitle>
                            <div className="flex items-center gap-2">
                                {areaOperator ? (
                                    <Badge variant="secondary">Area: {areaOperator}</Badge>
                                ) : (
                                    <Badge className="bg-sky-600 text-white hover:bg-sky-600">
                                        Semua area
                                    </Badge>
                                )}
                                <span className="text-muted-foreground text-sm">
                                    {daftarDo.total} DO
                                </span>
                            </div>
                        </div>
                    </CardHeader>

                    <CardContent className="space-y-3">
                        <PenyaringDo
                            kunci={kunci}
                            area={saring.area ?? SEMUA_AREA}
                            status={saring.status ?? STATUS_BAWAAN}
                            tglDari={saring.tgl_dari ?? ''}
                            tglSampai={saring.tgl_sampai ?? ''}
                            daftarAreaChannel={daftarAreaChannel}
                            adaPenyaring={adaPenyaring}
                            onKunci={setKunci}
                            onArea={(nilai) =>
                                pindah({ area: nilai === SEMUA_AREA ? '' : nilai, page: 1 })
                            }
                            onStatus={(nilai) =>
                                pindah({
                                    status: nilai,
                                    tgl_dari: '',
                                    tgl_sampai: '',
                                    page: 1,
                                })
                            }
                            onTanggal={(dari, sampai) =>
                                pindah({ tgl_dari: dari, tgl_sampai: sampai, page: 1 })
                            }
                            onReset={reset}
                        />

                        <TabelDo
                            daftar={daftarDo.data}
                            awalBaris={awalBaris}
                            bolehHapus={izin.hapus}
                            onHapus={setAkanDihapus}
                        />

                        <PaginasiTabel
                            halaman={daftarDo.current_page}
                            totalHalaman={daftarDo.last_page}
                            totalData={daftarDo.total}
                            perHalaman={daftarDo.per_page}
                            onPindah={(halaman) => pindah({ page: halaman })}
                        />
                    </CardContent>
                </Card>
            </div>

            <DialogKonfirmasi
                terbuka={akanDihapus !== null}
                judul="Hapus Seluruh Item DO"
                keterangan={
                    <>
                        Seluruh <strong>{akanDihapus?.total_items} item</strong> DO{' '}
                        <strong>{akanDihapus?.fk_do}</strong> akan dihapus, termasuk yang sudah
                        berstatus Done dan Final. Data akan muncul kembali pada sinkronisasi
                        berikutnya bila picking list-nya masih Ready For Scan.
                    </>
                }
                labelKonfirmasi="Hapus Semua"
                merusak
                sedangProses={sedangProses}
                onBatal={() => setAkanDihapus(null)}
                onKonfirmasi={() => {
                    if (!akanDihapus) return;

                    setSedangProses(true);
                    router.delete(destroy().url, {
                        data: { fk_do: akanDihapus.fk_do },
                        preserveScroll: true,
                        onFinish: () => {
                            setSedangProses(false);
                            setAkanDihapus(null);
                        },
                    });
                }}
            />
        </AppLayout>
    );
}
