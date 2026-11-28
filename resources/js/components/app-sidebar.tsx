import { Link, usePage } from '@inertiajs/react';
import {
    Banknote,
    Handshake,
    LayoutGrid,
    PackageSearch,
    ShieldCheck,
    ShoppingBag,
    Store,
    Truck,
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
import { tk } from '@/hooks/use-trans';
import { dashboard, home } from '@/routes';
import { index as myRequests } from '@/routes/requests';
import { index as myReservations } from '@/routes/reservations';
import { index as supplierOffers } from '@/routes/supplier/offers';
import { index as staffShipments } from '@/routes/staff/shipments';
import { index as staffSell } from '@/routes/staff/sell';
import type { NavItem } from '@/types';

export function AppSidebar() {
    const { auth } = usePage().props;
    const can = (permission: string) => auth.permissions.includes(permission);

    const mainNavItems: NavItem[] = [
        { title: tk('Account'), href: dashboard(), icon: LayoutGrid },
        ...(can('requests.create') && !can('requests.manage')
            ? [
                  {
                      title: tk('My reservations'),
                      href: myReservations(),
                      icon: ShoppingBag,
                  },
                  {
                      title: tk('My requests'),
                      href: myRequests(),
                      icon: PackageSearch,
                  },
              ]
            : []),
        ...(can('supplier-offers.submit')
            ? [
                  {
                      title: tk('My offers'),
                      href: supplierOffers(),
                      icon: Handshake,
                  },
              ]
            : []),
        ...(can('sales.record')
            ? [{ title: tk('Sell'), href: staffSell(), icon: Banknote }]
            : []),
        ...(can('shipments.receive')
            ? [{ title: tk('Shipments'), href: staffShipments(), icon: Truck }]
            : []),
    ];

    const footerNavItems: NavItem[] = [
        { title: tk('View the shop'), href: home(), icon: Store },
        ...(can('catalogue.manage')
            ? [{ title: tk('Admin'), href: '/admin', icon: ShieldCheck }]
            : []),
    ];

    return (
        <Sidebar collapsible="icon" variant="inset" className="vt-sidebar">
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
