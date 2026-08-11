import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import type { ReactNode } from 'react';

interface SalesSupervisor {
    id: number;
    nama: string;
    kode_npk: string | null;
    jabatan: string;
    no_hp: string | null;
    aktif: boolean;
}

interface TokoDipegang {
    kd_toko: string;
    toko: string;
    peran: string;
}

interface Props {
    salesSupervisor: SalesSupervisor;
    daftarToko: TokoDipegang[];
}

function Keterangan({ label, nilai }: { label: string; nilai: ReactNode }) {
    return (
        <div>
            <p className="text-muted-foreground text-xs tracking-wide uppercase">{label}</p>
            <div className="mt-0.5 text-sm">{nilai}</div>
        </div>
    );
}

export default function SalesSpvShow({ salesSupervisor, daftarToko }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Sales & Supervisor', href: '/admin/sales-spv' },
        { title: salesSupervisor.nama, href: `/admin/sales-spv/${salesSupervisor.id}` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Detail ${salesSupervisor.nama}`} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle className="text-base">Detail: {salesSupervisor.nama}</CardTitle>
                        <Button variant="outline" size="sm" asChild>
                            <Link href="/admin/sales-spv">
                                <ArrowLeft className="mr-1 h-4 w-4" />
                                Kembali
                            </Link>
                        </Button>
                    </CardHeader>

                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-2">
                            <Keterangan label="Nama" nilai={salesSupervisor.nama} />
                            <Keterangan
                                label="Kode NPK"
                                nilai={
                                    <span className="font-mono">{salesSupervisor.kode_npk || '-'}</span>
                                }
                            />
                            <Keterangan label="No. HP" nilai={salesSupervisor.no_hp || '-'} />
                            <Keterangan
                                label="Jabatan"
                                nilai={
                                    <Badge variant={salesSupervisor.jabatan === 'spv' ? 'default' : 'secondary'}>
                                        {salesSupervisor.jabatan.toUpperCase()}
                                    </Badge>
                                }
                            />
                            <Keterangan
                                label="Status"
                                nilai={
                                    <Badge variant={salesSupervisor.aktif ? 'default' : 'secondary'}>
                                        {salesSupervisor.aktif ? 'Aktif' : 'Tidak Aktif'}
                                    </Badge>
                                }
                            />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">Toko yang Dipegang ({daftarToko.length})</CardTitle>
                        <p className="text-muted-foreground text-sm">
                            Toko yang menunjuk NPK ini sebagai salesman atau supervisor.
                        </p>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead className="w-14 text-center">No.</TableHead>
                                        <TableHead>Kode Toko</TableHead>
                                        <TableHead>Nama Toko</TableHead>
                                        <TableHead className="text-center">Sebagai</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {daftarToko.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={4} className="text-muted-foreground h-20 text-center">
                                                Belum ada toko yang menunjuk NPK ini.
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        daftarToko.map((baris, indeks) => (
                                            <TableRow key={`${baris.kd_toko}-${baris.peran}`}>
                                                <TableCell className="text-muted-foreground text-center text-xs tabular-nums">
                                                    {indeks + 1}
                                                </TableCell>
                                                <TableCell className="font-mono text-xs">{baris.kd_toko}</TableCell>
                                                <TableCell className="text-sm">{baris.toko}</TableCell>
                                                <TableCell className="text-center">
                                                    <Badge variant="outline" className="text-[10px]">
                                                        {baris.peran}
                                                    </Badge>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
