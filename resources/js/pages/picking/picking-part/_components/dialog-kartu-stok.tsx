import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Loader2 } from 'lucide-react';
import { useState } from 'react';

export interface ItemInput {
    id: number;
    fk_do: string;
    fk_dealer: string;
    fk_part: string;
    lokasi_part: string;
    qty_part: number;
}

interface DialogKartuStokProps {
    terbuka: boolean;
    items: ItemInput[];
    onSimpan: (items: Array<{fk_do: string; fk_dealer: string; fk_part: string; lokasi_part: string; jumlah_input: number}>) => Promise<void>;
    sedangProses: boolean;
}

/**
 * Modal input Kartu Stok keluar:
 * - Tanpa menampilkan Qty Part (operator menghitung buta)
 * - Validasi ketat: jumlah input HARUS sama persis dengan qty_part
 * - Lock halaman: tombol back/backspace dikunci sampai submit/simpan error
 * - Error message meniru aplikasi lama (teks penuh)
 */
export function DialogKartuStok({
    terbuka,
    items,
    onSimpan,
    sedangProses,
}: DialogKartuStokProps) {
    const [formData, setFormData] = useState<Array<{id: number; jumlah_input: string}>>(
        items.map((item) => ({ id: item.id, jumlah_input: '' })),
    );

    // Lock page behavior: prevent browser back button
    if (terbuka) {
        document.body.style.pointerEvents = 'none';
        const beforeUnloadHandler = (e: BeforeUnloadEvent) => {
            e.preventDefault();
            e.returnValue = '';
        };
        window.addEventListener('beforeunload', beforeUnloadHandler);
        return () => {
            document.body.style.pointerEvents = '';
            window.removeEventListener('beforeunload', beforeUnloadHandler);
        };
    }

    const handleSimpan = async () => {
        // Validasi semua baris terisi
        const missing = formData.find((field) => !field.jumlah_input || field.jumlah_input === '');
        if (missing) {
            alert('Semua baris harus diisi.');
            return;
        }

        const validatedItems = formData.map((f) => {
            const input = parseInt(f.jumlah_input, 10);
            if (isNaN(input) || input < 1) {
                throw new Error(
                    'Jumlah barang yang keluar tidak sesuai dengan Qty Part DO<br />' +
                        'Jika ragu Silahkan Hitung Ulang Part Yang Anda Masukan Atau Hubungi Kepala Gudang!!!<br />' +
                        'SEGERA!!!!',
                );
            }

            const original = items.find((i) => i.id === f.id);
            if (!original) {
                throw new Error('Data part tidak ditemukan.');
            }

            if (input !== original.qty_part) {
                throw new Error(
                    'Jumlah barang yang keluar tidak sesuai dengan Qty Part DO<br />' +
                        'Jika ragu Silahkan Hitung Ulang Part Yang Anda Masukan Atau Hubungi Kepala Gudang!!!<br />' +
                        'SEGERA!!!!',
                );
            }

            return {
                fk_do: original.fk_do,
                fk_dealer: original.fk_dealer,
                fk_part: original.fk_part,
                lokasi_part: original.lokasi_part,
                jumlah_input: input,
            };
        });

        await onSimpan(validatedItems);
    };

    const handleChange = (id: number, nilai: string) => {
        setFormData((prev) => prev.map((row) => (row.id === id ? { ...row, jumlah_input: nilai } : row)));
    };

    return (
        <>
            <dialog open className="backdrop:bg-black/50 backdrop:blur-sm">
                <div className="bg-card mx-auto w-[90vw] max-w-3xl rounded-lg border p-4 shadow-lg sm:p-6">
                    <h2 className="text-xl font-semibold mb-4">Input Kartu Stok</h2>

                    {/* Tabel input tanpa Qty Part */}
                    <div className="overflow-y-auto max-h-[50vh] rounded-md border mb-4">
                        <table className="w-full text-left text-sm">
                            <thead className="bg-muted sticky top-0 z-10">
                                <tr>
                                    <th className="p-3 font-medium">No.</th>
                                    <th className="p-3 font-medium">Part Number</th>
                                    <th className="p-3 font-medium">Lokasi Rak</th>
                                    <th className="p-3 font-medium">FK Dealer</th>
                                    <th className="p-3 font-medium">Qty Keluar</th>
                                </tr>
                            </thead>
                            <tbody>
                                {items.map((item, idx) => (
                                    <tr key={item.id} className="border-t">
                                        <td className="p-3 text-center">{idx + 1}</td>
                                        <td className="p-3 font-mono">{item.fk_part}</td>
                                        <td className="p-3 font-mono">{item.lokasi_part}</td>
                                        <td className="p-3">{item.fk_dealer}</td>
                                        <td className="p-3 w-48">
                                            <Input
                                                type="number"
                                                min="1"
                                                step="1"
                                                placeholder="0"
                                                value={formData.find((f) => f.id === item.id)?.jumlah_input ?? ''}
                                                onChange={(e) => handleChange(item.id, e.target.value)}
                                                className="h-10"
                                            />
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    <p className="text-muted-foreground text-xs mb-4">
                        Pastikan Qty Keluar sudah benar sebelum menyimpan. Jumlah harus sama persis dengan Qty Part DO.
                    </p>

                    {/* Footer */}
                    <div className="flex justify-end gap-3">
                        <Button
                            variant="outline"
                            onClick={() => location.reload()}
                            disabled={sedangProses}
                            className="bg-gray-700 hover:bg-gray-800"
                        >
                            Tutup
                        </Button>
                        <Button onClick={handleSimpan} disabled={sedangProses}>
                            {sedangProses && <Loader2 className="mr-1 h-4 w-4 animate-spin" />}
                            Simpan
                        </Button>
                    </div>
                </div>
            </dialog>
        </>
    );
}
