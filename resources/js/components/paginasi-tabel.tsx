import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
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

const RENTANG = 1;
const TANPA_ELIPSIS = 7;

/**
 * Nomor yang ditampilkan dijaga maksimal tujuh slot supaya lebarnya tetap sama
 * baik untuk 3 halaman maupun 97 halaman.
 */
function daftarNomor(halaman: number, totalHalaman: number): (number | 'elipsis')[] {
    if (totalHalaman <= TANPA_ELIPSIS) {
        return Array.from({ length: totalHalaman }, (_, indeks) => indeks + 1);
    }

    const awal = Math.max(2, Math.min(halaman - RENTANG, totalHalaman - 2 * RENTANG - 2));
    const akhir = Math.min(totalHalaman - 1, Math.max(halaman + RENTANG, 2 * RENTANG + 3));

    const nomor: (number | 'elipsis')[] = [1];

    if (awal > 2) {
        nomor.push('elipsis');
    }

    for (let angka = awal; angka <= akhir; angka++) {
        nomor.push(angka);
    }

    if (akhir < totalHalaman - 1) {
        nomor.push('elipsis');
    }

    nomor.push(totalHalaman);

    return nomor;
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

                        {daftarNomor(halaman, totalHalaman).map((nomor, indeks) => (
                            <PaginationItem
                                key={nomor === 'elipsis' ? `elipsis-${indeks}` : nomor}
                                className="hidden sm:inline-block"
                            >
                                {nomor === 'elipsis' ? (
                                    <PaginationEllipsis />
                                ) : (
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
                                )}
                            </PaginationItem>
                        ))}

                        <PaginationItem className="sm:hidden">
                            <span className="text-muted-foreground px-2 text-xs">
                                Hal. {halaman} / {totalHalaman}
                            </span>
                        </PaginationItem>

                        <PaginationItem>
                            <PaginationNext
                                href="#"
                                className={
                                    halaman >= totalHalaman ? 'pointer-events-none opacity-50' : ''
                                }
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
