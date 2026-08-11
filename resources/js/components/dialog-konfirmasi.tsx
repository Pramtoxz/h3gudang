import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Loader2 } from 'lucide-react';
import type { ReactNode } from 'react';

interface DialogKonfirmasiProps {
    terbuka: boolean;
    judul: string;
    keterangan: ReactNode;
    labelKonfirmasi: string;
    merusak?: boolean;
    sedangProses: boolean;
    onBatal: () => void;
    onKonfirmasi: () => void;
}

export function DialogKonfirmasi({
    terbuka,
    judul,
    keterangan,
    labelKonfirmasi,
    merusak = false,
    sedangProses,
    onBatal,
    onKonfirmasi,
}: DialogKonfirmasiProps) {
    return (
        <Dialog open={terbuka} onOpenChange={(status) => !status && onBatal()}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>{judul}</DialogTitle>
                    <DialogDescription asChild>
                        <div className="text-muted-foreground text-sm">{keterangan}</div>
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" onClick={onBatal} disabled={sedangProses}>
                        Batal
                    </Button>
                    <Button
                        variant={merusak ? 'destructive' : 'default'}
                        onClick={onKonfirmasi}
                        disabled={sedangProses}
                    >
                        {sedangProses && <Loader2 className="mr-1 h-4 w-4 animate-spin" />}
                        {labelKonfirmasi}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
