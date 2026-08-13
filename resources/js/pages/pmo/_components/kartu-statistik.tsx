import { Card, CardContent } from '@/components/ui/card';
import type { LucideIcon } from 'lucide-react';

interface KartuStatistikProps {
    judul: string;
    nilai: number;
    ikon: LucideIcon;
    kelasIkon: string;
}

const formatAngka = new Intl.NumberFormat('id-ID');

export function KartuStatistik({ judul, nilai, ikon: Ikon, kelasIkon }: KartuStatistikProps) {
    return (
        <Card>
            <CardContent className="flex items-center justify-between gap-3 p-4">
                <div className="min-w-0">
                    <p className="text-muted-foreground truncate text-sm font-medium">{judul}</p>
                    <p className="text-2xl font-semibold tabular-nums">{formatAngka.format(nilai)}</p>
                </div>
                <div className={`flex h-11 w-11 shrink-0 items-center justify-center rounded-lg ${kelasIkon}`}>
                    <Ikon className="h-5 w-5" />
                </div>
            </CardContent>
        </Card>
    );
}
