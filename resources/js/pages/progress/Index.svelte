<script module lang="ts">
    import { show } from '@/routes/progress';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Progreso',
                href: show(),
            },
        ],
    };
</script>

<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import Chart from 'chart.js/auto';
    import AppHead from '@/components/AppHead.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent } from '@/components/ui/card';
    import { cn } from '@/lib/utils';

    interface ExerciseSummary {
        exerciseId: number;
        name: string;
        sessions: number;
        prWeight: number;
        best1Rm: number;
    }

    interface ProgressionPoint {
        date: string;
        topWeight: number;
        best1Rm: number;
    }

    interface SelectedExercise {
        exerciseId: number;
        name: string;
        sessions: number;
        prWeight: number;
        best1Rm: number;
        series: ProgressionPoint[];
    }

    interface BodyCompPoint {
        date: string;
        weightKg: number | null;
        bodyFatPct: number | null;
        muscleMassKg: number | null;
        waterPct?: number | null;
    }

    let {
        weeklyVolume = [],
        exercises = [],
        selected = null,
        bodyComp = [],
    }: {
        weeklyVolume?: { label: string; volume: number }[];
        exercises?: ExerciseSummary[];
        selected?: SelectedExercise | null;
        bodyComp?: BodyCompPoint[];
    } = $props();

        // --- gráfica: volumen semanal ---
    let volumeCanvas: HTMLCanvasElement | undefined = $state();
    let exerciseCanvas: HTMLCanvasElement | undefined = $state();
    let bodyCanvas: HTMLCanvasElement | undefined = $state();

    $effect(() => {
        if (!volumeCanvas || weeklyVolume.length === 0) return;

        const chart = new Chart(volumeCanvas, {
            type: 'bar',
            data: {
                labels: weeklyVolume.map((week) => week.label),
                datasets: [
                    {
                        label: 'Volumen (kg)',
                        data: weeklyVolume.map((week) => Math.round(week.volume)),
                        backgroundColor: 'rgba(99, 102, 241, 0.55)',
                        borderRadius: 6,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { maxTicksLimit: 5 } } },
            },
        });

        return () => chart.destroy();
    });

    // --- gráfica: progresión del ejercicio seleccionado ---
    $effect(() => {
        if (!exerciseCanvas || !selected || selected.series.length === 0) return;

        const chart = new Chart(exerciseCanvas, {
            type: 'line',
            data: {
                labels: selected.series.map((point) =>
                    new Intl.DateTimeFormat('es', { day: 'numeric', month: 'short' }).format(
                        new Date(point.date + 'T12:00:00'),
                    ),
                ),
                datasets: [
                    {
                        label: 'Peso máximo (kg)',
                        data: selected.series.map((point) => point.topWeight),
                        borderColor: 'rgba(99, 102, 241, 1)',
                        backgroundColor: 'rgba(99, 102, 241, 0.12)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 4,
                    },
                    {
                        label: '1RM est.',
                        data: selected.series.map((point) => Math.round(point.best1Rm * 10) / 10),
                        borderColor: 'rgba(165, 180, 252, 1)',
                        borderDash: [6, 4],
                        tension: 0.3,
                        pointRadius: 3,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true, ticks: { maxTicksLimit: 6 } } },
            },
        });

        return () => chart.destroy();
    });

    // --- gráfica: composición corporal (báscula) ---
    $effect(() => {
        if (!bodyCanvas || bodyComp.length < 2) return;

        const labels = bodyComp.map((point) =>
            new Intl.DateTimeFormat('es', { day: 'numeric', month: 'short' }).format(
                new Date(point.date + 'T12:00:00'),
            ),
        );

        const chart = new Chart(bodyCanvas, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Peso (kg)',
                        data: bodyComp.map((p) => p.weightKg),
                        borderColor: 'rgba(79, 70, 229, 1)',
                        tension: 0.3,
                        pointRadius: 4,
                    },
                    {
                        label: 'Músculo (kg)',
                        data: bodyComp.map((p) => p.muscleMassKg),
                        borderColor: 'rgba(129, 140, 248, 1)',
                        tension: 0.3,
                    },
                    {
                        label: 'Grasa (%)',
                        data: bodyComp.map((p) => p.bodyFatPct),
                        borderColor: 'rgba(199, 210, 254, 1)',
                        yAxisID: 'y1',
                        borderDash: [6, 4],
                        tension: 0.3,
                    },
                    {
                        label: 'Agua (%)',
                        data: bodyComp.map((p) => p.waterPct ?? null),
                        borderColor: 'rgba(167, 139, 250, 1)',
                        yAxisID: 'y1',
                        borderDash: [3, 3],
                        tension: 0.3,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    y: { beginAtZero: false, position: 'left', title: { display: true, text: 'kg' } },
                    y1: { beginAtZero: false, position: 'right', title: { display: true, text: '%' }, grid: { drawOnChartArea: false } },
                },
            },
        });

        return () => chart.destroy();
    });

    const prs = $derived([...exercises].sort((a, b) => b.best1Rm - a.best1Rm).slice(0, 8));
