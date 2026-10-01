import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Clock, KeyRound, Loader2, RefreshCw, Search, ShieldCheck } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';

interface KodeAktif {
    kode: string;
    expires_at: string;
    sisa_detik: number;
    created_by: string;
}

interface BarisOperator {
    email: string;
    nama: string;
    area: string;
    level: number;
    kode_aktif: KodeAktif | null;
}

interface Props {
    daftarOperator: BarisOperator[];
}

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Kode Akses Lapangan', href: '/picking/kode-akses' }];

export default function KodeAksesIndex({ daftarOperator }: Props) {
    const [pencarian, setPencarian] = useState('');
    const [sedangGenerate, setSedangGenerate] = useState<string | null>(null);
    const [modalKode, setModalKode] = useState<{ nama: string; email: string; kode: string } | null>(null);
    const [sisaWaktuMap, setSisaWaktuMap] = useState<Record<string, number>>({});

    // Hitung mundur timer lokal berbasis detik integer
    useEffect(() => {
        const waktuAwal: Record<string, number> = {};
        daftarOperator.forEach((op) => {
            if (op.kode_aktif) {
                waktuAwal[op.email] = Math.max(0, Math.floor(op.kode_aktif.sisa_detik));
            }
        });
        setSisaWaktuMap(waktuAwal);

        const interval = setInterval(() => {
            setSisaWaktuMap((sebelumnya) => {
                const baru: Record<string, number> = {};
                for (const [email, sisa] of Object.entries(sebelumnya)) {
                    if (sisa > 0) {
                        baru[email] = sisa - 1;
                    }
                }
                return baru;
            });
        }, 1000);

        return () => clearInterval(interval);
    }, [daftarOperator]);

    const hasilSaring = useMemo(() => {
        const kunci = pencarian.trim().toLowerCase();
        if (!kunci) return daftarOperator;

        return daftarOperator.filter(
            (op) =>
                op.nama.toLowerCase().includes(kunci) ||
                op.email.toLowerCase().includes(kunci) ||
                op.area.toLowerCase().includes(kunci),
        );
    }, [daftarOperator, pencarian]);

    const buatKode = (op: BarisOperator) => {
        setSedangGenerate(op.email);
        router.post(
            '/picking/kode-akses',
            { email: op.email },
            {
                preserveScroll: true,
                onSuccess: (page) => {
                    const updatedList = (page.props.daftarOperator as BarisOperator[]) ?? [];
                    const updatedOp = updatedList.find((item) => item.email === op.email);
                    if (updatedOp?.kode_aktif) {
                        setModalKode({
                            nama: op.nama,
                            email: op.email,
                            kode: updatedOp.kode_aktif.kode,
                        });
                    }
                },
                onFinish: () => {
                    setSedangGenerate(null);
                },
            },
        );
    };

    const formatMenitDetik = (detik: number) => {
        const totalDetik = Math.max(0, Math.floor(detik));
        const m = Math.floor(totalDetik / 60);
        const s = totalDetik % 60;
        return `${m}:${s < 10 ? '0' : ''}${s}`;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Kode Akses Lapangan" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <Alert>
                    <ShieldCheck className="h-4 w-4" />
                    <AlertTitle className="font-semibold text-sm">
                        Otorisasi Login Operator Lapangan
                    </AlertTitle>
                    <AlertDescription className="text-xs text-muted-foreground mt-1">
                        Operator gudang tidak perlu memasukkan email dan password di HP. Buat kode 6 digit di bawah ini
                        dan beritahukan kepada operator saat memulai tugas. Kode berlaku tepat{' '}
                        <strong>5 menit</strong> (WIB / Asia/Jakarta) dan langsung hangus setelah sukses dipakai masuk.
                    </AlertDescription>
                </Alert>

                <Card>
                    <CardHeader className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <CardTitle className="text-base flex items-center gap-2">
                                <KeyRound className="size-4" />
                                Daftar Operator & Kode Akses
                            </CardTitle>
                            <p className="text-muted-foreground text-xs mt-0.5">
                                Kelola kode otorisasi login HP untuk masing-masing operator gudang
                            </p>
                        </div>

                        <div className="flex items-center gap-2">
                            <div className="relative w-64">
                                <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input
                                    placeholder="Cari nama, email, area..."
                                    value={pencarian}
                                    onChange={(e) => setPencarian(e.target.value)}
                                    className="h-9 pl-9 text-xs"
                                />
                            </div>

                            <Button
                                variant="outline"
                                size="sm"
                                onClick={() => router.reload({ only: ['daftarOperator'] })}
                                title="Segarkan status"
                            >
                                <RefreshCw className="size-3.5" />
                            </Button>
                        </div>
                    </CardHeader>

                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead className="w-12 text-center">No</TableHead>
                                    <TableHead>Nama Operator</TableHead>
                                    <TableHead>Email Akun</TableHead>
                                    <TableHead>Area Rak</TableHead>
                                    <TableHead className="w-48 text-center">Kode Akses Aktif</TableHead>
                                    <TableHead className="w-36 text-center">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>

                            <TableBody>
                                {hasilSaring.length === 0 ? (
                                    <TableRow>
                                        <TableCell colSpan={6} className="h-32 text-center text-sm text-muted-foreground">
                                            Tidak ada operator yang cocok dengan pencarian.
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    hasilSaring.map((op, idx) => {
                                        const sisaDetik = sisaWaktuMap[op.email] ?? 0;
                                        const adaKodeAktif = op.kode_aktif !== null && sisaDetik > 0;

                                        return (
                                            <TableRow key={op.email}>
                                                <TableCell className="text-center font-mono text-xs text-muted-foreground">
                                                    {idx + 1}
                                                </TableCell>

                                                <TableCell className="font-medium text-sm">
                                                    {op.nama}
                                                </TableCell>

                                                <TableCell className="font-mono text-xs text-muted-foreground">
                                                    {op.email}
                                                </TableCell>

                                                <TableCell>
                                                    <Badge variant="outline" className="font-mono text-[11px]">
                                                        {op.area}
                                                    </Badge>
                                                </TableCell>

                                                <TableCell className="text-center">
                                                    {adaKodeAktif ? (
                                                        <div className="flex flex-col items-center gap-1">
                                                            <div className="inline-flex items-center gap-1.5 rounded-md bg-muted px-2.5 py-1 border font-mono text-base font-bold text-foreground">
                                                                <span>{op.kode_aktif?.kode.slice(0, 3)}</span>
                                                                <span>-</span>
                                                                <span>{op.kode_aktif?.kode.slice(3, 6)}</span>
                                                            </div>
                                                            <span className="flex items-center gap-1 font-mono text-[11px] text-muted-foreground">
                                                                <Clock className="size-3" />
                                                                Sisa {formatMenitDetik(sisaDetik)}
                                                            </span>
                                                        </div>
                                                    ) : (
                                                        <span className="font-mono text-xs text-muted-foreground">
                                                            — Belum ada kode —
                                                        </span>
                                                    )}
                                                </TableCell>

                                                <TableCell className="text-center">
                                                    <Button
                                                        size="sm"
                                                        variant={adaKodeAktif ? 'outline' : 'default'}
                                                        disabled={sedangGenerate === op.email}
                                                        onClick={() => buatKode(op)}
                                                        className="text-xs"
                                                    >
                                                        {sedangGenerate === op.email ? (
                                                            <>
                                                                <Loader2 className="mr-1.5 size-3.5 animate-spin" />
                                                                Membuat...
                                                            </>
                                                        ) : adaKodeAktif ? (
                                                            'Buat Ulang'
                                                        ) : (
                                                            <>
                                                                <KeyRound className="mr-1.5 size-3.5" />
                                                                Buat Kode
                                                            </>
                                                        )}
                                                    </Button>
                                                </TableCell>
                                            </TableRow>
                                        );
                                    })
                                )}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>

            {/* Modal Dialog Standar Saat Kode Baru Selesai Dibuat */}
            <Dialog open={modalKode !== null} onOpenChange={(open) => !open && setModalKode(null)}>
                <DialogContent className="max-w-md text-center">
                    <DialogHeader className="items-center">
                        <div className="flex size-11 items-center justify-center rounded-full bg-primary/10 text-primary mb-2">
                            <KeyRound className="size-5" />
                        </div>
                        <DialogTitle className="text-lg font-bold">Kode Akses Berhasil Dibuat</DialogTitle>
                        <DialogDescription className="text-xs text-muted-foreground">
                            Sebutkan 6 digit angka ini kepada operator <strong>{modalKode?.nama}</strong> untuk login di HP.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="my-4 flex flex-col items-center justify-center rounded-lg border bg-muted/40 p-6">
                        <span className="font-mono text-xs text-muted-foreground uppercase tracking-widest mb-1.5">
                            KODE LOGIN (AKTIF 5 MENIT)
                        </span>
                        <div className="font-mono text-5xl font-bold tracking-widest text-foreground">
                            {modalKode?.kode ? `${modalKode.kode.slice(0, 3)} ${modalKode.kode.slice(3, 6)}` : ''}
                        </div>
                    </div>

                    <div className="flex justify-center">
                        <Button className="w-full" onClick={() => setModalKode(null)}>
                            Tutup
                        </Button>
                    </div>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}