<script module lang="ts">
    import { dashboard } from '@/routes';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Hoy',
                href: dashboard(),
            },
        ],
    };
</script>

<script lang="ts">
    import { Link, router } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent } from '@/components/ui/card';
    import { show, store as startSession } from '@/routes/workout-sessions';

    interface RecentSession {
        id: number;
        date: string;
        exercises: number;
        sets: number;
        volumeKg: number;
    }

    let {
        activeSession = null,
        week = { sessions: 0, volumeKg: 0 },
        recentSessions = [],
    }: {
        activeSession?: { id: number; started_at: string } | null;
        week?: { sessions: number; volumeKg: number };
        recentSessions?: RecentSession[];
    } = $props();

    function formatDate(iso: string): string {
        return new Intl.DateTimeFormat('es', { weekday: 'short', day: 'numeric', month: 'short' }).format(
            new Date(iso + 'T12:00:00'),
        );
    }

    const sinceLabel = $derived.by(() => {
        if (!activeSession) return '';
        const minutes = Math.floor((Date.now() - new Date(activeSession.started_at).getTime()) / 60000);
        return minutes < 60 ? `${minutes} min` : `${Math.floor(minutes / 60)} h ${minutes % 60} min`;
    });
</script>

<AppHead title="Hoy" />

<div class="flex flex-col gap-6 p-4">
    <header>
        <h1 class="text-2xl font-bold">Hoy</h1>
        <p class="text-sm text-muted-foreground">Tu entrenamiento y tu semana de un vistazo.</p>
    </header>

    {#if activeSession}
        <Card class="border-primary/40">
            <CardContent class="flex flex-col items-center gap-3 py-8 text-center">
                <p class="text-lg font-semibold">Tienes una sesión en curso</p>
                <p class="text-sm text-muted-foreground">Empezada hace {sinceLabel}</p>
                <Button size="lg" href={show({ session: activeSession.id }).url}>Continuar entrenamiento</Button>
            </CardContent>
        </Card>
    {:else}
        <Card>
            <CardContent class="flex flex-col items-center gap-3 py-8 text-center">
                <p class="text-lg font-semibold">¿Listo para entrenar?</p>
                <p class="text-sm text-muted-foreground">Registra tus series con reps y peso en el momento.</p>
                <Button size="lg" onclick={() => router.post(startSession().url)}>Empezar entrenamiento</Button>
            </CardContent>
        </Card>
    {/if}

    <div class="grid grid-cols-2 gap-3">
        <Card>
            <CardContent class="py-4">
                <p class="text-sm font-medium text-muted-foreground">Sesiones esta semana</p>
                <p class="text-3xl font-bold">{week.sessions}</p>
            </CardContent>
        </Card>
        <Card>
            <CardContent class="py-4">
                <p class="text-sm font-medium text-muted-foreground">Volumen semanal</p>
                <p class="text-3xl font-bold">
                    {week.volumeKg.toLocaleString('es')}
                    <span class="text-base font-normal text-muted-foreground">kg</span>
                </p>
            </CardContent>
        </Card>
    </div>

    <section class="flex flex-col gap-3">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold">Últimas sesiones</h2>
            <Link href="/history" class="text-sm text-muted-foreground underline-offset-4 hover:underline">
                Ver historial
            </Link>
        </div>
        {#each recentSessions as s (s.id)}
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
                    Aún no hay sesiones terminadas. ¡Empieza la primera!
                </CardContent>
            </Card>
        {/each}
    </section>
</div>
