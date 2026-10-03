import { PaginasiTabel } from '@/components/paginasi-tabel';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { index, sync } from '@/routes/picking/storing-part';
import { type BreadcrumbItem, type HalamanData } from '@/types';
import { Head, router, usePoll } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { toast } from 'sonner';
import { PenyaringStoring, SEMUA_AREA, SEMUA_GUDANG } from './_components/penyaring-storing';
import { TabelStoring } from './_components/tabel-storing';
import { type BarisStoring, type GudangItem, type SaringStoring } from './_components/tipe';

interface Props {
    daftarStoring: HalamanData<BarisStoring>;
    daftarAreaRak: string[];
    daftarGudang: GudangItem[];
    areaOperator: string | null;
    isAdmin: boolean;
    saring: SaringStoring;
}

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Storing Part', href: index().url }];

const INTERVAL_REFRESH_MS = 60000;

export default function StoringPartIndex({
    daftarStoring,
    daftarAreaRak,
    daftarGudang,
    areaOperator,
    isAdmin,
    saring,
}: Props) {
    const [kunci, setKunci] = useState(saring.cari ?? '');
    const [sedangSync, setSedangSync] = useState(false);

    usePoll(INTERVAL_REFRESH_MS);

    const pindah = (perubahan: Record<string, string | number>) => {
        const nilai: Record<string, string | number> = {
            cari: saring.cari ?? '',
            area: saring.area ?? '',
            gudang: saring.gudang ?? '',
            status: saring.status ?? '',
            tgl_dari: saring.tgl_dari ?? '',
            tgl_sampai: saring.tgl_sampai ?? '',
            page: daftarStoring.current_page,
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

    const handleSync = async () => {
        setSedangSync(true);
        try {
            const tokenCsrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            const res = await fetch(sync().url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': tokenCsrf,
                },
            });

            const data = await res.json();
            if (data.success) {
                toast.success(data.message);
                router.reload();
            } else {
                toast.error(data.message || 'Gagal melakukan sinkronisasi.');
            }
        } catch (e: any) {
            toast.error(e?.message || 'Terjadi kesalahan saat sinkronisasi.');
        } finally {
            setSedangSync(false);
        }
    };

    const adaPenyaring = Boolean(
        saring.cari || saring.area || saring.gudang || saring.status || saring.tgl_dari || saring.tgl_sampai,
    );
    const awalBaris = (daftarStoring.current_page - 1) * daftarStoring.per_page;

    const penyaringAktif = Object.fromEntries(
        Object.entries({
            cari: saring.cari ?? '',
            area: saring.area ?? '',
            gudang: saring.gudang ?? '',
            status: saring.status ?? '',
            tgl_dari: saring.tgl_dari ?? '',
            tgl_sampai: saring.tgl_sampai ?? '',
            page: daftarStoring.current_page > 1 ? daftarStoring.current_page : '',
        }).filter(([, isi]) => isi !== ''),
    );

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Storing Part" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <Card>
                    <CardHeader>
                        <div className="space-y-1">
                            <CardTitle className="text-base">Data Storing Part</CardTitle>
                            <p className="text-muted-foreground text-xs">
                                Penempatan part masuk rak dari dokumen penerimaan supplier (RS).
                            </p>
                            <div className="flex flex-wrap items-center gap-1.5 pt-1">
                                <Badge variant="secondary" className="font-mono text-[11px]">
                                    Area:{' '}
                                    {isAdmin ? 'Semua Area' : areaOperator ?? 'Tidak Terdaftar'}
                                </Badge>
                                <span className="text-muted-foreground text-xs">•</span>
                                <span className="text-muted-foreground text-xs">
                                    {daftarStoring.total} total dokumen
                                </span>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <PenyaringStoring
                            kunci={kunci}
                            area={saring.area ?? SEMUA_AREA}
                            gudang={saring.gudang ?? SEMUA_GUDANG}
                            status={saring.status ?? 'default'}
                            tglDari={saring.tgl_dari ?? ''}
                            tglSampai={saring.tgl_sampai ?? ''}
                            daftarAreaRak={daftarAreaRak}
                            daftarGudang={daftarGudang}
                            adaPenyaring={adaPenyaring}
                            sedangSync={sedangSync}
                            onKunci={setKunci}
                            onArea={(area) => pindah({ area: area === SEMUA_AREA ? '' : area, page: 1 })}
                            onGudang={(gudang) => pindah({ gudang: gudang === SEMUA_GUDANG ? '' : gudang, page: 1 })}
                            onStatus={(status) => pindah({ status: status === 'default' ? '' : status, page: 1 })}
                            onTanggal={(dari, sampai) => pindah({ tgl_dari: dari, tgl_sampai: sampai, page: 1 })}
                            onReset={reset}
                            onSync={handleSync}
                        />

                        <TabelStoring
                            daftar={daftarStoring.data}
                            awalBaris={awalBaris}
                            penyaringAktif={penyaringAktif}
                        />

                        <PaginasiTabel
                            halaman={daftarStoring.current_page}
                            totalHalaman={daftarStoring.last_page}
                            totalData={daftarStoring.total}
                            perHalaman={daftarStoring.per_page}
                            onPindah={(hal: number) => pindah({ page: hal })}
                        />
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
