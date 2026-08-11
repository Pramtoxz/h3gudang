import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';
import { toast } from 'sonner';

interface PesanFlash {
    success?: string | null;
    error?: string | null;
    info?: string | null;
}

export function useFlashToast() {
    const { flash } = usePage().props as unknown as { flash?: PesanFlash };

    useEffect(() => {
        if (flash?.success) {
            toast.success(flash.success);
        }

        if (flash?.error) {
            toast.error(flash.error);
        }

        if (flash?.info) {
            toast.info(flash.info);
        }
    }, [flash]);
}
