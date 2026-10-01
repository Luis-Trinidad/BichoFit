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
    import Play from '@lucide/svelte/icons/play';
    import AppHead from '@/components/AppHead.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent } from '@/components/ui/card';
    import { cn } from '@/lib/utils';
    import { show, store as startSession } from '@/routes/workout-sessions';

    interface RoutineItemPreview {
        name: string;
        target: string | null;
        imageUrl?: string | null;
    }

    interface RoutineDay {
        day: number;
        dayName: string;
        items: RoutineItemPreview[];
    }

    interface RoutineRow {
        id: number;
        name: string;
        days: RoutineDay[];
    }

    interface RecentSession {
        id: number;
        date: string;
        exercises: number;
        sets: number;
        volumeKg: number;
    }

    let {
        activeSession = null,
        week = { sessions: 0, streak: 0 },
        recentSessions = [],
        routines = [],
    }: {
        activeSession?: { id: number; started_at: string } | null;
        week?: { sessions: number; streak: number };
        recentSessions?: RecentSession[];
        routines?: RoutineRow[];
    } = $props();

    function formatDate(iso: string): string {
        return new Intl.DateTimeFormat('es', { weekday: 'short', day: 'numeric', month: 'short' }).format(
            new Date(iso + 'T12:00:00'),
        );
    }

    // --- asistente de arranque: elegir → ver → confirmar ---
    let wizardOpen = $state(false);
    let pickedId = $state<number | null>(null); // null = entrenamiento libre
    let pickedDay = $state<number | null>(null);
    let step = $state<'elegir' | 'dias' | 'preview'>('elegir');
    let countdown = $state<number | null>(null);

    const pickedRoutine = $derived(routines.find((r) => r.id === pickedId) ?? null);
    const pickedRoutineDay = $derived(
        pickedRoutine?.days.find((d) => d.day === pickedDay) ?? null,
    );

    function abrirWizard() {
        if (countdown !== null) return;
        pickedId = null;
        pickedDay = null;
        step = 'elegir';
        wizardOpen = true;
    }

    function lanzar(routineId: number | null, day: number | null = null) {
        pickedId = routineId;
        pickedDay = day;
        wizardOpen = false;
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
                pickedId !== null
                    ? { routine_id: pickedId, routine_day: pickedDay ?? undefined }
                    : {},
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
            <CardContent class="flex flex-col items-center gap-3 py-8 text-center">
                <p class="text-lg font-semibold">¿Listo para entrenar?</p>
                <p class="text-sm text-muted-foreground">Elige tu rutina y dale con todo.</p>
                <Button size="lg" class="h-14 w-full max-w-xs gap-2 text-base font-semibold" onclick={abrirWizard}>
                    <Play class="size-5 fill-current" />
                    Comenzar
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
                <p class="text-sm font-medium text-muted-foreground">Racha 🔥</p>
                <p class="text-3xl font-bold">
                    {week.streak}
                    <span class="text-base font-normal text-muted-foreground">
                        {week.streak === 1 ? 'semana' : 'semanas'}
                    </span>
                </p>
                <p class="text-xs text-muted-foreground">3+ entrenos/semana</p>
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

{#if wizardOpen}
    <div class="fixed inset-0 z-50 flex flex-col bg-background">
        <header class="flex items-center justify-between border-b px-4 py-3">
            <button
                class="text-sm text-muted-foreground underline-offset-4 hover:underline"
                onclick={() => {
                    if (step === 'preview') {
                        step = 'dias';
                    } else if (step === 'dias') {
                        step = 'elegir';
                    } else {
                        wizardOpen = false;
                    }
                }}
            >
                ← {step === 'preview' ? 'Cambiar día' : step === 'dias' ? 'Cambiar rutina' : 'Cerrar'}
            </button>
            <p class="text-sm font-semibold">
                {step === 'preview'
                    ? `${pickedRoutine?.name ?? ''} · ${pickedRoutineDay?.dayName ?? ''}`
                    : step === 'dias'
                        ? (pickedRoutine?.name ?? '')
                        : '¿Qué toca hoy?'}
            </p>
            <span class="w-14"></span>
        </header>

        <div class="flex-1 overflow-y-auto px-4 py-4">
            {#if step === 'elegir'}
                <div class="mx-auto flex max-w-lg flex-col gap-2">
                    <button
                        class="flex items-center justify-between rounded-xl border bg-background px-4 py-4 text-left transition-colors hover:bg-muted/60 active:scale-[0.98]"
                        onclick={() => lanzar(null)}
                    >
                        <span>
                            <span class="block font-semibold">Entrenamiento libre</span>
                            <span class="block text-xs text-muted-foreground">
                                Armas los ejercicios en el momento
                            </span>
                        </span>
                        <Play class="size-5 text-muted-foreground" />
                    </button>
                    {#each routines as routine (routine.id)}
                        <button
                            class="flex items-center justify-between rounded-xl border bg-background px-4 py-4 text-left transition-colors hover:bg-muted/60 active:scale-[0.98]"
                            onclick={() => {
                                pickedId = routine.id;
                                pickedDay = null;
                                step = routine.days.length > 1 ? 'dias' : 'preview';
                                if (routine.days.length === 1) pickedDay = routine.days[0].day;
                            }}
                        >
                            <span>
                                <span class="block font-semibold">{routine.name}</span>
                                <span class="block text-xs text-muted-foreground">
                                    {routine.days.length === 1
                                        ? `${routine.days[0].items.length} ejercicios`
                                        : `${routine.days.length} días · ${routine.days.reduce((total, day) => total + day.items.length, 0)} ejercicios`}
                                </span>
                            </span>
                            <span class="text-muted-foreground">›</span>
                        </button>
                    {/each}
                </div>
            {:else if step === 'dias' && pickedRoutine}
                <div class="mx-auto flex max-w-lg flex-col gap-2">
                    {#each pickedRoutine.days as day (day.day)}
                        <button
                            class="flex items-center justify-between rounded-xl border bg-background px-4 py-4 text-left transition-colors hover:bg-muted/60 active:scale-[0.98]"
                            onclick={() => {
                                pickedDay = day.day;
                                step = 'preview';
                            }}
                        >
                            <span>
                                <span class="block font-semibold">{day.dayName}</span>
                                <span class="block text-xs text-muted-foreground">
                                    {day.items.length} ejercicios
                                </span>
                            </span>
                            <span class="text-muted-foreground">›</span>
                        </button>
                    {/each}
                </div>
            {:else if pickedRoutineDay}
                <div class="mx-auto flex max-w-lg flex-col gap-3">
                    {#each pickedRoutineDay.items as item, index (index)}
                        <div class="flex items-center gap-3 rounded-xl border bg-background px-3 py-2.5">
                            <span class="w-5 text-center text-sm font-semibold text-muted-foreground">
                                {index + 1}
                            </span>
                            {#if item.imageUrl}
                                <img
                                    src={item.imageUrl}
                                    alt=""
                                    loading="lazy"
                                    class="size-11 shrink-0 rounded-md bg-muted object-contain"
                                />
                            {:else}
                                <div class="flex size-11 shrink-0 items-center justify-center rounded-md bg-muted text-xs text-muted-foreground">
                                    {item.name.slice(0, 2)}
                                </div>
                            {/if}
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-medium">{item.name}</span>
                                {#if item.target}
                                    <span class="block text-xs text-muted-foreground">objetivo {item.target}</span>
                                {/if}
                            </span>
                        </div>
                    {:else}
                        <p class="py-6 text-center text-sm text-muted-foreground">
                            Esta rutina no tiene ejercicios todavía.
                        </p>
                    {/each}
                </div>
            {:else}
                <div class="mx-auto flex max-w-lg flex-col items-center gap-3 py-10 text-center">
                    <p class="font-semibold">Entrenamiento libre</p>
                    <p class="text-sm text-muted-foreground">
                        Al llegar a la sesión agregas ejercicios del catálogo cuando quieras.
                    </p>
                </div>
            {/if}
        </div>

        {#if step === 'preview' || (step === 'dias' && pickedRoutine && pickedRoutine.days.length === 0)}
            <div class="border-t px-4 pt-3 pb-[max(env(safe-area-inset-bottom),1rem)]">
                <div class="mx-auto max-w-lg">
                    <Button size="lg" class="h-14 w-full gap-2 text-base font-semibold" onclick={() => lanzar(pickedId, pickedDay)}>
                        <Play class="size-5 fill-current" />
                        Comenzar{pickedRoutineDay ? ` · ${pickedRoutineDay.dayName}` : ''}
                    </Button>
                </div>
            </div>
        {/if}
    </div>
{/if}

{#if countdown !== null}
    <div class="fixed inset-0 z-[60] flex flex-col items-center justify-center gap-6 bg-background">
        <p class="text-sm font-medium tracking-wide text-muted-foreground uppercase">
            {pickedRoutineDay
                ? `${pickedRoutine?.name ?? ''} · ${pickedRoutineDay.dayName}`
                : pickedRoutine
                    ? pickedRoutine.name
                    : 'Entrenamiento libre'}
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
