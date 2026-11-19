import { Link, usePage } from '@inertiajs/react';
import { Banknote, LayoutGrid, ShieldCheck, Store, Truck } from 'lucide-react';
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
import { dashboard, home } from '@/routes';
import { index as staffShipments } from '@/routes/staff/shipments';
import { index as staffSell } from '@/routes/staff/sell';
import type { NavItem } from '@/types';

export function AppSidebar() {
    const { auth } = usePage().props;
    const can = (permission: string) => auth.permissions.includes(permission);

    const mainNavItems: NavItem[] = [
        { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
        ...(can('sales.record')
            ? [{ title: 'Sell', href: staffSell(), icon: Banknote }]
            : []),
        ...(can('shipments.receive')
            ? [{ title: 'Shipments', href: staffShipments(), icon: Truck }]
            : []),
    ];

    const footerNavItems: NavItem[] = [
        { title: 'View the shop', href: home(), icon: Store },
        ...(can('catalogue.manage')
            ? [{ title: 'Admin', href: '/admin', icon: ShieldCheck }]
            : []),
    ];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
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
