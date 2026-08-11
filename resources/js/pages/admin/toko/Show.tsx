import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, KeyRound, Pencil } from 'lucide-react';
import { useState } from 'react';

import { DialogKonfirmasi } from '@/components/dialog-konfirmasi';

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

interface UserToko {
    email: string;
    role: string;
    punya_pin: boolean;
}

interface Props {
    toko: AtributToko;
    daftarUser: UserToko[];
    pin_collection_terpasang: boolean;
}

function Keterangan({ label, nilai }: { label: string; nilai: React.ReactNode }) {
    return (
        <div>
            <p className="text-muted-foreground text-xs tracking-wide uppercase">{label}</p>
            <div className="mt-0.5 text-sm">{nilai}</div>
        </div>
    );
}

export default function TokoShow({ toko, daftarUser, pin_collection_terpasang }: Props) {
    const [konfirmasiResetPin, setKonfirmasiResetPin] = useState(false);
    const [sedangProses, setSedangProses] = useState(false);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Kelola Toko', href: '/admin/toko' },
        { title: toko.toko, href: `/admin/toko/${toko.kd_toko}` },
    ];

    const resetPin = () => {
        setSedangProses(true);
        router.post(
            `/admin/toko/${toko.kd_toko}/reset-pin`,
            {},
            {
                onFinish: () => {
                    setSedangProses(false);
                    setKonfirmasiResetPin(false);
                },
            },
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Detail ${toko.toko}`} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle className="text-base">Detail Toko: {toko.toko}</CardTitle>
                        <Button variant="outline" size="sm" asChild>
                            <Link href="/admin/toko">
                                <ArrowLeft className="mr-1 h-4 w-4" />
                                Kembali
                            </Link>
                        </Button>
                    </CardHeader>

                    <CardContent className="space-y-5">
                        <div className="grid gap-4 md:grid-cols-2">
                            <Keterangan
                                label="Kode Toko"
                                nilai={<span className="font-mono">{toko.kd_toko}</span>}
                            />
                            <Keterangan label="Nama Toko" nilai={toko.toko} />
                            <Keterangan label="No. Telepon" nilai={toko.no_telp || '-'} />
                            <Keterangan label="NPWP" nilai={toko.npwp || '-'} />
                            <Keterangan label="Kategori" nilai={toko.kategori || '-'} />
                            <Keterangan label="Kode AHM" nilai={toko.kd_ahm || '-'} />
                            <Keterangan
                                label="Status"
                                nilai={
                                    <Badge variant={toko.toko_active ? 'default' : 'secondary'}>
                                        {toko.toko_active ? 'Aktif' : 'Tidak Aktif'}
                                    </Badge>
                                }
                            />
                            <Keterangan
                                label="PIN Collection"
                                nilai={
                                    <Badge variant={pin_collection_terpasang ? 'default' : 'secondary'}>
                                        {pin_collection_terpasang ? 'Sudah Setup' : 'Belum Setup'}
                                    </Badge>
                                }
                            />
                        </div>

                        <Keterangan label="Alamat" nilai={toko.alamat || '-'} />

                        <Keterangan
                            label={`User Toko (${daftarUser.length})`}
                            nilai={
                                daftarUser.length === 0 ? (
                                    '-'
                                ) : (
                                    <ul className="divide-y rounded-md border">
                                        {daftarUser.map((user) => (
                                            <li
                                                key={user.email}
                                                className="flex flex-wrap items-center justify-between gap-2 px-3 py-2"
                                            >
                                                <span className="text-sm">{user.email}</span>
                                                <span className="flex items-center gap-1.5">
                                                    <Badge variant="outline" className="text-[10px]">
                                                        {user.role}
                                                    </Badge>
                                                    {user.punya_pin && (
                                                        <Badge variant="secondary" className="text-[10px]">
                                                            PIN aktif
                                                        </Badge>
                                                    )}
                                                </span>
                                            </li>
                                        ))}
                                    </ul>
                                )
                            }
                        />

                        <div className="flex flex-wrap gap-2 border-t pt-4">
                            <Button size="sm" asChild>
                                <Link href={`/admin/toko/${toko.kd_toko}/edit`}>
                                    <Pencil className="mr-1 h-4 w-4" />
                                    Edit Toko
                                </Link>
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                className="text-amber-600"
                                onClick={() => setKonfirmasiResetPin(true)}
                            >
                                <KeyRound className="mr-1 h-4 w-4" />
                                Reset PIN Collection
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <DialogKonfirmasi
                terbuka={konfirmasiResetPin}
                judul="Reset PIN Collection"
                keterangan={
                    <>
                        PIN Collection untuk toko <strong>{toko.toko}</strong> akan dihapus. User toko dapat
                        membuat PIN baru seperti pertama kali.
                    </>
                }
                labelKonfirmasi="Reset PIN"
                sedangProses={sedangProses}
                onBatal={() => setKonfirmasiResetPin(false)}
                onKonfirmasi={resetPin}
            />
        </AppLayout>
    );
}
