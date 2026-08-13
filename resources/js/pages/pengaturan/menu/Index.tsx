import { DialogKonfirmasi } from '@/components/dialog-konfirmasi';
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
import { destroy, index } from '@/routes/pengaturan/menu';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';

import { DialogMenu, type BarisMenu } from './_components/dialog-menu';

interface ProjectPilihan {
    id: number;
    kode: string;
    nama: string;
    aktif: boolean;
}

interface Props {
    daftarProject: ProjectPilihan[];
    daftarMenu: BarisMenu[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Pengaturan', href: '#' },
    { title: 'Kelola Menu', href: index().url },
];

export default function MenuIndex({ daftarProject, daftarMenu }: Props) {
    const [dialogTerbuka, setDialogTerbuka] = useState(false);
    const [menuDiedit, setMenuDiedit] = useState<BarisMenu | null>(null);
    const [parentBaru, setParentBaru] = useState<number | null>(null);
    const [akanDihapus, setAkanDihapus] = useState<BarisMenu | null>(null);
    const [sedangProses, setSedangProses] = useState(false);

    const namaProject = (id: number | null): string =>
        id === null ? 'Global' : (daftarProject.find((item) => item.id === id)?.nama ?? '-');

    const bukaTambah = (parentId: number | null = null) => {
        setMenuDiedit(null);
        setParentBaru(parentId);
        setDialogTerbuka(true);
    };

    const bukaEdit = (menu: BarisMenu) => {
        setMenuDiedit(menu);
        setParentBaru(null);
        setDialogTerbuka(true);
    };

    const hapus = () => {
        if (!akanDihapus) return;

        setSedangProses(true);
        router.delete(destroy(akanDihapus.id).url, {
            preserveScroll: true,
            onFinish: () => {
                setSedangProses(false);
                setAkanDihapus(null);
            },
        });
    };

    const baris = (menu: BarisMenu, tingkat = 0) => (
        <TableRow key={menu.id}>
            <TableCell>
                <span style={{ paddingLeft: tingkat * 20 }} className="text-sm font-medium">
                    {tingkat > 0 && <span className="text-muted-foreground mr-1">└</span>}
                    {menu.nama_menu}
                </span>
            </TableCell>
            <TableCell className="text-sm">{namaProject(menu.project_id)}</TableCell>
            <TableCell className="text-muted-foreground font-mono text-xs">
                {menu.route ?? menu.url ?? '-'}
            </TableCell>
            <TableCell className="text-center text-xs tabular-nums">{menu.urutan}</TableCell>
            <TableCell className="text-center">
                <div className="flex items-center justify-center gap-1">
                    <Badge variant={menu.status_aktif ? 'default' : 'secondary'}>
                        {menu.status_aktif ? 'Aktif' : 'Nonaktif'}
                    </Badge>
                    {menu.khusus_it && (
                        <Badge variant="outline" className="text-[10px]">
                            IT
                        </Badge>
                    )}
                </div>
            </TableCell>
            <TableCell>
                <div className="flex items-center justify-center gap-1">
                    {tingkat === 0 && (
                        <Button
                            variant="outline"
                            size="sm"
                            className="h-7 text-xs"
                            onClick={() => bukaTambah(menu.id)}
                        >
                            <Plus className="mr-1 h-3 w-3" />
                            Sub
                        </Button>
                    )}
                    <Button
                        variant="ghost"
                        size="sm"
                        className="h-8 w-8 p-0"
                        onClick={() => bukaEdit(menu)}
                    >
                        <Pencil className="h-4 w-4" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="sm"
                        className="text-destructive h-8 w-8 p-0"
                        onClick={() => setAkanDihapus(menu)}
                    >
                        <Trash2 className="h-4 w-4" />
                    </Button>
                </div>
            </TableCell>
        </TableRow>
    );

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Kelola Menu" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle className="text-base">Kelola Menu</CardTitle>
                            <p className="text-muted-foreground text-sm">
                                Menu bertanda IT tidak bisa diberikan ke user biasa.
                            </p>
                        </div>
                        <Button size="sm" onClick={() => bukaTambah()}>
                            <Plus className="mr-1 h-4 w-4" />
                            Tambah Menu
                        </Button>
                    </CardHeader>

                    <CardContent>
                        <div className="overflow-x-auto rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Nama Menu</TableHead>
                                        <TableHead>Project</TableHead>
                                        <TableHead>Route / URL</TableHead>
                                        <TableHead className="text-center">Urutan</TableHead>
                                        <TableHead className="text-center">Status</TableHead>
                                        <TableHead className="text-center">Aksi</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {daftarMenu.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={6} className="text-muted-foreground h-24 text-center">
                                                Belum ada menu.
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        daftarMenu.flatMap((menu) => [
                                            baris(menu),
                                            ...(menu.children ?? []).map((anak) => baris(anak, 1)),
                                        ])
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <DialogMenu
                terbuka={dialogTerbuka}
                menu={menuDiedit}
                parentId={parentBaru}
                daftarProject={daftarProject}
                onTutup={() => setDialogTerbuka(false)}
            />

            <DialogKonfirmasi
                terbuka={akanDihapus !== null}
                judul="Hapus Menu"
                keterangan={
                    <>
                        Menu <strong>{akanDihapus?.nama_menu}</strong> akan dihapus beserta sub-menu
                        dan seluruh hak akses yang menunjuknya.
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
