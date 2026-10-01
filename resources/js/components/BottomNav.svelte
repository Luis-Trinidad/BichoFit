<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import Dumbbell from '@lucide/svelte/icons/dumbbell';
    import History from '@lucide/svelte/icons/history';
    import TrendingUp from '@lucide/svelte/icons/trending-up';
    import Scale from '@lucide/svelte/icons/scale';
    import ClipboardList from '@lucide/svelte/icons/clipboard-list';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import { cn } from '@/lib/utils';
    import { dashboard } from '@/routes';
    import { index as historyIndex } from '@/routes/history';
    import { show as progressShow } from '@/routes/progress';
    import { index as bodyScansIndex } from '@/routes/body-scans';
    import { index as routinesIndex } from '@/routes/routines';

    // Mantener el objeto (no destructurar): currentUrl es un getter reactivo
    const url = currentUrlState();

    const items = [
        { title: 'Hoy', href: dashboard(), icon: Dumbbell },
        { title: 'Rutinas', href: routinesIndex(), icon: ClipboardList },
        { title: 'Historial', href: historyIndex(), icon: History },
        { title: 'Progreso', href: progressShow(), icon: TrendingUp },
        { title: 'Cuerpo', href: bodyScansIndex(), icon: Scale },
    ];
</script>

<nav
    aria-label="Navegación principal"
    class="fixed inset-x-0 bottom-0 z-40 flex justify-center px-6 pb-[max(env(safe-area-inset-bottom),1.4rem)]"
>
    <div
        class="flex w-full max-w-md items-center gap-1 rounded-full border bg-background/90 p-1.5 shadow-lg shadow-black/10 ring-1 ring-black/5 backdrop-blur dark:ring-white/10"
    >
        {#each items as item (item.title)}
            {@const active = url.isCurrentOrParentUrl(item.href, url.currentUrl)}
            <Link
                href={item.href}
                aria-current={active ? 'page' : undefined}
                class={cn(
                    'flex h-14 flex-1 flex-col items-center justify-center gap-1 rounded-full px-1 text-[10px] font-medium transition-all select-none',
                    active
                        ? 'bg-primary text-primary-foreground shadow-sm'
                        : 'text-muted-foreground hover:bg-muted hover:text-foreground active:scale-95',
                )}
            >
                <item.icon class="size-5 shrink-0" />
                <span class="max-w-full truncate">{item.title}</span>
            </Link>
        {/each}
    </div>
</nav>
