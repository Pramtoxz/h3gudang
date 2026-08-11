import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useForm } from '@inertiajs/react';
import { Loader2, Upload } from 'lucide-react';
import type { FormEvent, ReactNode } from 'react';

interface DialogImportExcelProps {
    terbuka: boolean;
    judul: string;
    keterangan: ReactNode;
    url: string;
    onTutup: () => void;
}

export function DialogImportExcel({
    terbuka,
    judul,
    keterangan,
    url,
    onTutup,
}: DialogImportExcelProps) {
    const { setData, post, processing, progress, errors, reset, clearErrors } = useForm<{
        file: File | null;
    }>({ file: null });

    const kirim = (event: FormEvent) => {
        event.preventDefault();

        post(url, {
            forceFormData: true,
            onSuccess: () => {
                reset();
                onTutup();
            },
        });
    };

    const tutup = () => {
        reset();
        clearErrors();
        onTutup();
    };

    return (
        <Dialog open={terbuka} onOpenChange={(status) => !status && tutup()}>
            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>{judul}</DialogTitle>
                </DialogHeader>

                <form onSubmit={kirim} className="space-y-4">
                    <div className="bg-muted rounded-lg p-3 text-xs leading-relaxed">{keterangan}</div>

                    <div className="space-y-1">
                        <Label htmlFor="file">Pilih berkas (.xlsx atau .csv)</Label>
                        <Input
                            id="file"
                            type="file"
                            accept=".xlsx,.csv,.txt"
                            onChange={(event) => setData('file', event.target.files?.[0] ?? null)}
                        />
                        {errors.file && <p className="text-destructive text-xs">{errors.file}</p>}
                    </div>

                    {progress && (
                        <div className="bg-muted h-2 w-full overflow-hidden rounded-full">
                            <div
                                className="bg-primary h-full transition-all"
                                style={{ width: `${progress.percentage ?? 0}%` }}
                            />
                        </div>
                    )}

                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={tutup} disabled={processing}>
                            Batal
                        </Button>
                        <Button type="submit" disabled={processing}>
                            {processing ? (
                                <Loader2 className="mr-1 h-4 w-4 animate-spin" />
                            ) : (
                                <Upload className="mr-1 h-4 w-4" />
                            )}
                            Unggah & Import
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
