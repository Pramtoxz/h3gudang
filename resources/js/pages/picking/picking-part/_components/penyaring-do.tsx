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
import { RotateCcw, Search } from 'lucide-react';
import { STATUS_BAWAAN } from './tipe';

export const SEMUA_AREA = 'semua';

interface PenyaringDoProps {
    kunci: string;
    area: string;
    status: string;
    tglDari: string;
    tglSampai: string;
    daftarAreaChannel: string[];
    adaPenyaring: boolean;
    onKunci: (nilai: string) => void;
    onArea: (nilai: string) => void;
    onStatus: (nilai: string) => void;
    onTanggal: (dari: string, sampai: string) => void;
    onReset: () => void;
}

export function PenyaringDo({
    kunci,
    area,
    status,
    tglDari,
    tglSampai,
    daftarAreaChannel,
    adaPenyaring,
    onKunci,
    onArea,
    onStatus,
    onTanggal,
    onReset,
}: PenyaringDoProps) {
    return (
        <div className="space-y-2">
            <div className="flex flex-col gap-2 lg:flex-row">
                <div className="relative lg:max-w-xs lg:flex-1">
                    <Search className="text-muted-foreground absolute top-2.5 left-2.5 h-4 w-4" />
                    <Input
                        value={kunci}
                        onChange={(event) => onKunci(event.target.value)}
                        placeholder="Cari nomor DO, channel, atau dealer"
                        className="pl-8"
                    />
                </div>

                <Select value={area} onValueChange={onArea}>
                    <SelectTrigger className="lg:w-52">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value={SEMUA_AREA}>Semua area</SelectItem>
                        {daftarAreaChannel.map((nama) => (
                            <SelectItem key={nama} value={nama}>
                                {nama}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>

                <Select value={status} onValueChange={onStatus}>
                    <SelectTrigger className="lg:w-56">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value={STATUS_BAWAAN}>On Progress &amp; Waiting</SelectItem>
                        <SelectItem value="all">Semua Status (hari ini)</SelectItem>
                        <SelectItem value="Done">Done</SelectItem>
                        <SelectItem value="On Progress">On Progress</SelectItem>
                        <SelectItem value="Waiting">Waiting</SelectItem>
                    </SelectContent>
                </Select>

                {adaPenyaring && (
                    <Button variant="outline" onClick={onReset}>
                        <RotateCcw className="mr-1 h-4 w-4" />
                        Reset
                    </Button>
                )}
            </div>

            {status === 'Done' && (
                <div className="bg-muted/40 flex flex-col gap-2 rounded-md border p-3 sm:flex-row sm:items-end">
                    <div className="space-y-1">
                        <Label htmlFor="tgl_dari" className="text-xs">
                            Dari tanggal
                        </Label>
                        <Input
                            id="tgl_dari"
                            type="date"
                            className="h-11 sm:w-44"
                            value={tglDari}
                            onChange={(event) => onTanggal(event.target.value, tglSampai)}
                        />
                    </div>
                    <div className="space-y-1">
                        <Label htmlFor="tgl_sampai" className="text-xs">
                            Sampai tanggal
                        </Label>
                        <Input
                            id="tgl_sampai"
                            type="date"
                            className="h-11 sm:w-44"
                            value={tglSampai}
                            onChange={(event) => onTanggal(tglDari, event.target.value)}
                        />
                    </div>
                    <p className="text-muted-foreground pb-2 text-xs">
                        Dikosongkan berarti hari ini.
                    </p>
                </div>
            )}
        </div>
    );
}
