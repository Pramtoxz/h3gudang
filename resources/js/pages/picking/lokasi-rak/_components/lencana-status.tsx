import { Badge } from '@/components/ui/badge';
import { CheckCircle2, CircleSlash, Info } from 'lucide-react';

export function LencanaStatus({ total, aktif }: { total: number; aktif: number }) {
    if (aktif === total) {
        return (
            <Badge className="bg-emerald-600 text-white hover:bg-emerald-600">
                <CheckCircle2 className="mr-1 h-3 w-3" />
                Semua aktif
            </Badge>
        );
    }

    if (aktif === 0) {
        return (
            <Badge variant="destructive">
                <CircleSlash className="mr-1 h-3 w-3" />
                Semua tidak aktif
            </Badge>
        );
    }

    return (
        <Badge className="bg-amber-500 text-white hover:bg-amber-500">
            <Info className="mr-1 h-3 w-3" />
            Sebagian ({aktif}/{total})
        </Badge>
    );
}

export function LencanaAktif({ aktif }: { aktif: boolean }) {
    return aktif ? (
        <Badge className="bg-emerald-600 text-white hover:bg-emerald-600">Aktif</Badge>
    ) : (
        <Badge variant="destructive">Tidak aktif</Badge>
    );
}
