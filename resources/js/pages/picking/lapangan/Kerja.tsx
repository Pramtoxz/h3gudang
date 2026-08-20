import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { ArrowLeft, CheckCircle2, Hourglass, Loader2, TriangleAlert } from 'lucide-react';
import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import LapangLayout from './LapangLayout';
import type { BarisPart } from '../_components/tipe';
import { DialogKartuStok, type ItemKartuStok } from '../picking-part/_components/dialog-kartu-stok';

interface Props {
    fkDo: string;
    daftarPart: BarisPart[];
    isBundling: boolean;
    infoDo: {
        nama_channel: string;
        area: string;
        keterangan_picking: string;
        tgl_picking_list_part: string | null;
    };
}

/**
 * Layar kerja operator lapangan: SATU PART PER LAYAR.
 * 
 * Design philosophy untuk HP landscape di lengan:
 * - Yang dibesarkan: KODE RAK dan JUMLAH (dibaca sekilas)
 * - Tombol besar di bawah (thumb zone)
 * - Auto-advance ke part berikutnya setelah "Sudah Diambil"
 * - Waiting dulu, done di bawah (sesuai urutan rak)
 */
export default function KerjaLapangan({ fkDo, daftarPart, isBundling, infoDo }: Props) {
    const [indexPart, setIndexPart] = useState(0);
    const [sedangProses, setSedangProses] = useState(false);
    const [modalKartuStok, setModalKartuStok] = useState(false);
    const [itemsKartuStok, setItemsKartuStok] = useState<ItemKartuStok[]>([]);
    const [error, setError] = useState<string | null>(null);

    const partSekarang = daftarPart[indexPart] ?? null;
    const totalPart = daftarPart.length;
    const progress = totalPart > 0 ? Math.round(((indexPart + 1) / totalPart) * 100) : 0;

    if (!partSekarang) {
        return (
            <LapangLayout>
                <div className="flex min-h-[60vh] flex-col items-center justify-center gap-4">
                    <CheckCircle2 className="h-16 w-16 text-emerald-600" />
                    <p className="text-xl font-bold">Semua part sudah selesai!</p>
                    <Button variant="outline" onClick={() => router.visit('/picking/lapangan')}>
                        Kembali ke Daftar DO
                    </Button>
                </div>
            </LapangLayout>
        );
    }

    const handleMarkDone = async () => {
        if (!partSekarang || partSekarang.status_picking_list === 'done') return;

        setSedangProses(true);
        setError(null);

        try {
            const res = await fetch('/picking/picking-part/update-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
                },
                body: JSON.stringify({ id: partSekarang.id, status: 'done' }),
            });

            const data = await res.json();

            if (!data.success) {
                setError(data.message || 'Gagal mengubah status.');
                return;
            }

            // Jika ada kartu stok yang harus diinput, buka modal
            if (Array.isArray(data.kartustok_list) && data.kartustok_list.length > 0) {
                setItemsKartuStok(data.kartustok_list);
                setModalKartuStok(true);
            } else {
                // Auto-advance ke part berikutnya
                advanceToNext();
            }
        } catch (err) {
            console.error(err);
            setError('Terjadi kesalahan. Coba lagi.');
        } finally {
            setSedangProses(false);
        }
    };

    const advanceToNext = () => {
        if (indexPart < totalPart - 1) {
            setIndexPart(indexPart + 1);
        } else {
            // Semua part selesai → tampilkan layar sukses
            setIndexPart(-1); // Trigger empty state
        }
    };

    const handleSimpanKartuStok = (items: ItemKartuStok[]) => {
        setSedangProses(true);

        fetch('/picking/picking-part/kartustok', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
            },
            body: JSON.stringify({ items }),
        })
            .then((res) => res.json())
            .then((data) => {
                if (!data.success) {
                    throw new Error(data.message || 'Gagal menyimpan kartu stok');
                }
                // Sukses → close modal & advance
                setModalKartuStok(false);
                advanceToNext();
            })
            .catch((err) => {
                console.error(err);
                alert('Terjadi kesalahan saat menyimpan kartu stok.');
            })
            .finally(() => {
                setSedangProses(false);
            });
    };

    return (
        <>
            <Head title={`Kerja: ${fkDo}`} />

            <LapangLayout>
                <div className="mx-auto max-w-4xl space-y-6">
                    {/* Header Progress */}
                    <div className="space-y-2">
                        <div className="flex items-center justify-between">
                            <Button
                                variant="ghost"
                                size="sm"
                                onClick={() => router.visit('/picking/lapangan')}
                                className="gap-2"
                            >
                                <ArrowLeft className="h-4 w-4" />
                                Kembali
                            </Button>

                            <div className="text-center">
                                <p className="text-sm text-muted-foreground">
                                    Part {indexPart + 1} dari {totalPart}
                                </p>
                                {isBundling && (
                                    <Badge variant="destructive" className="animate-pulse mt-1">
                                        <TriangleAlert className="mr-1 h-3 w-3" />
                                        URGENT
                                    </Badge>
                                )}
                            </div>

                            <Badge variant="secondary" className="px-3 py-1">
                                {infoDo.nama_channel}
                            </Badge>
                        </div>

                        {/* Progress Bar */}
                        <div className="bg-muted relative h-3 w-full overflow-hidden rounded-full">
                            <div
                                className="bg-emerald-600 h-full transition-all duration-300"
                                style={{ width: `${progress}%` }}
                            />
                        </div>
                    </div>

                    {/* Main Card: One Part Display */}
                    <Card className="border-2">
                        <CardContent className="p-8">
                            <div className="grid grid-cols-2 gap-8">
                                {/* Left Column: Location & Quantity */}
                                <div className="space-y-6">
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground mb-2">
                                            LOKASI RAK
                                        </p>
                                        <p className="text-5xl font-bold text-foreground">
                                            {partSekarang.lokasi_part}
                                        </p>
                                    </div>

                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground mb-2">
                                            JUMLAH
                                        </p>
                                        <p className="text-5xl font-bold text-emerald-600">
                                            {partSekarang.qty_part}
                                        </p>
                                    </div>
                                </div>

                                {/* Right Column: Part Info */}
                                <div className="space-y-6">
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground mb-2">
                                            PART NUMBER
                                        </p>
                                        <p className="text-3xl font-bold font-mono">
                                            {partSekarang.fk_part}
                                        </p>
                                    </div>

                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground mb-2">
                                            DESKRIPSI
                                        </p>
                                        <p className="text-xl font-medium">
                                            {partSekarang.nm_part}
                                        </p>
                                    </div>

                                    {partSekarang.keterangan_picking !== '-' && (
                                        <div>
                                            <p className="text-sm font-medium text-muted-foreground mb-2">
                                                KETERANGAN
                                            </p>
                                            <p className="text-lg">
                                                {partSekarang.keterangan_picking}
                                            </p>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Error Message */}
                    {error && (
                        <div className="rounded-lg bg-destructive/10 p-4 text-center text-destructive">
                            <p className="font-medium">{error}</p>
                        </div>
                    )}

                    {/* Action Button (Big, Bottom) */}
                    <Button
                        className="w-full h-20 text-2xl font-bold shadow-lg"
                        size="lg"
                        disabled={sedangProses || partSekarang.status_picking_list === 'done'}
                        onClick={handleMarkDone}
                    >
                        {sedangProses ? (
                            <>
                                <Loader2 className="mr-3 h-6 w-6 animate-spin" />
                                Memproses...
                            </>
                        ) : partSekarang.status_picking_list === 'done' ? (
                            <>
                                <CheckCircle2 className="mr-3 h-6 w-6" />
                                Sudah Diambil
                            </>
                        ) : (
                            <>
                                <Hourglass className="mr-3 h-6 w-6" />
                                Sudah Diambil
                            </>
                        )}
                    </Button>
                </div>
            </LapangLayout>

            {/* Dialog Kartu Stok (Batch Submit) */}
            <DialogKartuStok
                terbuka={modalKartuStok}
                items={itemsKartuStok}
                sedangProses={sedangProses}
                error={null}
                onSimpan={handleSimpanKartuStok}
                onBatal={() => {
                    setModalKartuStok(false);
                    window.location.reload();
                }}
            />
        </>
    );
}
