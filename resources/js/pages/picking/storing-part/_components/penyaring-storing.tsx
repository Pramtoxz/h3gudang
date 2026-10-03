import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { RefreshCw, RotateCcw, Search } from 'lucide-react';
import { STATUS_BAWAAN, type GudangItem } from './tipe';

export const SEMUA_AREA = 'semua';
export const SEMUA_GUDANG = 'semua';

interface PenyaringStoringProps {
    kunci: string;
    area: string;
    gudang: string;
    status: string;
    tglDari: string;
    tglSampai: string;
    daftarAreaRak: string[];
    daftarGudang: GudangItem[];
    adaPenyaring: boolean;
    sedangSync: boolean;
    onKunci: (nilai: string) => void;
    onArea: (nilai: string) => void;
    onGudang: (nilai: string) => void;
    onStatus: (nilai: string) => void;
    onTanggal: (dari: string, sampai: string) => void;
    onReset: () => void;
    onSync: () => void;
}

export function PenyaringStoring({
    kunci,
    area,
    gudang,
    status,
    tglDari,
    tglSampai,
    daftarAreaRak,
    daftarGudang,
    adaPenyaring,
    sedangSync,
    onKunci,
    onArea,
    onGudang,
    onStatus,
    onTanggal,
    onReset,
    onSync,
}: PenyaringStoringProps) {
    return (
        <div className="space-y-2">
            <div className="flex flex-col gap-2 lg:flex-row lg:items-center">
                <div className="relative lg:max-w-xs lg:flex-1">
                    <Search className="text-muted-foreground absolute top-2.5 left-2.5 h-4 w-4" />
                    <Input
                        value={kunci}
                        onChange={(event) => onKunci(event.target.value)}
                        placeholder="Cari no. dokumen (RS) atau no. part..."
                        className="pl-8"
                    />
                </div>

                <Select value={area} onValueChange={onArea}>
                    <SelectTrigger className="lg:w-44">
                        <SelectValue placeholder="Area Rak" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value={SEMUA_AREA}>Semua Area Rak</SelectItem>
                        {daftarAreaRak.map((nama) => (
                            <SelectItem key={nama} value={nama}>
                                {nama}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>

                <Select value={gudang} onValueChange={onGudang}>
                    <SelectTrigger className="lg:w-44">
                        <SelectValue placeholder="Gudang" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value={SEMUA_GUDANG}>Semua Gudang</SelectItem>
                        {daftarGudang.map((g) => (
                            <SelectItem key={g.kd_gudang_part} value={g.kd_gudang_part}>
                                {g.nm_gudang_part}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>

                <Select value={status} onValueChange={onStatus}>
                    <SelectTrigger className="lg:w-48">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value={STATUS_BAWAAN}>Antrean Aktif (Waiting)</SelectItem>
                        <SelectItem value="done">Selesai (Done)</SelectItem>
                        <SelectItem value="all">Semua Status</SelectItem>
                    </SelectContent>
                </Select>

                {adaPenyaring && (
                    <Button variant="outline" onClick={onReset}>
                        <RotateCcw className="mr-1 h-4 w-4" />
                        Reset
                    </Button>
                )}

                <Button
                    variant="secondary"
                    className="ml-auto"
                    onClick={onSync}
                    disabled={sedangSync}
                    title="Tarik data penerimaan (storing RS) terbaru dari DMS"
                >
                    <RefreshCw className={`mr-1.5 h-4 w-4 ${sedangSync ? 'animate-spin' : ''}`} />
                    Sync Penerimaan
                </Button>
            </div>

            {(status === 'done' || status === 'all') && (
                <div className="bg-muted/40 flex flex-col gap-2 rounded-md border p-3 sm:flex-row sm:items-end">
                    <div className="space-y-1">
                        <Label htmlFor="tgl_dari" className="text-xs">
                            Dari tanggal
                        </Label>
                        <Input
                            id="tgl_dari"
                            type="date"
                            value={tglDari}
                            onChange={(e) => onTanggal(e.target.value, tglSampai)}
                        />
                    </div>
                    <div className="space-y-1">
                        <Label htmlFor="tgl_sampai" className="text-xs">
                            Sampai tanggal
                        </Label>
                        <Input
                            id="tgl_sampai"
                            type="date"
                            value={tglSampai}
                            onChange={(e) => onTanggal(tglDari, e.target.value)}
                        />
                    </div>
                </div>
            )}
        </div>
    );
}
