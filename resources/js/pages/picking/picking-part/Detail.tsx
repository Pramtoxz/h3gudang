import { ArrowLeft, CheckCircle2, Hourglass, Loader2, RotateCcw, Trash2 } from 'lucide-react';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { DialogKonfirmasi } from '@/components/dialog-konfirmasi';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { index, updateStatus, kartustok } from '@/routes/picking/picking-part';
import { useIzin } from '@/hooks/use-izin';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { DialogKartuStok, type ItemInput } from './_components/dialog-kartu-stok';
import { type BarisPart } from './_components/tipe';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

interface Props {
    fkDo: string;
    daftarPart: BarisPart[];
    isAdmin: boolean;
    isBundling: boolean;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Picking Part', href: index().url },
    { title: 'Detail DO' },
];

export default function PickingPartDetail({ fkDo, daftarPart, isAdmin, isBundling }: Props) {
    const izin = useIzin();
    const [sedangProses, setSedangProses] = useState(false);
    const [akanDihapus, setAkanDihapus] = useState<BarisPart | null>(null);
    const [modalTerbuka, setModalTerbuka] = useState(false);
    const [itemsKartuStok, setItemsKartuStok] = useState<ItemInput[]>([]);

    const partPertama = daftarPart[0] ?? null;

    const updateStatusPart = async (id: number, status: string) => {
        setSedangProses(true);

        try {
            const hasil = await new Promise<{
                success: boolean;
                message: string;
                waktu_done?: string;
                kartustok_list?: Array<{fk_do: string; fk_dealer: string; fk_part: string; lokasi_part: string; qty_part: number}>;
            }>(async (resolve) => {
                router.post(updateStatus().url, { id, status }, {
                    preserveScroll: true,
                    onFinish: () => resolve({ success: true }),
                    onSuccess: (page) => {
                        const data = page.props.flash?.success || {};
                        resolve(typeof data === 'object' ? data : { success: false });
                    },
                    onError: () => resolve({ success: false }),
                });
            });

            // Jika response dari service mengandung kartustok_list → buka modal
            if (hasil.kartustok_list && hasil.kartustok_list.length > 0) {
                setItemsKartuStok(hasil.kartustok_list.map((item, idx) => ({
                    id: id + idx,
                    fk_do: item.fk_do,
                    fk_dealer: item.fk_dealer,
                    fk_part: item.fk_part,
                    lokasi_part: item.lokasi_part,
                    qty_part: item.qty_part,
                })));
                setModalTerbuka(true);
            }
        } catch (e) {
            console.error(e);
        } finally {
            setSedangProses(false);
        }
    };

    const handleSimpanKartuStok = async (items: typeof itemsKartuStok) => {
        setSedangProses(true);
        try {
            router.post(kartustok().url, { items }, {
                preserveScroll: true,
                onSuccess: () => {
                    location.reload(); // reload setelah kartu stok tersimpan
                },
                onFinish: () => setSedangProses(false),
            });
        } catch (e) {
            console.error(e);
            setSedangProses(false);
        }
    };

    /** Hapus item — hanya untuk admin */
    const hapusItem = (id: number) => {
        if (!isAdmin || !izin.hapus) return;
        
        router.delete(`/picking/picking-part/items/${id}`, {
            preserveScroll: true,
            onSuccess: () => {
                location.reload(); // reload tabel setelah hapus
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Detail DO ${fkDo}`} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <Card>
                    <CardHeader className="flex flex-row items-start justify-between space-y-0">
                        <div className="space-y-2">
                            <CardTitle className="flex items-center gap-2 text-lg">
                                Detail Picking List - DO: <span className="font-mono">{fkDo}</span>
                                {isBundling && (
                                    <Badge variant="destructive" className="animate-pulse">
                                        URGENT
                                    </Badge>
                                )}
                            </CardTitle>

                            {partPertama && (
                                <div className="bg-muted/40 space-y-1 rounded-md border-l-4 border-red-500 p-3 text-sm">
                                    <div className="flex flex-wrap gap-x-4 gap-y-1">
                                        <span>
                                            <strong>Area Lokasi:</strong> {partPertama.area}
                                        </span>
                                        <span>
                                            <strong>Channel:</strong> {partPertama.nama_channel}
                                        </span>
                                        <span>
                                            <strong>Dealer:</strong> {partPertama.fk_dealer}
                                        </span>
                                        <span>
                                            <strong>Tanggal DO:</strong>{' '}
                                            <span className="text-red-600 font-semibold">
                                                {partPertama.tgl_picking_list_part
                                                    ? new Date(partPertama.tgl_picking_list_part).toLocaleDateString('id-ID')
                                                    : '-'}
                                            </span>
                                        </span>
                                    </div>
                                    <div className="border-t pt-1">
                                        <strong>Keterangan:</strong>{' '}
                                        <span className="text-blue-600 font-semibold">
                                            {partPertama.keterangan_picking}
                                        </span>
                                    </div>
                                </div>
                            )}
                        </div>

                        <Button variant="outline" size="sm" onClick={() => router.get(index().url)}>
                            <ArrowLeft className="mr-1 h-4 w-4" />
                            Kembali
                        </Button>
                    </CardHeader>

                    <CardContent>
                        <div className="overflow-x-auto rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead className="w-14 text-center">No.</TableHead>
                                        <TableHead>Part Number</TableHead>
                                        <TableHead>Deskripsi</TableHead>
                                        <TableHead className="text-center">Lokasi Rak</TableHead>
                                        <TableHead className="text-center">Qty Part</TableHead>
                                        <TableHead className="text-center">Qty Picking</TableHead>
                                        <TableHead className="text-center">Status</TableHead>
                                        <TableHead className="text-center">Waktu Done</TableHead>
                                        <TableHead className="w-32 text-center">Aksi</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {daftarPart.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={9} className="text-muted-foreground h-24 text-center">
                                                Tidak ada part dalam DO ini.
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        daftarPart.map((part, indeks) => {
                                            const isDoneOrFinal = part.status_picking_list === 'done';
                                            const isFinal = part.status_picking_list === 'final';

                                            return (
                                                <TableRow key={part.id}>
                                                    <TableCell className="text-muted-foreground text-center text-xs tabular-nums">{indeks + 1}</TableCell>
                                                    <TableCell className="font-mono text-sm">{part.fk_part}</TableCell>
                                                    <TableCell className="max-w-48 truncate text-sm">{part.nm_part || '-'}</TableCell>
                                                    <TableCell className="text-center text-sm">{part.lokasi_part}</TableCell>
                                                    <TableCell className="text-center text-sm tabular-nums">{part.qty_part}</TableCell>
                                                    <TableCell className="text-center text-sm tabular-nums">{part.qty_picking}</TableCell>
                                                    <TableCell className="text-center">
                                                        {isFinal ? (
                                                            <Badge className="bg-emerald-600 text-white hover:bg-emerald-600">
                                                                Final
                                                            </Badge>
                                                        ) : isDoneOrFinal ? (
                                                            <Badge className="bg-emerald-600 text-white hover:bg-emerald-600">
                                                                Done
                                                            </Badge>
                                                        ) : (
                                                            <Badge variant="destructive">
                                                                Waiting
                                                            </Badge>
                                                        )}
                                                    </TableCell>
                                                    <TableCell className="text-center text-sm">
                                                        {part.waktu_done
                                                            ? new Date(part.waktu_done).toLocaleString('id-ID')
                                                            : '-'}
                                                    </TableCell>
                                                    <TableCell>
                                                        <div className="flex items-center justify-center gap-1">
                                                            {isFinal ? (
                                                                <Badge className="bg-emerald-600 text-white hover:bg-emerald-600">Final Check</Badge>
                                                            ) : (
                                                                <>
                                                                    {/* Admin: hanya bisa Undo atau Delete */}
                                                                    {isAdmin && isDoneOrFinal ? (
                                                                        <Button
                                                                            variant="secondary"
                                                                            size="sm"
                                                                            onClick={() => updateStatusPart(part.id, 'waiting')}
                                                                            disabled={sedangProses}
                                            title="Admin dapat undo item yang sudah done"
                                        >
                                                                        {sedangProses && <Loader2 className="mr-1 h-4 w-4 animate-spin" />}
                                                                        <RotateCcw className="h-4 w-4" />
                                                                        Undo
                                                                    </Button>
                                                                    ) : isAdmin ? (
                                                                        <span className="text-muted-foreground text-xs italic">
                                                                            Tunggu operator mark done
                                                                        </span>
                                                                    ) : (
                                                                        /* Operator HP nanti: bisa Mark Done / Undo */
                                                                        <Button
                                                                            variant={isDoneOrFinal ? 'secondary' : 'default'}
                                                                            size="sm"
                                                                            onClick={() => updateStatusPart(part.id, isDoneOrFinal ? 'waiting' : 'done')}
                                                                            disabled={sedangProses}
                                                                        >
                                                                            {sedangProses && <Loader2 className="mr-1 h-4 w-4 animate-spin" />}
                                                                            {isDoneOrFinal ? (
                                                                                <>
                                                                                    <RotateCcw className="h-4 w-4" />
                                                                                    Undo
                                                                                </>
                                                                            ) : (
                                                                                <>
                                                                                    <CheckCircle2 className="h-4 w-4" />
                                                                                    Mark Done
                                                                                </>
                                                                            )}
                                                                        </Button>
                                                                    )}
                                                                </>
                                                            )}
                                                            
                                                            {/* Hapus button — hanya untuk admin */}
                                                            {isAdmin && izin.hapus && (
                                                                <Button
                                                                    variant="ghost"
                                                                    size="sm"
                                                                    className="text-destructive h-8 w-8 p-0"
                                                                    title="Hapus item ini (Admin)"
                                    onClick={() => setAkanDihapus(part)}
                                >
                                                                    <Trash2 className="h-4 w-4" />
                                                                </Button>
                                                            )}
                                                        </div>
                                                    </TableCell>
                                                </TableRow>
                                            );
                                        })
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <DialogKonfirmasi
                terbuka={akanDihapus !== null}
                judul="Hapus Item DO"
                keterangan={
                    <>
                        Part <strong>{akanDihapus?.fk_part}</strong> di lokasi <strong>{akanDihapus?.lokasi_part}</strong> akan dihapus permanen.
                    </>
                }
                labelKonfirmasi="Hapus Item"
                merusak
                sedangProses={sedangProses}
                onBatal={() => setAkanDihapus(null)}
                onKonfirmasi={() => {
                    if (!akanDihapus) return;
                    hapusItem(akanDihapus.id);
                    setAkanDihapus(null);
                }}
            />

            <DialogKartuStok
                terbuka={modalTerbuka}
                items={itemsKartuStok}
                onSimpan={handleSimpanKartuStok}
                sedangProses={sedangProses}
            />
        </AppLayout>
    );
}
