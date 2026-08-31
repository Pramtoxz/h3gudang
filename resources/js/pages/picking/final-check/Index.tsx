import { PaginasiTabel } from '@/components/paginasi-tabel';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { index } from '@/routes/picking/final-check';
import { type BreadcrumbItem, type HalamanData } from '@/types';
import { Head, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { Penyaring } from './_components/penyaring';
import { TabelDo } from './_components/tabel-do';
import { SEMUA_AREA, STATUS_BAWAAN, type BarisDoFinal, type SaringFinalCheck } from './_components/tipe';

interface Props {
    daftarDo: HalamanData<BarisDoFinal>;
    daftarAreaChannel: string[];
    saring: SaringFinalCheck;
}

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Final Check', href: index().url }];

export default function FinalCheckIndex({ daftarDo, daftarAreaChannel, saring }: Props) {
    const [kunci, setKunci] = useState(saring.cari ?? '');

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

    const penyaringAktif = Object.fromEntries(
        Object.entries({
            cari: saring.cari ?? '',
            area: saring.area ?? '',
            status: saring.status ?? '',
            tgl_dari: saring.tgl_dari ?? '',
            tgl_sampai: saring.tgl_sampai ?? '',
            page: daftarDo.current_page > 1 ? daftarDo.current_page : '',
        }).filter(([, isi]) => isi !== ''),
    );

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Final Check" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <Card>
                    <CardHeader>
                        <div className="space-y-1">
                            <CardTitle className="text-base">Final Check</CardTitle>
                            <p className="text-muted-foreground text-sm">
                                {daftarDo.total} DO — hanya DO yang seluruh part-nya sudah selesai
                                diambil operator yang muncul di sini.
                            </p>
                        </div>
                    </CardHeader>

                    <CardContent className="space-y-3">
                        <Penyaring
                            kunci={kunci}
                            area={saring.area ?? SEMUA_AREA}
                            status={saring.status ?? STATUS_BAWAAN}
                            tglDari={saring.tgl_dari ?? ''}
                            tglSampai={saring.tgl_sampai ?? ''}
                            daftarAreaChannel={daftarAreaChannel}
                            adaPenyaring={adaPenyaring}
                            onKunci={setKunci}
                            onArea={(nilai) => pindah({ area: nilai === SEMUA_AREA ? '' : nilai, page: 1 })}
                            onStatus={(nilai) =>
                                pindah({ status: nilai, tgl_dari: '', tgl_sampai: '', page: 1 })
                            }
                            onTanggal={(dari, sampai) =>
                                pindah({ tgl_dari: dari, tgl_sampai: sampai, page: 1 })
                            }
                            onReset={reset}
                        />

                        <TabelDo
                            daftar={daftarDo.data}
                            awalBaris={awalBaris}
                            penyaringAktif={penyaringAktif}
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
        </AppLayout>
    );
}
