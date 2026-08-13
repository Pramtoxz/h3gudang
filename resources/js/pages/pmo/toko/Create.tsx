import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { create, index } from '@/routes/pmo/toko';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

import { FormToko, type NilaiFormToko } from './_components/form-toko';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Kelola Toko', href: index().url },
    { title: 'Tambah Toko', href: create().url },
];

const nilaiAwal: NilaiFormToko = {
    kd_toko: '',
    toko: '',
    no_telp: '',
    npwp: '',
    alamat: '',
    kategori: '',
    kd_ahm: '',
    toko_active: true,
};

export default function TokoCreate() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tambah Toko" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">Tambah Toko</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <FormToko nilaiAwal={nilaiAwal} mode="tambah" />
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
