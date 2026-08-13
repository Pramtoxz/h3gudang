import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { index, update } from '@/routes/pengaturan/hak-akses';
import { type BreadcrumbItem, type IzinAksi } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Loader2, Save, ShieldCheck } from 'lucide-react';
import { useMemo, useState } from 'react';

import { BarisIzinMenu, KepalaIzin, TANPA_IZIN } from './_components/baris-izin-menu';
import { DaftarUser, type UserDms } from './_components/daftar-user';

interface MenuPilihan {
    id: number;
    nama_menu: string;
    project_kode: string | null;
    project_nama: string | null;
}

type PetaIzin = Record<number, IzinAksi>;

interface Props {
    daftarUser: UserDms[];
    daftarMenu: MenuPilihan[];
    petaAkses: Record<string, PetaIzin>;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Pengaturan', href: '#' },
    { title: 'Kelola Hak Akses', href: index().url },
];

export default function HakAksesIndex({ daftarUser, daftarMenu, petaAkses }: Props) {
    const [emailTerpilih, setEmailTerpilih] = useState<string | null>(null);
    const [izinMenu, setIzinMenu] = useState<PetaIzin>({});
    const [sedangSimpan, setSedangSimpan] = useState(false);

    const jumlahMenu = useMemo(
        () =>
            Object.fromEntries(
                Object.entries(petaAkses).map(([email, peta]) => [
                    email,
                    Object.keys(peta).length,
                ]),
            ) as Record<string, number>,
        [petaAkses],
    );

    const menuPerProject = useMemo(() => {
        const kelompok = new Map<string, MenuPilihan[]>();

        daftarMenu.forEach((menu) => {
            const kunci = menu.project_nama ?? 'Lainnya';
            kelompok.set(kunci, [...(kelompok.get(kunci) ?? []), menu]);
        });

        return [...kelompok.entries()];
    }, [daftarMenu]);

    const userTerpilih = daftarUser.find((user) => user.email === emailTerpilih);
    const jumlahDipilih = Object.values(izinMenu).filter((izin) => izin.lihat).length;

    const pilihUser = (email: string) => {
        setEmailTerpilih(email);
        setIzinMenu(petaAkses[email] ?? {});
    };

    const salinDari = (email: string) => setIzinMenu(petaAkses[email] ?? {});

    const ubahIzin = (menuId: number, aksi: keyof IzinAksi) => {
        setIzinMenu((sebelum) => {
            const sekarang = sebelum[menuId] ?? TANPA_IZIN;

            if (aksi === 'lihat') {
                return sekarang.lihat
                    ? { ...sebelum, [menuId]: TANPA_IZIN }
                    : { ...sebelum, [menuId]: { ...sekarang, lihat: true } };
            }

            return {
                ...sebelum,
                [menuId]: { ...sekarang, lihat: true, [aksi]: !sekarang[aksi] },
            };
        });
    };

    const simpan = () => {
        if (!emailTerpilih) return;

        const izin = Object.entries(izinMenu)
            .filter(([, nilai]) => nilai.lihat)
            .map(([menuId, nilai]) => ({ menu_id: Number(menuId), ...nilai }));

        setSedangSimpan(true);
        router.put(
            update().url,
            { email: emailTerpilih, izin },
            { onFinish: () => setSedangSimpan(false), preserveScroll: true },
        );
    };

    const sumberSalin = Object.keys(petaAkses).filter((email) => email !== emailTerpilih);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Kelola Hak Akses" />

            <div className="grid flex-1 gap-4 p-4 lg:grid-cols-[320px_1fr]">
                <Card className="flex flex-col">
                    <CardHeader>
                        <CardTitle className="text-base">Pilih User</CardTitle>
                        <p className="text-muted-foreground text-sm">
                            {daftarUser.length} akun terdaftar di DMS
                        </p>
                    </CardHeader>
                    <CardContent className="flex-1">
                        <DaftarUser
                            daftarUser={daftarUser}
                            jumlahMenu={jumlahMenu}
                            emailTerpilih={emailTerpilih}
                            onPilih={pilihUser}
                        />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div className="min-w-0">
                            <CardTitle className="truncate text-base">
                                {userTerpilih ? userTerpilih.nama : 'Belum ada user dipilih'}
                            </CardTitle>
                            <p className="text-muted-foreground truncate text-sm">
                                {userTerpilih?.email ??
                                    'Pilih user di panel kiri untuk mengatur menunya.'}
                            </p>
                        </div>

                        {userTerpilih && !userTerpilih.is_it && sumberSalin.length > 0 && (
                            <Select onValueChange={salinDari}>
                                <SelectTrigger className="w-full sm:w-56">
                                    <SelectValue placeholder="Salin akses dari user lain" />
                                </SelectTrigger>
                                <SelectContent>
                                    {sumberSalin.map((email) => (
                                        <SelectItem key={email} value={email}>
                                            {email}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        )}
                    </CardHeader>

                    <CardContent className="space-y-4">
                        {!userTerpilih && (
                            <p className="text-muted-foreground py-10 text-center text-sm">
                                Belum ada user yang dipilih.
                            </p>
                        )}

                        {userTerpilih?.is_it && (
                            <div className="flex items-start gap-3 rounded-lg border p-4">
                                <ShieldCheck className="mt-0.5 h-5 w-5 text-green-600" />
                                <div>
                                    <p className="text-sm font-medium">Pengelola IT</p>
                                    <p className="text-muted-foreground text-sm">
                                        User ini otomatis melihat seluruh menu di semua project,
                                        dengan izin penuh untuk semua aksi, dan tidak perlu diberi
                                        akses satu per satu.
                                    </p>
                                </div>
                            </div>
                        )}

                        {userTerpilih && !userTerpilih.is_it && (
                            <>
                                {menuPerProject.map(([namaProject, menus]) => (
                                    <div key={namaProject} className="space-y-2">
                                        <div className="flex items-center gap-2">
                                            <h3 className="text-sm font-semibold">{namaProject}</h3>
                                            <Badge variant="outline" className="text-[10px]">
                                                {
                                                    menus.filter(
                                                        (menu) => izinMenu[menu.id]?.lihat,
                                                    ).length
                                                }
                                                /{menus.length}
                                            </Badge>
                                        </div>
                                        <div className="rounded-md border px-3 py-2">
                                            <KepalaIzin />
                                            {menus.map((menu) => (
                                                <BarisIzinMenu
                                                    key={menu.id}
                                                    namaMenu={menu.nama_menu}
                                                    izin={izinMenu[menu.id] ?? TANPA_IZIN}
                                                    onUbah={(aksi) => ubahIzin(menu.id, aksi)}
                                                />
                                            ))}
                                        </div>
                                    </div>
                                ))}

                                <div className="flex items-center gap-3 border-t pt-4">
                                    <Button onClick={simpan} disabled={sedangSimpan}>
                                        {sedangSimpan ? (
                                            <Loader2 className="mr-1 h-4 w-4 animate-spin" />
                                        ) : (
                                            <Save className="mr-1 h-4 w-4" />
                                        )}
                                        Simpan Hak Akses
                                    </Button>
                                    <Label className="text-muted-foreground text-xs">
                                        {jumlahDipilih} menu dipilih. Mencabut centang Lihat
                                        menghapus menu itu beserta seluruh izinnya.
                                    </Label>
                                </div>
                            </>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
