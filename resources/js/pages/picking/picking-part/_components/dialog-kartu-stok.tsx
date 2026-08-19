import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Loader2 } from 'lucide-react';
import { useEffect, useState } from 'react';

export interface ItemKartuStok {
    fk_do: string;
    fk_dealer: string;
    fk_part: string;
    lokasi_part: string;
    qty_part: number;
}

interface DialogKartuStokProps {
    terbuka: boolean;
    items: ItemKartuStok[];
    sedangProses: boolean;
    error: string | null;
    onSimpan: (
        items: Array<{
            fk_do: string;
            fk_dealer: string;
            fk_part: string;
            lokasi_part: string;
            jumlah_input: number;
        }>,
    ) => void;
    onBatal: () => void;
}

/**
 * Modal input Kartu Stok keluar setelah part ditandai Done.
 * Meniru perilaku aplikasi lama (`picking_detail.blade.php`):
 *
 * - **Qty Part sengaja tidak ditampilkan** — operator menghitung buta.
 * - Modal **mengunci halaman**: tidak bisa ditutup lewat klik luar/ESC;
 *   operator wajib mengisi semua baris lalu Simpan (atau Batal yang
 *   menandakan ada masalah dan perlu dicek ulang).
 * - Validasi qty sama persis dilakukan di server; pesan error dari server
 *   ditampilkan apa adanya di sini.
 */
export function DialogKartuStok({
    terbuka,
    items,
    sedangProses,
    error,
    onSimpan,
    onBatal,
}: DialogKartuStokProps) {
    const [jumlah, setJumlah] = useState<string[]>([]);

    // Reset input setiap kali modal dibuka dengan daftar item baru.
    useEffect(() => {
        if (terbuka) {
            setJumlah(items.map(() => ''));
        }
    }, [terbuka, items]);

    const handleSimpan = () => {
        onSimpan(
            items.map((item, indeks) => ({
                fk_do: item.fk_do,
                fk_dealer: item.fk_dealer,
                fk_part: item.fk_part,
                lokasi_part: item.lokasi_part,
                jumlah_input: parseInt(jumlah[indeks] ?? '', 10) || 0,
            })),
        );
    };

    return (
        <Dialog open={terbuka}>
            <DialogContent
                className="sm:max-w-2xl"
                // Modal mengunci: klik luar dan ESC tidak menutup.
                onPointerDownOutside={(e) => e.preventDefault()}
                onEscapeKeyDown={(e) => e.preventDefault()}
                onInteractOutside={(e) => e.preventDefault()}
            >
                <DialogHeader>
                    <DialogTitle>Input Kartu Stok</DialogTitle>
                    <DialogDescription>
                        Masukkan jumlah barang yang keluar untuk setiap part.
                    </DialogDescription>
                </DialogHeader>

                <div className="overflow-x-auto rounded-md border">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-muted">
                            <tr>
                                <th className="p-3 font-medium">No.</th>
                                <th className="p-3 font-medium">Part Number</th>
                                <th className="p-3 font-medium">Lokasi Rak</th>
                                <th className="p-3 font-medium">FK Dealer</th>
                                <th className="w-36 p-3 font-medium">Qty Keluar</th>
                            </tr>
                        </thead>
                        <tbody>
                            {items.map((item, indeks) => (
                                <tr key={`${item.fk_part}-${item.lokasi_part}`} className="border-t">
                                    <td className="text-muted-foreground p-3 text-center text-xs tabular-nums">
                                        {indeks + 1}
                                    </td>
                                    <td className="p-3 font-mono text-xs">{item.fk_part}</td>
                                    <td className="p-3 font-mono text-xs">{item.lokasi_part}</td>
                                    <td className="p-3 text-xs">{item.fk_dealer}</td>
                                    <td className="p-3">
                                        <Input
                                            type="number"
                                            min={1}
                                            step={1}
                                            autoFocus={indeks === 0}
                                            value={jumlah[indeks] ?? ''}
                                            onChange={(e) =>
                                                setJumlah((sebelumnya) => {
                                                    const berikutnya = [...sebelumnya];
                                                    berikutnya[indeks] = e.target.value;
                                                    return berikutnya;
                                                })
                                            }
                                        />
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                <p className="text-muted-foreground text-xs">
                    Pastikan Qty Keluar sudah benar sebelum menyimpan. Jumlah harus sama persis
                    dengan Qty Part DO.
                </p>

                {error && (
                    <div className="text-destructive rounded-md border border-red-300 bg-red-50 p-3 text-sm">
                        {error}
                    </div>
                )}

                <DialogFooter>
                    <Button variant="outline" onClick={onBatal} disabled={sedangProses}>
                        Batal
                    </Button>
                    <Button onClick={handleSimpan} disabled={sedangProses}>
                        {sedangProses && <Loader2 className="mr-1 h-4 w-4 animate-spin" />}
                        Simpan
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
