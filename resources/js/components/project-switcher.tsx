import LogoMA from '@/assets/images/malogo.png';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { type Project } from '@/types';
import { router } from '@inertiajs/react';
import { Check, ChevronsUpDown } from 'lucide-react';
import * as LucideIcons from 'lucide-react';

interface ProjectSwitcherProps {
    projects: Project[];
    projectAktif: string | null;
}

const NAMA_PERUSAHAAN = 'PT. Menara Agung';

function ikonProject(nama: string | null) {
    return (LucideIcons as unknown as Record<string, LucideIcons.LucideIcon>)[nama ?? ''] ?? LucideIcons.Boxes;
}

export function ProjectSwitcher({ projects, projectAktif }: ProjectSwitcherProps) {
    const { isMobile } = useSidebar();
    const aktif = projects.find((item) => item.kode === projectAktif) ?? projects[0];

    if (!aktif) {
        return null;
    }

    const bisaBerpindah = projects.length > 1;

    const isiTombol = (
        <>
            <div className="flex aspect-square size-8 items-center justify-center rounded-lg bg-white">
                <img src={LogoMA} alt={NAMA_PERUSAHAAN} className="size-6 object-contain" />
            </div>
            <div className="grid flex-1 text-left leading-tight">
                <span className="truncate text-sm font-semibold">{NAMA_PERUSAHAAN}</span>
                <span className="border-sidebar-foreground text-sidebar-foreground mt-0.5 truncate border-t pt-0.5 text-xs">
                    {aktif.nama}
                </span>
            </div>
            {bisaBerpindah && <ChevronsUpDown className="ml-auto" />}
        </>
    );

    /**
     * Tanpa project kedua tidak ada yang bisa dipilih. Trigger yang disabled
     * ikut membawa `disabled:opacity-50` bawaan tombol sehingga tampak redup,
     * jadi dropdown-nya tidak dirender sama sekali.
     */
    if (!bisaBerpindah) {
        return (
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton
                        size="lg"
                        tooltip={`${NAMA_PERUSAHAAN} — ${aktif.nama}`}
                        className="hover:bg-transparent hover:text-sidebar-foreground active:bg-transparent cursor-default"
                    >
                        {isiTombol}
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        );
    }

    return (
        <SidebarMenu>
            <SidebarMenuItem>
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <SidebarMenuButton
                            size="lg"
                            tooltip={`${NAMA_PERUSAHAAN} — ${aktif.nama}`}
                            className="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                        >
                            {isiTombol}
                        </SidebarMenuButton>
                    </DropdownMenuTrigger>

                    <DropdownMenuContent
                        className="w-(--radix-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                        align="start"
                        side={isMobile ? 'bottom' : 'right'}
                        sideOffset={4}
                    >
                        <DropdownMenuLabel className="text-muted-foreground text-xs">
                            Pindah Project
                        </DropdownMenuLabel>

                        {projects.map((project) => {
                            const Ikon = ikonProject(project.ikon);

                            return (
                                <DropdownMenuItem
                                    key={project.kode}
                                    className="gap-2 p-2"
                                    disabled={!project.url_awal}
                                    onClick={() => project.url_awal && router.visit(project.url_awal)}
                                >
                                    <div className="flex size-6 items-center justify-center rounded-md border">
                                        <Ikon className="size-3.5 shrink-0" />
                                    </div>
                                    <span className="flex-1">{project.nama}</span>
                                    {project.kode === aktif.kode && <Check className="size-4" />}
                                </DropdownMenuItem>
                            );
                        })}
                    </DropdownMenuContent>
                </DropdownMenu>
            </SidebarMenuItem>
        </SidebarMenu>
    );
}
