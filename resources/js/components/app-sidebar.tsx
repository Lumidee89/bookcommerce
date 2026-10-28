import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { can } from '@/lib/format';
import { type NavGroup, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import {
    Award,
    BookCopy,
    BookOpen,
    CalendarClock,
    CalendarDays,
    ClipboardList,
    ExternalLink,
    FileText,
    Images,
    Inbox,
    LayoutGrid,
    Mail,
    Mic2,
    Newspaper,
    Quote,
    Send,
    Settings,
    ShieldCheck,
    ShoppingCart,
    Tags,
    Truck,
    UserRound,
    Users,
    Video,
} from 'lucide-react';
import AppLogo from './app-logo';

const groups: NavGroup[] = [
    { title: 'Overview', items: [{ title: 'Dashboard', url: '/admin', icon: LayoutGrid }] },
    {
        title: 'Sales',
        items: [
            { title: 'Orders', url: '/admin/orders', icon: ShoppingCart, permission: 'manage orders' },
            { title: 'Books', url: '/admin/books', icon: BookOpen, permission: 'manage books' },
            { title: 'Book categories', url: '/admin/book-categories', icon: Tags, permission: 'manage books' },
            { title: 'Delivery options', url: '/admin/delivery-options', icon: Truck, permission: 'manage orders' },
        ],
    },
    {
        title: 'Consultations',
        items: [
            { title: 'Bookings', url: '/admin/bookings', icon: CalendarClock, permission: 'manage bookings' },
            { title: 'Services', url: '/admin/services', icon: ClipboardList, permission: 'manage consultations' },
            { title: 'Availability', url: '/admin/availability', icon: CalendarDays, permission: 'manage consultations' },
        ],
    },
    {
        title: 'Communications',
        items: [
            { title: 'Enquiries', url: '/admin/enquiries', icon: Inbox, permission: 'manage enquiries' },
            { title: 'Speaking invitations', url: '/admin/invitations', icon: Mic2, permission: 'manage enquiries' },
            { title: 'Newsletters', url: '/admin/newsletters', icon: Send, permission: 'manage subscribers' },
            { title: 'Subscribers', url: '/admin/subscribers', icon: Mail, permission: 'manage subscribers' },
        ],
    },
    {
        title: 'Publishing',
        items: [
            { title: 'Articles', url: '/admin/articles', icon: Newspaper, permission: 'manage articles' },
            { title: 'Videos', url: '/admin/videos', icon: Video, permission: 'manage media' },
            { title: 'Photo albums', url: '/admin/albums', icon: Images, permission: 'manage media' },
            { title: 'Events', url: '/admin/events', icon: CalendarDays, permission: 'manage events' },
        ],
    },
    {
        title: 'Profile',
        items: [
            { title: 'Biography', url: '/admin/profile-sections', icon: UserRound, permission: 'manage content' },
            { title: 'Awards & credentials', url: '/admin/awards', icon: Award, permission: 'manage profile' },
            { title: 'Testimonials', url: '/admin/testimonials', icon: Quote, permission: 'manage profile' },
            { title: 'Media kit', url: '/admin/speaker-documents', icon: BookCopy, permission: 'manage content' },
            { title: 'Pages & policies', url: '/admin/pages', icon: FileText, permission: 'manage content' },
        ],
    },
    {
        title: 'Settings',
        items: [
            { title: 'Website settings', url: '/admin/settings', icon: Settings, permission: 'manage settings' },
            { title: 'Users & roles', url: '/admin/users', icon: Users, permission: 'manage users' },
            { title: 'Audit log', url: '/admin/audit-logs', icon: ShieldCheck, permission: 'view audit logs' },
        ],
    },
];

export function AppSidebar() {
    const page = usePage<SharedData>();
    const permissions = page.props.auth.user?.permissions;
    const path = page.url.split('?')[0];

    const isActive = (url: string) => (url === '/admin' ? path === '/admin' : path === url || path.startsWith(url + '/'));

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/admin" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                {groups.map((group) => {
                    const items = group.items.filter((item) => can(permissions, item.permission));
                    if (items.length === 0) return null;
                    return (
                        <SidebarGroup key={group.title} className="px-2 py-0">
                            <SidebarGroupLabel>{group.title}</SidebarGroupLabel>
                            <SidebarMenu>
                                {items.map((item) => (
                                    <SidebarMenuItem key={item.url}>
                                        <SidebarMenuButton asChild isActive={isActive(item.url)} tooltip={{ children: item.title }}>
                                            <Link href={item.url} prefetch>
                                                {item.icon && <item.icon />}
                                                <span>{item.title}</span>
                                            </Link>
                                        </SidebarMenuButton>
                                    </SidebarMenuItem>
                                ))}
                            </SidebarMenu>
                        </SidebarGroup>
                    );
                })}
            </SidebarContent>

            <SidebarFooter>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton asChild>
                            <a href="/" target="_blank" rel="noopener">
                                <ExternalLink />
                                <span>View website</span>
                            </a>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
