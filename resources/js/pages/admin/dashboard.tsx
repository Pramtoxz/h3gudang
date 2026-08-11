import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { ChevronRight, Megaphone, Package, RefreshCw, Store, TrendingUp } from 'lucide-react';

import { KartuStatistik } from './_components/kartu-statistik';
import { KartuStatusCache, type StatusCacheCollection } from './_components/kartu-status-cache';

interface Statistik {
    totalToko: number;
    totalParts: number;
    popularParts: number;
    activeCampaigns: number;
}

interface Props {
    statistik: Statistik;
    cacheCollection: StatusCacheCollection;
}

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/admin/dashboard' }];

export default function AdminDashboard({ statistik, cacheCollection }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <KartuStatistik
                        judul="Total Toko"
                        nilai={statistik.totalToko}
                        ikon={Store}
                        kelasIkon="bg-blue-500/10 text-blue-600 dark:text-blue-400"
                    />
                    <KartuStatistik
                        judul="Total Part"
                        nilai={statistik.totalParts}
                        ikon={Package}
                        kelasIkon="bg-green-500/10 text-green-600 dark:text-green-400"
                    />
                    <KartuStatistik
                        judul="Part Terlaris"
                        nilai={statistik.popularParts}
                        ikon={TrendingUp}
                        kelasIkon="bg-amber-500/10 text-amber-600 dark:text-amber-400"
                    />
                    <KartuStatistik
                        judul="Kampanye Aktif"
                        nilai={statistik.activeCampaigns}
                        ikon={Megaphone}
                        kelasIkon="bg-rose-500/10 text-rose-600 dark:text-rose-400"
                    />
                </div>

                <div className="grid gap-4 lg:grid-cols-3">
                    <Card className="lg:col-span-2">
                        <CardHeader>
                            <CardTitle className="text-base">Selamat Datang di Admin Panel PMO</CardTitle>
                            <p className="text-muted-foreground text-sm">
                                Kelola data part, toko, kampanye, dan notifikasi dari sini.
                            </p>
                        </CardHeader>
                        <CardContent>
                            <h3 className="mb-2 text-sm font-semibold">Aksi Cepat</h3>
                            <Link
                                href="/admin/popular-parts"
                                className="hover:bg-muted flex items-center justify-between rounded-lg border p-3 transition-colors"
                            >
                                <span className="flex items-center gap-3">
                                    <span className="flex h-9 w-9 items-center justify-center rounded-full border text-green-600 dark:text-green-400">
                                        <RefreshCw className="h-4 w-4" />
                                    </span>
                                    <span className="flex flex-col">
                                        <span className="text-sm font-medium">Generate Part Terlaris</span>
                                        <span className="text-muted-foreground text-xs">
                                            Perbarui data part terlaris dari database
                                        </span>
                                    </span>
                                </span>
                                <ChevronRight className="text-muted-foreground h-4 w-4" />
                            </Link>
                        </CardContent>
                    </Card>

                    <KartuStatusCache
                        isRefreshing={cacheCollection.isRefreshing}
                        lastRefresh={cacheCollection.lastRefresh}
                    />
                </div>
            </div>
        </AppLayout>
    );
}
