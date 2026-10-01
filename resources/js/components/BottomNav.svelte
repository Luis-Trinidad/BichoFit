<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import Dumbbell from '@lucide/svelte/icons/dumbbell';
    import History from '@lucide/svelte/icons/history';
    import ClipboardList from '@lucide/svelte/icons/clipboard-list';
    import Tags from '@lucide/svelte/icons/tags';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import { cn } from '@/lib/utils';
    import { dashboard } from '@/routes';
    import { index as exercisesIndex } from '@/routes/exercises';
    import { index as historyIndex } from '@/routes/history';
    import { index as routinesIndex } from '@/routes/routines';

    const { currentUrl, isCurrentOrParentUrl } = currentUrlState();

    const items = [
        { title: 'Hoy', href: dashboard(), icon: Dumbbell },
        { title: 'Rutinas', href: routinesIndex(), icon: ClipboardList },
        { title: 'Historial', href: historyIndex(), icon: History },
        { title: 'Ejercicios', href: exercisesIndex(), icon: Tags },
    ];
</script>

<nav
    aria-label="Navegación principal"
    class="fixed inset-x-0 bottom-0 z-40 flex justify-center px-4 pb-[max(env(safe-area-inset-bottom),0.9rem)]"
>
    <div
        class="flex w-full max-w-md items-center gap-1 rounded-full border bg-background/90 p-1.5 shadow-lg shadow-black/10 ring-1 ring-black/5 backdrop-blur dark:ring-white/10"
    >
        {#each items as item (item.title)}
            {@const active = isCurrentOrParentUrl(item.href, currentUrl)}
            <Link
                href={item.href}
                aria-current={active ? 'page' : undefined}
                class={cn(
                    'flex h-11 flex-1 flex-row items-center justify-center gap-1.5 rounded-full px-2 text-xs font-medium transition-all select-none',
                    active
                        ? 'bg-primary text-primary-foreground shadow-sm'
                        : 'text-muted-foreground hover:bg-muted hover:text-foreground active:scale-95',
                )}
            >
                <item.icon class="size-4 shrink-0" />
                <span class="truncate">{item.title}</span>
            </Link>
        {/each}
    </div>
</nav>
