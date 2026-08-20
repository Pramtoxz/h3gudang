import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { TriangleAlert } from 'lucide-react';
import LapangLayout from './LapangLayout';
import type { BarisDo } from '../_components/tipe';

interface Props {
    daftarDo: {
        data: BarisDo[];
        current_page: number;
        last_page: number;
        total: number;
        per_page: number;
    };
    areaOperator: string | null;
}

/**
 * Layar awal operator lapangan: daftar DO yang punya part waiting.
 * Desain untuk HP landscape (873×393px) — kartu besar, tombol besar,
 * tanpa scroll berlebihan.
 */
export default function LapanganIndex({ daftarDo, areaOperator }: Props) {
    const doTersedia = daftarDo.data;

    if (doTersedia.length === 0) {
        return (
            <LapangLayout>
                <div className="flex min-h-[60vh] flex-col items-center justify-center gap-4">
                    <p className="text-lg font-medium text-muted-foreground">
                        Tidak ada DO yang perlu dikerjakan di area {areaOperator}.
                    </p>
                    <Button variant="outline" onClick={() => window.location.reload()}>
                        Muat Ulang
                    </Button>
                </div>
            </LapangLayout>
        );
    }

    return (
        <LapangLayout>
            <div className="space-y-4">
                {/* Header Info */}
                <div className="flex items-center justify-between">
                    <h2 className="text-lg font-semibold">Pilih DO untuk Dikerjakan</h2>
                    {areaOperator && (
                        <Badge variant="secondary" className="px-3 py-1 text-sm">
                            Area: {areaOperator}
                        </Badge>
                    )}
                </div>

                {/* Grid Kartu DO — 2 kolom landscape mobile */}
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {doTersedia.map((doItem) => (
                        <Card key={doItem.fk_do} className="cursor-pointer transition hover:shadow-md">
                            <CardHeader className="pb-3">
                                <div className="flex items-start justify-between">
                                    <CardTitle className="font-mono text-base">{doItem.fk_do}</CardTitle>
                                    {doItem.is_bundling && (
                                        <Badge variant="destructive" className="animate-pulse">
                                            <TriangleAlert className="mr-1 h-3 w-3" />
                                            URGENT
                                        </Badge>
                                    )}
                                </div>
                            </CardHeader>
                            <CardContent>
                                <dl className="space-y-2 text-sm">
                                    <div className="flex justify-between">
                                        <dt className="text-muted-foreground">Channel:</dt>
                                        <dd className="font-medium">{doItem.nama_channel}</dd>
                                    </div>
                                    <div className="flex justify-between">
                                        <dt className="text-muted-foreground">Items:</dt>
                                        <dd className="font-bold">{doItem.total_items}</dd>
                                    </div>
                                    <div className="flex justify-between">
                                        <dt className="text-muted-foreground">Progress:</dt>
                                        <dd className="font-semibold text-emerald-600">
                                            {doItem.done_parts} / {doItem.total_items}
                                        </dd>
                                    </div>
                                </dl>

                                <Button className="mt-4 w-full h-12 text-base font-bold" size="lg">
                                    Mulai Kerja
                                </Button>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        </LapangLayout>
    );
}
