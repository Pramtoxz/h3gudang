import MobileLayoutWrapper from '@/layouts/mobile-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';

interface LapangLayoutProps extends PropsWithChildren {
    breadcrumbs?: BreadcrumbItem[];
}

export default function LapangLayout({ children, breadcrumbs = [] }: LapangLayoutProps) {
    return (
        <MobileLayoutWrapper>
            <Head title="Picking Lapangan" />
            <div className="flex h-screen flex-col bg-background">
                {/* Header */}
                <header className="border-b bg-card px-4 py-3 shadow-sm">
                    <h1 className="text-base font-semibold text-foreground">Picking Lapangan</h1>
                </header>

                {/* Main Content */}
                <main className="flex-1 overflow-y-auto p-4">{children}</main>
            </div>
        </MobileLayoutWrapper>
    );
}
