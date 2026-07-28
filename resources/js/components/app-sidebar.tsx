import { Link } from '@inertiajs/react';
import {
    Clapperboard,
    ImageIcon,
    LayoutGrid,
    ListTodo,
    Music2,
    Sparkles,
    Wand2,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    { title: 'Dashboard', href: '/dashboard', icon: LayoutGrid },
    { title: 'Schedule', href: '/schedule', icon: ListTodo },
    { title: 'Beats', href: '/beats', icon: Music2 },
    { title: 'Thumbnail', href: '/tools/thumbnail', icon: ImageIcon },
    { title: 'Pack YouTube', href: '/tools/publish-pack', icon: Wand2 },
    { title: 'Shorts', href: '/tools/shorts', icon: Clapperboard },
    { title: 'Brief', href: '/tools/brief', icon: Sparkles },
];

const footerNavItems: NavItem[] = [
    {
        title: 'HollowGrim Beats',
        href: 'https://www.youtube.com/@HollowGrimBeats',
        icon: Music2,
    },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/dashboard" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
