<script lang="ts">
    import type { Snippet } from 'svelte';
    import { Link, page } from '@inertiajs/svelte';
    import AppLogoIcon from '@/components/AppLogoIcon.svelte';
    import BottomNav from '@/components/BottomNav.svelte';
    import UserMenuContent from '@/components/UserMenuContent.svelte';
    import VersionCheck from '@/components/VersionCheck.svelte';
    import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
    import {
        DropdownMenu,
        DropdownMenuContent,
        DropdownMenuTrigger,
    } from '@/components/ui/dropdown-menu';
    import { Toaster } from '@/components/ui/sonner';
    import { getInitials } from '@/lib/initials';
    import { dashboard } from '@/routes';
    import type { BreadcrumbItem, User } from '@/types';

    let {
        breadcrumbs = [],
        children,
    }: {
        breadcrumbs?: BreadcrumbItem[];
        children?: Snippet;
    } = $props();

    const user = $derived(page.props.auth.user);
</script>

<div class="flex h-dvh flex-col bg-background">
    <header class="sticky top-0 z-30 border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80">
        <div class="mx-auto flex h-14 w-full max-w-lg items-center justify-between px-4">
            <Link href={dashboard()} class="flex items-center gap-2 font-bold">
                <AppLogoIcon class="size-7 fill-current text-primary" />
                <span class="text-lg tracking-tight">BichoFit</span>
            </Link>

            {#if user}
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        {#snippet children(props)}
                            <button
                                {...props}
                                class="relative flex size-9 items-center justify-center overflow-hidden rounded-full border bg-muted transition-colors hover:bg-accent"
                                aria-label="Menú de usuario"
                            >
                                <Avatar class="size-9">
                                    {#if user.avatar}
                                        <AvatarImage src={user.avatar} alt={user.name} />
                                    {/if}
                                    <AvatarFallback>{getInitials(user.name)}</AvatarFallback>
                                </Avatar>
                            </button>
                        {/snippet}
                    </DropdownMenuTrigger>
                    <DropdownMenuContent class="w-56" align="end">
                        <UserMenuContent {user} />
                    </DropdownMenuContent>
                </DropdownMenu>
            {/if}
        </div>
    </header>

    <main class="flex-1 overflow-y-auto overscroll-contain">
        <div class="mx-auto w-full max-w-lg pb-28">
            {@render children?.()}
        </div>
    </main>

    <BottomNav />
    <VersionCheck />
    <Toaster />
</div>
