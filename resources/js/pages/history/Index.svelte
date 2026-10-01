<script module lang="ts">
    import { index } from '@/routes/history';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Historial',
                href: index(),
            },
        ],
    };
</script>

<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent } from '@/components/ui/card';
    import { show } from '@/routes/workout-sessions';

    interface SessionRow {
        id: number;
        date: string;
        exercises: number;
        sets: number;
        volumeKg: number;
    }

    interface Paginated<T> {
        data: T[];
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    }

    let { sessions }: { sessions: Paginated<SessionRow> } = $props();

    function formatDate(iso: string): string {
        return new Intl.DateTimeFormat('es', { weekday: 'long', day: 'numeric', month: 'long' }).format(
            new Date(iso + 'T12:00:00'),
        );
    }
</script>

<AppHead title="Historial" />

<div class="flex flex-col gap-4 p-4">
    <header>
        <h1 class="text-2xl font-bold">Historial</h1>
        <p class="text-sm text-muted-foreground">Todos tus entrenamientos terminados.</p>
    </header>

    <div class="flex flex-col gap-3">
        {#each sessions.data as s (s.id)}
            <Link href={show({ session: s.id }).url} class="block">
                <Card class="transition-colors hover:bg-muted/40">
                    <CardContent class="flex items-center justify-between gap-3 py-4">
                        <div>
                            <p class="font-medium capitalize">{formatDate(s.date)}</p>
                            <p class="text-sm text-muted-foreground">{s.exercises} ejercicios · {s.sets} series</p>
                        </div>
                        <Badge variant="secondary">{s.volumeKg.toLocaleString('es')} kg</Badge>
                    </CardContent>
                </Card>
            </Link>
        {:else}
            <Card>
                <CardContent class="py-6 text-center text-sm text-muted-foreground">
                    Todavía no hay entrenamientos terminados.
                </CardContent>
            </Card>
        {/each}
    </div>

    {#if sessions.last_page > 1}
        <div class="flex items-center justify-between pt-2">
            <Button variant="outline" size="sm" disabled={!sessions.prev_page_url} href={sessions.prev_page_url ?? '#'}>
                Anterior
            </Button>
            <span class="text-sm text-muted-foreground">{sessions.current_page} / {sessions.last_page}</span>
            <Button variant="outline" size="sm" disabled={!sessions.next_page_url} href={sessions.next_page_url ?? '#'}>
                Siguiente
            </Button>
        </div>
    {/if}
</div>
