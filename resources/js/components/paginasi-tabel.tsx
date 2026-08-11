import {
    Pagination,
    PaginationContent,
    PaginationItem,
    PaginationLink,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';

interface PaginasiTabelProps {
    halaman: number;
    totalHalaman: number;
    totalData: number;
    perHalaman: number;
    onPindah: (halaman: number) => void;
}

export function PaginasiTabel({
    halaman,
    totalHalaman,
    totalData,
    perHalaman,
    onPindah,
}: PaginasiTabelProps) {
    if (totalData === 0) {
        return null;
    }

    const dari = (halaman - 1) * perHalaman + 1;
    const sampai = Math.min(halaman * perHalaman, totalData);

    return (
        <div className="flex flex-col items-center justify-between gap-2 sm:flex-row">
            <p className="text-muted-foreground text-xs">
                Menampilkan {dari}–{sampai} dari {totalData} data
            </p>

            {totalHalaman > 1 && (
                <Pagination className="mx-0 w-auto justify-end">
                    <PaginationContent>
                        <PaginationItem>
                            <PaginationPrevious
                                href="#"
                                className={halaman <= 1 ? 'pointer-events-none opacity-50' : ''}
                                onClick={(event) => {
                                    event.preventDefault();
                                    if (halaman > 1) onPindah(halaman - 1);
                                }}
                            />
                        </PaginationItem>

                        {Array.from({ length: totalHalaman }, (_, indeks) => indeks + 1).map((nomor) => (
                            <PaginationItem key={nomor} className="hidden sm:inline-block">
                                <PaginationLink
                                    href="#"
                                    isActive={nomor === halaman}
                                    onClick={(event) => {
                                        event.preventDefault();
                                        onPindah(nomor);
                                    }}
                                >
                                    {nomor}
                                </PaginationLink>
                            </PaginationItem>
                        ))}

                        <PaginationItem>
                            <PaginationNext
                                href="#"
                                className={halaman >= totalHalaman ? 'pointer-events-none opacity-50' : ''}
                                onClick={(event) => {
                                    event.preventDefault();
                                    if (halaman < totalHalaman) onPindah(halaman + 1);
                                }}
                            />
                        </PaginationItem>
                    </PaginationContent>
                </Pagination>
            )}
        </div>
    );
}