</script>

<AppHead title="Progreso" />

<div class="flex flex-col gap-4 p-4">
    <header>
        <h1 class="text-2xl font-bold">Progreso</h1>
        <p class="text-sm text-muted-foreground">Tu evolución semana a semana.</p>
    </header>

    <Card>
        <CardContent class="flex flex-col gap-2 py-4">
            <p class="text-sm font-semibold">Volumen semanal</p>
            <p class="text-xs text-muted-foreground">Kilos movidos por semana (últimas 10)</p>
            <div class="h-48">
                <canvas bind:this={volumeCanvas}></canvas>
            </div>
        </CardContent>
    </Card>

    <Card>
        <CardContent class="flex flex-col gap-2 py-4">
            <div class="flex items-center justify-between gap-2">
                <p class="text-sm font-semibold">Composición corporal</p>
                <Button variant="secondary" size="sm" class="shrink-0" onclick={() => router.get('/body-scans')}>
                    {bodyComp.length > 0 ? 'Nueva medición' : 'Escanear báscula'}
                </Button>
            </div>
            {#if bodyComp.length >= 2}
                <div class="h-52">
                    <canvas bind:this={bodyCanvas}></canvas>
                </div>
            {:else}
                <p class="text-xs text-muted-foreground">
                    {bodyComp.length === 1
                        ? 'Con una medición más verás la evolución de peso, músculo y grasa.'
                        : 'Manda la captura de tu báscula y registra tu composición corporal.'}
                </p>
            {/if}
        </CardContent>
    </Card>

    {#if selected}
        <Card>
            <CardContent class="flex flex-col gap-2 py-4">
                <p class="text-sm font-semibold">{selected.name}</p>
                <p class="text-xs text-muted-foreground">
                    PR {selected.prWeight.toLocaleString('es')} kg · 1RM est. {selected.best1Rm.toLocaleString('es')} kg
                    · {selected.sessions} sesiones
                </p>
                <div class="h-56">
                    <canvas bind:this={exerciseCanvas}></canvas>
                </div>
            </CardContent>
        </Card>
    {/if}

    {#if exercises.length > 1}
        <div class="flex flex-col gap-2">
            <p class="px-1 text-xs font-medium tracking-wide text-muted-foreground uppercase">
                Cambiar ejercicio
            </p>
            <div class="flex flex-wrap gap-2">
                {#each exercises as exercise (exercise.exerciseId)}
                    <button
                        class={cn(
                            'rounded-full border px-3 py-1.5 text-xs font-medium transition-all active:scale-95',
                            selected?.exerciseId === exercise.exerciseId
                                ? 'border-primary bg-primary text-primary-foreground'
                                : 'bg-background text-muted-foreground hover:bg-muted',
                        )}
                        onclick={() => router.get(`/progress/${exercise.exerciseId}`)}
                    >
                        {exercise.name}
                    </button>
                {/each}
            </div>
        </div>
    {/if}

    <Card>
        <CardContent class="flex flex-col divide-y p-1">
            <p class="px-3 pt-3 pb-2 text-sm font-semibold">Récords (1RM est.)</p>
            {#each prs as exercise (exercise.exerciseId)}
                <div class="flex items-center justify-between gap-3 px-3 py-2.5">
                    <button
                        class="min-w-0 flex-1 truncate text-left text-sm font-medium"
                        onclick={() => router.get(`/progress/${exercise.exerciseId}`)}
                    >
                        {exercise.name}
                    </button>
                    <div class="flex shrink-0 items-center gap-2">
                        <Badge variant="secondary" class="tabular-nums">
                            {exercise.best1Rm.toLocaleString('es')} kg
                        </Badge>
                        <span class="text-xs tabular-nums text-muted-foreground">
                            {exercise.prWeight.toLocaleString('es')} kg
                        </span>
                    </div>
                </div>
            {:else}
                <p class="px-3 py-6 text-center text-sm text-muted-foreground">
                    Entrena un par de veces y aquí aparecen tus récords.
                </p>
            {/each}
        </CardContent>
    </Card>
</div>
