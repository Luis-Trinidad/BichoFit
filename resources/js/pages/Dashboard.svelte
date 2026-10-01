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
    import { cn } from '@/lib/utils';
    import Play from '@lucide/svelte/icons/play';

    interface RecentSession {
        id: number;
        date: string;
        exercises: number;
        sets: number;
        volumeKg: number;
    }

    interface RoutineRow {
        id: number;
        name: string;
        exercises: number;
    }

    let {
        activeSession = null,
        week = { sessions: 0, volumeKg: 0 },
        recentSessions = [],
        routines = [],
    }: {
        activeSession?: { id: number; started_at: string } | null;
        week?: { sessions: number; volumeKg: number };
        recentSessions?: RecentSession[];
        routines?: RoutineRow[];
    } = $props();

    function formatDate(iso: string): string {
        return new Intl.DateTimeFormat('es', { weekday: 'short', day: 'numeric', month: 'short' }).format(
            new Date(iso + 'T12:00:00'),
        );
    }

    // --- arranque con cuenta regresiva ---
    let selectedRoutineId = $state<number | null>(null); // null = entrenamiento libre
    let countdown = $state<number | null>(null);

    const selectedRoutine = $derived(routines.find((r) => r.id === selectedRoutineId) ?? null);

    function comenzar() {
        if (countdown !== null) return;
        countdown = 3;
    }

    $effect(() => {
        if (countdown === null) return;
        const timer = setTimeout(() => {
            if (countdown > 1) {
                countdown -= 1;

                return;
            }
            countdown = null;
            router.post(
                startSession().url,
                selectedRoutineId ? { routine_id: selectedRoutineId } : {},
            );
        }, 1000);

        return () => clearTimeout(timer);
    });

    // --- contador en vivo de la sesión activa ---
    let nowTick = $state(Date.now());

    $effect(() => {
        const interval = setInterval(() => (nowTick = Date.now()), 30_000);

        return () => clearInterval(interval);
    });

    const sinceLabel = $derived.by(() => {
        void nowTick;
        if (!activeSession) return '';
        const minutes = Math.max(
            0,
            Math.floor((nowTick - new Date(activeSession.started_at).getTime()) / 60000),
        );

        return minutes < 60 ? `hace ${minutes} min` : `hace ${Math.floor(minutes / 60)} h ${minutes % 60} min`;
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
                <p class="text-sm tabular-nums text-muted-foreground">Empezada {sinceLabel}</p>
                <Button size="lg" asChild>
                    {#snippet children(props)}
                        <Link {...props} href={show({ session: activeSession.id }).url}>
                            Continuar entrenamiento
                        </Link>
                    {/snippet}
                </Button>
            </CardContent>
        </Card>
    {:else}
        <Card>
            <CardContent class="flex flex-col gap-4 py-6">
                <div class="flex flex-col items-center gap-1 text-center">
                    <p class="text-lg font-semibold">¿Listo para entrenar?</p>
                    <p class="text-sm text-muted-foreground">Elige tu rutina o entrena libre.</p>
                </div>

                <div class="flex flex-col gap-2">
                    <p class="text-xs font-medium tracking-wide text-muted-foreground uppercase">Rutina</p>
                    <div class="flex flex-wrap gap-2">
                        <button
                            class={cn(
                                'rounded-full border px-3 py-1.5 text-sm font-medium transition-all active:scale-95',
                                selectedRoutineId === null
                                    ? 'border-primary bg-primary text-primary-foreground'
                                    : 'bg-background text-muted-foreground hover:bg-muted',
                            )}
                            onclick={() => (selectedRoutineId = null)}
                        >
                            Libre
                        </button>
                        {#each routines as routine (routine.id)}
                            <button
                                class={cn(
                                    'rounded-full border px-3 py-1.5 text-sm font-medium transition-all active:scale-95',
                                    selectedRoutineId === routine.id
                                        ? 'border-primary bg-primary text-primary-foreground'
                                        : 'bg-background text-muted-foreground hover:bg-muted',
                                )}
                                onclick={() => (selectedRoutineId = routine.id)}
                            >
                                {routine.name}
                                <span class="ml-1 opacity-70">{routine.exercises}</span>
                            </button>
                        {/each}
                    </div>
                </div>

                <Button size="lg" class="h-14 gap-2 text-base font-semibold" onclick={comenzar}>
                    <Play class="size-5 fill-current" />
                    Comenzar{selectedRoutine ? ` · ${selectedRoutine.name}` : ''}
                </Button>
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

{#if countdown !== null}
    <div class="fixed inset-0 z-50 flex flex-col items-center justify-center gap-6 bg-background">
        <p class="text-sm font-medium tracking-wide text-muted-foreground uppercase">
            {selectedRoutine ? selectedRoutine.name : 'Entrenamiento libre'}
        </p>
        {#key countdown}
            <div
                class="text-9xl font-black tabular-nums text-primary"
                style="animation: countdown-pop 1s ease-out forwards"
            >
                {countdown}
            </div>
        {/key}
        <Button variant="ghost" class="text-muted-foreground" onclick={() => (countdown = null)}>
            Cancelar
        </Button>
    </div>
{/if}

<style>
    @keyframes countdown-pop {
        0% {
            transform: scale(0.4);
            opacity: 0;
        }
        25% {
            transform: scale(1.1);
            opacity: 1;
        }
        100% {
            transform: scale(0.9);
            opacity: 0.85;
        }
    }
</style>
