import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { edit, index, show } from '@/routes/pmo/toko';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

import { FormToko, type NilaiFormToko } from './_components/form-toko';

interface AtributToko {
    kd_toko: string;
    toko: string;
    no_telp: string | null;
    npwp: string | null;
    alamat: string | null;
    kategori: string | null;
    kd_ahm: string | null;
    toko_active: boolean;
}

interface Props {
    toko: AtributToko;
}

export default function TokoEdit({ toko }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Kelola Toko', href: index().url },
        { title: toko.toko, href: show(toko.kd_toko).url },
        { title: 'Edit', href: edit(toko.kd_toko).url },
    ];

    const nilaiAwal: NilaiFormToko = {
        kd_toko: toko.kd_toko,
        toko: toko.toko,
        no_telp: toko.no_telp ?? '',
        npwp: toko.npwp ?? '',
        alamat: toko.alamat ?? '',
        kategori: toko.kategori ?? '',
        kd_ahm: toko.kd_ahm ?? '',
        toko_active: toko.toko_active,
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${toko.toko}`} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">Edit Toko: {toko.toko}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <FormToko nilaiAwal={nilaiAwal} mode="ubah" />
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
