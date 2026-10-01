<script lang="ts">
    import { Link, router } from '@inertiajs/svelte';
    import Dumbbell from '@lucide/svelte/icons/dumbbell';
    import History from '@lucide/svelte/icons/history';
    import Tags from '@lucide/svelte/icons/tags';
    import type { Snippet } from 'svelte';
    import AppLogo from '@/components/AppLogo.svelte';
    import NavFooter from '@/components/NavFooter.svelte';
    import NavMain from '@/components/NavMain.svelte';
    import NavUser from '@/components/NavUser.svelte';
    import {
        Sidebar,
        SidebarContent,
        SidebarFooter,
        SidebarHeader,
        SidebarMenu,
        SidebarMenuButton,
        SidebarMenuItem,
    } from '@/components/ui/sidebar';
    import { toUrl } from '@/lib/utils';
    import { dashboard } from '@/routes';
    import { index as exercisesIndex } from '@/routes/exercises';
    import { index as historyIndex } from '@/routes/history';
    import type { NavItem } from '@/types';
    import { useSidebar } from '@/components/ui/sidebar';

    let {
        children,
    }: {
        children?: Snippet;
    } = $props();

    // En móvil el menú es un overlay: cerrarlo al navegar, como en una app nativa
    const { setOpenMobile } = useSidebar();

    $effect(() => {
        return router.on('navigate', () => setOpenMobile(false));
    });

    const mainNavItems: NavItem[] = [
        {
            title: 'Hoy',
            href: dashboard(),
            icon: Dumbbell,
        },
        {
            title: 'Historial',
            href: historyIndex(),
            icon: History,
        },
        {
            title: 'Ejercicios',
            href: exercisesIndex(),
            icon: Tags,
        },
    ];

    const footerNavItems: NavItem[] = [];
</script>

<Sidebar collapsible="icon" variant="inset">
    <SidebarHeader>
        <SidebarMenu>
            <SidebarMenuItem>
                <SidebarMenuButton size="lg" asChild>
                    {#snippet children(props)}
                        <Link
                            {...props}
                            href={toUrl(dashboard())}
                            class={props.class}
                        >
                            <AppLogo />
                        </Link>
                    {/snippet}
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
        <NavMain items={mainNavItems} />
    </SidebarContent>

    <SidebarFooter>
        <NavFooter items={footerNavItems} />
        <NavUser />
    </SidebarFooter>
</Sidebar>
{@render children?.()}
