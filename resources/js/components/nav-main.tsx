import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { type MenuNavigasi } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import * as LucideIcons from 'lucide-react';

interface NavMainProps {
    menus: MenuNavigasi[];
    label?: string;
}

function ikonMenu(nama: string | null) {
    return (LucideIcons as unknown as Record<string, LucideIcons.LucideIcon>)[nama ?? ''] ?? LucideIcons.Circle;
}

export function NavMain({ menus, label = 'Menu' }: NavMainProps) {
    const halaman = usePage().url;

    const sedangDibuka = (url: string | null): boolean =>
        Boolean(url) && (halaman === url || halaman.startsWith(`${url}/`));

    return (
        <SidebarGroup>
            <SidebarGroupLabel>{label}</SidebarGroupLabel>
            <SidebarMenu>
                {menus.map((menu) => {
                    const Ikon = ikonMenu(menu.ikon);
                    const anak = menu.children ?? [];

                    if (anak.length === 0) {
                        return (
                            <SidebarMenuItem key={menu.id}>
                                <SidebarMenuButton
                                    asChild
                                    isActive={sedangDibuka(menu.url)}
                                    tooltip={menu.nama_menu}
                                >
                                    <Link href={menu.url ?? '#'} prefetch>
                                        <Ikon />
                                        <span>{menu.nama_menu}</span>
                                    </Link>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        );
                    }

                    return (
                        <Collapsible
                            key={menu.id}
                            asChild
                            defaultOpen={anak.some((item) => sedangDibuka(item.url))}
                            className="group/collapsible"
                        >
                            <SidebarMenuItem>
                                <CollapsibleTrigger asChild>
                                    <SidebarMenuButton tooltip={menu.nama_menu}>
                                        <Ikon />
                                        <span>{menu.nama_menu}</span>
                                        <ChevronRight className="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
                                    </SidebarMenuButton>
                                </CollapsibleTrigger>
                                <CollapsibleContent>
                                    <SidebarMenuSub>
                                        {anak.map((item) => (
                                            <SidebarMenuSubItem key={item.id}>
                                                <SidebarMenuSubButton
                                                    asChild
                                                    isActive={sedangDibuka(item.url)}
                                                >
                                                    <Link href={item.url ?? '#'} prefetch>
                                                        <span>{item.nama_menu}</span>
                                                    </Link>
                                                </SidebarMenuSubButton>
                                            </SidebarMenuSubItem>
                                        ))}
                                    </SidebarMenuSub>
                                </CollapsibleContent>
                            </SidebarMenuItem>
                        </Collapsible>
                    );
                })}
            </SidebarMenu>
        </SidebarGroup>
    );
}
