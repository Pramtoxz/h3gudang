import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { CheckCircle2, Info, Loader2 } from 'lucide-react';

export interface RingkasanRefreshCache {
    last_refresh_at: string | null;
    last_refresh_diff: string | null;
    total_shops_processed: number | null;
    total_records: number | null;
    duration_seconds: number | null;
}

export interface StatusCacheCollection {
    isRefreshing: boolean;
    lastRefresh: RingkasanRefreshCache | null;
}

const formatAngka = new Intl.NumberFormat('id-ID');

const formatWaktu = new Intl.DateTimeFormat('id-ID', {
    dateStyle: 'medium',
    timeStyle: 'short',
});

function formatDurasi(detik: number | null): string {
    if (detik === null) {
        return '-';
    }

    const menit = Math.floor(detik / 60);
    const sisaDetik = detik % 60;

    return menit > 0 ? `${menit} menit ${sisaDetik} detik` : `${sisaDetik} detik`;
}

export function KartuStatusCache({ isRefreshing, lastRefresh }: StatusCacheCollection) {
    return (
        <Card className="h-full">
            <CardHeader>
                <CardTitle className="text-base">Status Cache Collection</CardTitle>
                <p className="text-muted-foreground text-sm">Status refresh data tagihan toko</p>
            </CardHeader>
            <CardContent>
                {isRefreshing ? (
                    <div className="flex flex-col items-center py-6 text-center">
                        <Loader2 className="mb-2 h-8 w-8 animate-spin text-amber-500" />
                        <p className="text-sm font-medium text-amber-600">Sedang memperbarui cache...</p>
                        <p className="text-muted-foreground text-xs">
                            Proses berjalan di background. Muat ulang halaman beberapa menit lagi.
                        </p>
                    </div>
                ) : lastRefresh ? (
                    <div className="flex flex-col gap-3">
                        <Badge variant="outline" className="w-fit gap-1 border-green-600/30 text-green-700 dark:text-green-400">
                            <CheckCircle2 className="h-3 w-3" />
                            Berhasil
                        </Badge>

                        <div>
                            <p className="text-muted-foreground text-xs">Terakhir diperbarui</p>
                            <p className="text-sm font-semibold">
                                {lastRefresh.last_refresh_at
                                    ? formatWaktu.format(new Date(lastRefresh.last_refresh_at))
                                    : '-'}
                            </p>
                            {lastRefresh.last_refresh_diff && (
                                <span className="text-muted-foreground text-xs">
                                    ({lastRefresh.last_refresh_diff})
                                </span>
                            )}
                        </div>

                        <div className="grid grid-cols-2 gap-3 border-t pt-3">
                            <div>
                                <p className="text-muted-foreground text-xs">Toko diproses</p>
                                <p className="text-sm font-semibold tabular-nums">
                                    {formatAngka.format(lastRefresh.total_shops_processed ?? 0)}
                                </p>
                            </div>
                            <div>
                                <p className="text-muted-foreground text-xs">Total data</p>
                                <p className="text-sm font-semibold tabular-nums">
                                    {formatAngka.format(lastRefresh.total_records ?? 0)}
                                </p>
                            </div>
                        </div>

                        <div className="border-t pt-3">
                            <p className="text-muted-foreground text-xs">Durasi proses</p>
                            <p className="text-sm font-semibold">{formatDurasi(lastRefresh.duration_seconds)}</p>
                        </div>
                    </div>
                ) : (
                    <div className="flex flex-col items-center py-6 text-center">
                        <Info className="text-muted-foreground mb-2 h-8 w-8" />
                        <p className="text-muted-foreground text-sm">Belum ada data refresh</p>
                        <p className="text-muted-foreground text-xs">
                            Berjalan otomatis lewat cron, atau refresh manual di halaman Toko.
                        </p>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
