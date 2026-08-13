import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { ProjectSwitcher } from '@/components/project-switcher';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarRail,
} from '@/components/ui/sidebar';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import type * as React from 'react';

export function AppSidebar({ ...props }: React.ComponentProps<typeof Sidebar>) {
    const { auth, menus, projects, projectAktif } = usePage<SharedData>().props;

    const menuProject = menus.filter((menu) => menu.nama_menu !== 'Pengaturan');
    const menuPengaturan = menus.filter((menu) => menu.nama_menu === 'Pengaturan');

    return (
        <Sidebar collapsible="icon" {...props}>
            <SidebarHeader>
                <ProjectSwitcher projects={projects ?? []} projectAktif={projectAktif} />
            </SidebarHeader>

            <SidebarContent>
                <NavMain menus={menuProject} />
                {menuPengaturan.length > 0 && <NavMain menus={menuPengaturan} label="Sistem" />}
            </SidebarContent>

            <SidebarFooter>{auth?.user && <NavUser user={auth.user} />}</SidebarFooter>

            <SidebarRail />
        </Sidebar>
    );
}
