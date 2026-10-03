import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { detail, simpan, tandaiSemua } from '@/routes/picking/storing-part';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { ArrowLeft, Check, CheckCheck, Loader2 } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import { type BarisItemStoring, type DokumenStoring } from './_components/tipe';

interface Props {
    fkDo: string;
    dokumen: DokumenStoring;
    daftarPart: BarisItemStoring[];
    areaOperator: string | null;
    isAdmin: boolean;
    urlKembali: string;
}

export default function StoringPartDetail({
    fkDo,
    dokumen,
    daftarPart,
    areaOperator,
    isAdmin,
    urlKembali,
}: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Storing Part', href: urlKembali },
        { title: `Dokumen ${fkDo}`, href: detail({ query: { do: fkDo } }).url },
    ];

    const [itemDipilih, setItemDipilih] = useState<BarisItemStoring | null>(null);
    const [inputQty, setInputQty] = useState<number>(0);
    const [sedangSimpan, setSedangSimpan] = useState(false);
    const [sedangTandaiSemua, setSedangTandaiSemua] = useState(false);

    const bukaModalInput = (part: BarisItemStoring) => {
        setItemDipilih(part);
        setInputQty(part.qty_diterima);
    };

    const tokenCsrf = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    const handleSimpanItem = async () => {
        if (!itemDipilih) return;

        setSedangSimpan(true);
        try {
            const res = await fetch(simpan().url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': tokenCsrf(),
                },
                body: JSON.stringify({
                    fk_do: itemDipilih.fk_do,
                    no_part: itemDipilih.no_part,
                    kode_rak: itemDipilih.kode_rak,
                    qty_masuk: inputQty,
                }),
            });

            const data = await res.json();
            if (data.success) {
                toast.success(data.message);
                setItemDipilih(null);
                router.reload();
            } else {
                toast.error(data.message || 'Gagal menyimpan part ke rak.');
            }
        } catch (e: any) {
            toast.error(e?.message || 'Terjadi kesalahan sistem.');
        } finally {
            setSedangSimpan(false);
        }
    };

    const handleTandaiSemua = async () => {
        if (!confirm('Tandai semua part yang belum selesai sebagai selesai (Qty Masuk = Qty Diterima)?')) {
            return;
        }

        setSedangTandaiSemua(true);
        try {
            const res = await fetch(tandaiSemua().url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': tokenCsrf(),
                },
                body: JSON.stringify({ fk_do: fkDo }),
            });

            const data = await res.json();
            if (data.success) {
                toast.success(data.message);
                router.reload();
            } else {
                toast.error(data.message || 'Gagal memproses data.');
            }
        } catch (e: any) {
            toast.error(e?.message || 'Terjadi kesalahan.');
        } finally {
            setSedangTandaiSemua(false);
        }
    };

    const adaYangBelumDone = daftarPart.some((p) => !p.status_masuk);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Detail Storing ${fkDo}`} />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <Card>
                    <CardHeader className="flex flex-row items-start justify-between space-y-0">
                        <div className="space-y-2">
                            <CardTitle className="flex items-center gap-2 text-base sm:text-lg">
                                Detail Storing: <span className="font-mono font-bold">{fkDo}</span>
                            </CardTitle>

                            <div className="bg-muted/40 space-y-1 rounded-md border-l-4 border-emerald-500 p-3 text-xs sm:text-sm">
                                <div className="flex flex-wrap gap-x-5 gap-y-1">
                                    <span>
                                        <strong>Gudang:</strong> {dokumen.nm_gudang_part || dokumen.fk_gudang || '-'}
                                    </span>
                                    <span>
                                        <strong>Tanggal:</strong>{' '}
                                        {dokumen.tgl_kartu
                                            ? new Date(dokumen.tgl_kartu).toLocaleDateString('id-ID')
                                            : '-'}
                                    </span>
                                    <span>
                                        <strong>Progres:</strong> {dokumen.done_items} / {dokumen.total_items} Part Selesai
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div className="flex items-center gap-2">
                            {adaYangBelumDone && (
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={handleTandaiSemua}
                                    disabled={sedangTandaiSemua}
                                    className="border-emerald-600 text-emerald-600 hover:bg-emerald-50"
                                >
                                    {sedangTandaiSemua ? (
                                        <Loader2 className="mr-1 h-4 w-4 animate-spin" />
                                    ) : (
                                        <CheckCheck className="mr-1 h-4 w-4" />
                                    )}
                                    Ceklis Semua
                                </Button>
                            )}

                            <Button variant="outline" size="sm" onClick={() => router.get(urlKembali)}>
                                <ArrowLeft className="mr-1 h-4 w-4" />
                                Kembali
                            </Button>
                        </div>
                    </CardHeader>

                    <CardContent>
                        <div className="overflow-x-auto rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead className="w-14 text-center">No.</TableHead>
                                        <TableHead>Nomor Part</TableHead>
                                        <TableHead>Deskripsi Part</TableHead>
                                        <TableHead>Nomor Doos</TableHead>
                                        <TableHead className="text-center">Kode Rak</TableHead>
                                        <TableHead className="text-right">Qty Diterima</TableHead>
                                        <TableHead className="text-right">Qty Masuk</TableHead>
                                        <TableHead className="text-center">Status</TableHead>
                                        <TableHead className="text-center">Waktu Selesai</TableHead>
                                        <TableHead className="w-28 text-center">Aksi</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {daftarPart.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={10} className="text-muted-foreground h-24 text-center">
                                                Tidak ada part dalam dokumen penerimaan ini di area Anda.
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        daftarPart.map((part, idx) => (
                                            <TableRow key={part.id}>
                                                <TableCell className="text-muted-foreground text-center text-xs tabular-nums">
                                                    {idx + 1}
                                                </TableCell>
                                                <TableCell className="font-mono text-xs font-bold">
                                                    {part.no_part}
                                                </TableCell>
                                                <TableCell className="max-w-56 truncate text-xs">
                                                    {part.nm_part}
                                                </TableCell>
                                                <TableCell className="font-mono text-xs">
                                                    {part.no_doos || '-'}
                                                </TableCell>
                                                <TableCell className="text-center text-xs">
                                                    <Badge variant="outline" className="font-mono font-bold">
                                                        {part.kode_rak}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell className="text-right text-xs tabular-nums font-semibold">
                                                    {part.qty_diterima}
                                                </TableCell>
                                                <TableCell className="text-right text-xs tabular-nums font-bold text-emerald-600">
                                                    {part.qty_masuk !== null ? part.qty_masuk : '-'}
                                                </TableCell>
                                                <TableCell className="text-center">
                                                    {part.status_masuk ? (
                                                        <Badge className="bg-emerald-600 text-white hover:bg-emerald-600">
                                                            Done
                                                        </Badge>
                                                    ) : (
                                                        <Badge variant="destructive">Waiting</Badge>
                                                    )}
                                                </TableCell>
                                                <TableCell className="text-center text-xs tabular-nums text-muted-foreground">
                                                    {part.waktu_done
                                                        ? new Date(part.waktu_done).toLocaleString('id-ID')
                                                        : '-'}
                                                </TableCell>
                                                <TableCell className="text-center">
                                                    {part.status_masuk ? (
                                                        <span className="text-muted-foreground text-xs italic">
                                                            Selesai
                                                        </span>
                                                    ) : (
                                                        <Button
                                                            size="sm"
                                                            className="h-8 text-xs font-bold"
                                                            onClick={() => bukaModalInput(part)}
                                                        >
                                                            <Check className="mr-1 h-3.5 w-3.5" />
                                                            Masuk Rak
                                                        </Button>
                                                    )}
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

            {/* Modal Input Qty Masuk */}
            <Dialog open={itemDipilih !== null} onOpenChange={(open) => !open && setItemDipilih(null)}>
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Konfirmasi Part Masuk Rak</DialogTitle>
                        <DialogDescription>
                            Pastikan part telah diletakkan di rak yang sesuai sebelum konfirmasi.
                        </DialogDescription>
                    </DialogHeader>

                    {itemDipilih && (
                        <div className="space-y-3 py-2 text-sm">
                            <div className="bg-muted/50 space-y-1.5 rounded-md p-3">
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Part Number:</span>
                                    <span className="font-mono font-bold">{itemDipilih.no_part}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Lokasi Rak:</span>
                                    <span className="font-mono font-bold text-red-600">{itemDipilih.kode_rak}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Qty Diterima:</span>
                                    <span className="font-mono font-bold">{itemDipilih.qty_diterima}</span>
                                </div>
                            </div>

                            <div className="space-y-1">
                                <Label htmlFor="qty_masuk">Jumlah Qty Masuk ke Rak</Label>
                                <Input
                                    id="qty_masuk"
                                    type="number"
                                    min={0}
                                    max={itemDipilih.qty_diterima}
                                    value={inputQty}
                                    onChange={(e) => setInputQty(parseInt(e.target.value, 10) || 0)}
                                    autoFocus
                                />
                            </div>
                        </div>
                    )}

                    <DialogFooter>
                        <Button variant="outline" onClick={() => setItemDipilih(null)} disabled={sedangSimpan}>
                            Batal
                        </Button>
                        <Button onClick={handleSimpanItem} disabled={sedangSimpan}>
                            {sedangSimpan && <Loader2 className="mr-1.5 h-4 w-4 animate-spin" />}
                            Simpan ke Rak
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
