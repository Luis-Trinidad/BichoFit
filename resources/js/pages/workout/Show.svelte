<script module lang="ts">
    import { dashboard } from '@/routes';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Entrenamiento',
                href: dashboard(),
            },
        ],
    };
</script>

<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import { toast } from 'svelte-sonner';
    import AppHead from '@/components/AppHead.svelte';
    import ExerciseDetailDialog from '@/components/ExerciseDetailDialog.svelte';
    import ExercisePickerDialog from '@/components/ExercisePickerDialog.svelte';
    import SetEntryForm from '@/components/SetEntryForm.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { destroy as destroySession, finish } from '@/routes/workout-sessions';
    import { destroy as destroySet, store as storeSet, update as updateSet } from '@/routes/workout-sets';

    interface SetItem {
        id: number;
        exerciseId: number;
        exerciseName: string;
        muscleGroup: string;
        gifUrl?: string | null;
        imageUrl?: string | null;
        reps: number;
        weightKg: number;
    }

    interface SessionProp {
        id: number;
        date: string;
        startedAt: string;
        finishedAt: string | null;
        notes: string | null;
        sets: SetItem[];
    }

    interface ExerciseOption {
        id: number;
        name: string;
        muscle_group: string;
    }

    let {
        session,
        routine = null,
        routinePlan = [],
        lastByExercise = {},
        exercises = [],
        routineExerciseIds = [],
    }: {
        session: SessionProp;
        routine?: { id: number; name: string } | null;
        routinePlan?: { exerciseId: number; name: string; target: string | null }[];
        lastByExercise?: Record<string, { reps: number; weightKg: number }>;
        exercises?: import('@/components/ExercisePickerDialog.svelte').ExerciseOption[];
        routineExerciseIds?: number[];
    } = $props();

    const isActive = $derived(session.finishedAt === null);

    // Plan de la rutina: orden y objetivo por ejercicio
    const routineOrder = $derived(new Map(routinePlan.map((item, index) => [item.exerciseId, index])));
    const routineTargets = $derived(new Map(routinePlan.map((item) => [item.exerciseId, item.target ?? ''])));

    /** Objetivo "4x10" / "4x8-12" → {sets:4, reps:10}; sin patrón → null. */
    function parseTarget(exerciseId: number): { sets: number; reps: number } | null {
        const target = routineTargets.get(exerciseId) ?? '';
        const match = /^(\d+)\s*[x×]\s*(\d+)(?:\s*-\s*\d+)?/i.exec(target.trim());

        return match ? { sets: Number(match[1]), reps: Number(match[2]) } : null;
    }

    function targetSets(exerciseId: number): number | null {
        return parseTarget(exerciseId)?.sets ?? null;
    }

    type BlockState = 'pendiente' | 'en-curso' | 'completado';

    function blockState(exerciseId: number, setCount: number): BlockState {
        if (setCount === 0) return 'pendiente';
        const goal = parseTarget(exerciseId);

        return goal === null || setCount >= goal.sets ? 'completado' : 'en-curso';
    }

    // Ejercicios de la sesión; si hay rutina, en su orden (los extra al final)
    interface ExerciseBlock {
        exerciseId: number;
        name: string;
        muscleGroup: string;
        gifUrl?: string | null;
        imageUrl?: string | null;
        sets: SetItem[];
    }

    const blocks = $derived.by(() => {
        const map = new Map<number, ExerciseBlock>();

        for (const set of session.sets) {
            let block = map.get(set.exerciseId);
            if (!block) {
                block = {
                    exerciseId: set.exerciseId,
                    name: set.exerciseName,
                    muscleGroup: set.muscleGroup,
                    gifUrl: set.gifUrl,
                    imageUrl: set.imageUrl,
                    sets: [],
                };
                map.set(set.exerciseId, block);
            }
            block.sets.push(set);
        }
        const list = [...map.values()];
        if (routinePlan.length > 0) {
            list.sort((a, b) => (routineOrder.get(a.exerciseId) ?? 999) - (routineOrder.get(b.exerciseId) ?? 999));
        }

        return list;
    });

    // Progreso de la rutina: completados sobre el total del plan
    const routineProgress = $derived.by(() => {
        if (routinePlan.length === 0) return null;
        const done = routinePlan.filter((item) => {
            const block = blocks.find((b) => b.exerciseId === item.exerciseId);

            return block ? blockState(item.exerciseId, block.sets.length) === 'completado' : false;
        }).length;

        return { done, total: routinePlan.length };
    });

    const totalVolume = $derived(session.sets.reduce((sum, s) => sum + s.reps * s.weightKg, 0));

    // Ejercicios seleccionados sin series todavía; al iniciar desde una rutina
    // se preparan los de la rutina (en orden) para registrar en el momento
    let stagedIds = $state<number[]>([...routineExerciseIds]);
    const stagedBlocks = $derived(
        stagedIds
            .filter((id) => !blocks.some((b) => b.exerciseId === id))
            .sort((a, b) => (routineOrder.get(a) ?? 999) - (routineOrder.get(b) ?? 999))
            .map((id) => {
                const plan = routinePlan.find((item) => item.exerciseId === id);
                const option = exercises.find((e) => e.id === id);
                return {
                    exerciseId: id,
                    name: plan?.name ?? option?.name ?? '',
                    muscleGroup: option?.muscle_group ?? '',
                    gifUrl: option?.gifUrl ?? null,
                    imageUrl: option?.imageUrl ?? null,
                    sets: [] as SetItem[],
                };
            }),
    );

    // Lista única de tarjetas: hechas + pendientes, en orden de rutina si la hay
    const renderBlocks = $derived.by(() => {
        const all = [...blocks, ...stagedBlocks];
        if (routinePlan.length === 0) return all;

        return all.sort(
            (a, b) => (routineOrder.get(a.exerciseId) ?? 999) - (routineOrder.get(b.exerciseId) ?? 999),
        );
    });

    let pickerOpen = $state(false);
    let detailExerciseId = $state<number | null>(null);

    // Formulario rápido por ejercicio: reps/peso pre-llenados de la última serie
    let draft = $state<Record<number, { reps: string; weight: string }>>({});

    function prefill(exerciseId: number): { reps: string; weight: string } {
        const inSession = session.sets.filter((s) => s.exerciseId === exerciseId).at(-1);
        const previous = lastByExercise[String(exerciseId)];
        const goal = parseTarget(exerciseId);

        return {
            reps: String(inSession?.reps ?? goal?.reps ?? previous?.reps ?? ''),
            weight: String(inSession?.weightKg ?? previous?.weightKg ?? ''),
        };
    }

    function addSet(exerciseId: number) {
        const values = draft[exerciseId] ?? prefill(exerciseId);
        const reps = Number(values.reps);
        const weight = Number(values.weight);

        if (!reps || Number.isNaN(weight)) {
            toast.error('Registra repeticiones y peso (0 si es peso corporal).');
            return;
        }

        router.post(
            storeSet({ session: session.id }).url,
            { exercise_id: exerciseId, reps, weight_kg: weight },
            {
                preserveScroll: true,
                onSuccess: () => {
                    draft[exerciseId] = prefill(exerciseId);
                    stagedIds = stagedIds.filter((id) => id !== exerciseId);
                },
            },
        );
    }

    let editingSetId = $state<number | null>(null);
    let editDraft = $state({ reps: '', weight: '' });

    function startEdit(set: SetItem) {
        editingSetId = set.id;
        editDraft = { reps: String(set.reps), weight: String(set.weightKg) };
    }

    function saveEdit(setId: number) {
        router.patch(
            updateSet({ set: setId }).url,
            { reps: Number(editDraft.reps), weight_kg: Number(editDraft.weight) },
            { preserveScroll: true, onSuccess: () => (editingSetId = null) },
        );
    }

    function removeSet(setId: number) {
        router.delete(destroySet({ set: setId }).url, { preserveScroll: true });
    }

    function finishWorkout() {
        router.post(finish({ session: session.id }).url);
    }

    function deleteSession() {
        if (confirm('¿Borrar el entrenamiento completo? Esta acción no se puede deshacer.')) {
            router.delete(destroySession({ session: session.id }).url);
        }
    }

    // Cronómetro de la sesión en curso
    let elapsed = $state('');

    $effect(() => {
        if (!isActive) return;
        const started = new Date(session.startedAt).getTime();
        const tick = () => {
            elapsed = formatElapsed(Date.now() - started);
        };
        tick();
        const interval = setInterval(tick, 1000);
        return () => clearInterval(interval);
    });

    function formatElapsed(ms: number): string {
        const totalSeconds = Math.floor(ms / 1000);
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;
        const mm = String(minutes).padStart(2, '0');
        const ss = String(seconds).padStart(2, '0');

        return hours > 0 ? `${hours}:${mm}:${ss}` : `${mm}:${ss}`;
    }

    const dateLabel = $derived(
        new Intl.DateTimeFormat('es', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
        }).format(new Date(session.date + 'T12:00:00')),
    );
</script>

<AppHead title="Entrenamiento" />

<div class="flex flex-col gap-4 p-4">
    <header class="flex flex-col gap-2">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold capitalize">{dateLabel}</h1>
                <p class="flex items-center gap-2 text-sm text-muted-foreground">
                    {#if isActive}
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-2.5 py-0.5 font-mono text-base font-bold tabular-nums text-primary">
                            {elapsed}
                        </span>
                        en curso · {session.sets.length} series
                    {:else}
                        Terminado · {session.sets.length} series
                    {/if}
                </p>
            </div>
            <Badge variant="secondary" class="text-sm">{totalVolume.toLocaleString('es')} kg</Badge>
        </div>

        {#if routine && routineProgress}
            <div class="rounded-xl border bg-muted/30 px-4 py-3">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-sm font-semibold">{routine.name}</p>
                    <p class="text-sm tabular-nums text-muted-foreground">
                        {routineProgress.done}/{routineProgress.total} completados
                    </p>
                </div>
                <div class="mt-2 h-2 overflow-hidden rounded-full bg-muted" role="progressbar"
                    aria-valuemin="0" aria-valuemax={routineProgress.total} aria-valuenow={routineProgress.done}
                >
                    <div
                        class="h-full rounded-full bg-primary transition-all"
                        style={`width: ${Math.round((routineProgress.done / routineProgress.total) * 100)}%`}
                    ></div>
                </div>
            </div>
        {/if}
    </header>

    {#if blocks.length === 0 && stagedBlocks.length === 0}
        <Card>
            <CardContent class="flex flex-col items-center gap-3 py-10 text-center">
                <p class="text-lg font-medium">Agrega tu primer ejercicio</p>
                <p class="text-sm text-muted-foreground">Busca en el catálogo y registra tus series de reps × peso.</p>
                {#if isActive}
                    <Button onclick={() => (pickerOpen = true)}>Añadir ejercicio</Button>
                {/if}
            </CardContent>
        </Card>
    {/if}

    {#each renderBlocks as block (block.exerciseId)}
        {@const estado = blockState(block.exerciseId, block.sets.length)}
        {@const objetivo = routineTargets.get(block.exerciseId) ?? ''}
        {@const seriesMeta = objetivo ? `${block.sets.length} / ${targetSets(block.exerciseId) ?? '·'} series` : `${block.sets.length} series`}
        <Card class={estado === 'pendiente' ? 'border-dashed' : ''}>
            <CardHeader class="pb-2">
                <CardTitle class="flex min-w-0 items-center justify-between gap-2 text-base">
                    <img
                        src={block.gifUrl ?? block.imageUrl ?? undefined}
                        alt=""
                        loading="lazy"
                        class="size-11 shrink-0 rounded-md bg-muted object-contain"
                    />
                    <button
                        class="min-w-0 flex-1 truncate text-left"
                        title="Ver guía"
                        onclick={() => (detailExerciseId = block.exerciseId)}
                    >
                        {block.name} <span class="text-xs text-muted-foreground">ⓘ</span>
                    </button>
                    {#if estado === 'completado'}
                        <Badge class="shrink-0 gap-1 bg-emerald-600 text-white hover:bg-emerald-600">✓ {seriesMeta}</Badge>
                    {:else if estado === 'pendiente'}
                        <Badge variant="outline" class="shrink-0">Pendiente</Badge>
                    {:else}
                        <Badge variant="outline" class="shrink-0">{seriesMeta}</Badge>
                    {/if}
                </CardTitle>
                <p class="text-xs text-muted-foreground">
                    {block.muscleGroup}{objetivo ? ` · objetivo ${objetivo}` : ''}
                </p>
            </CardHeader>
            <CardContent class="flex flex-col gap-2">
                {#each block.sets as set, index (set.id)}
                    {#if editingSetId === set.id}
                        <div class="flex items-center gap-2">
                            <Label class="w-4 text-sm text-muted-foreground">{index + 1}.</Label>
                            <Input
                                type="number"
                                inputmode="numeric"
                                class="h-9 w-20"
                                bind:value={editDraft.reps}
                                aria-label="Repeticiones"
                            />
                            <span class="text-sm text-muted-foreground">×</span>
                            <Input
                                type="number"
                                inputmode="decimal"
                                step="0.5"
                                class="h-9 w-24"
                                bind:value={editDraft.weight}
                                aria-label="Peso en kg"
                            />
                            <span class="text-sm text-muted-foreground">kg</span>
                            <Button size="sm" class="ml-auto" onclick={() => saveEdit(set.id)}>Guardar</Button>
                        </div>
                    {:else}
                        <div class="group flex items-center gap-2 rounded-md px-2 py-1.5 hover:bg-muted/60">
                            <span class="w-4 text-sm text-muted-foreground">{index + 1}.</span>
                            <button
                                class="flex-1 text-left text-sm font-medium"
                                onclick={() => isActive && startEdit(set)}
                            >
                                {set.reps} reps × {set.weightKg} kg
                            </button>
                            {#if isActive}
                                <Button
                                    size="sm"
                                    variant="ghost"
                                    class="h-7 px-2 text-muted-foreground"
                                    aria-label="Borrar serie"
                                    onclick={() => removeSet(set.id)}
                                >
                                    ✕
                                </Button>
                            {/if}
                        </div>
                    {/if}
                {/each}

                {#if isActive}
                    {@const goal = parseTarget(block.exerciseId)}
                    {@const enSlot = goal !== null && block.sets.length < goal.sets}
                    {@const ghosts = goal !== null ? Math.max(0, goal.sets - block.sets.length - 1) : 0}
                    {#if enSlot}
                        <p class="mt-2 text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                            Serie {block.sets.length + 1} de {goal?.sets}
                            {#if goal?.reps}
                                · objetivo {goal.reps} reps
                            {/if}
                        </p>
                    {/if}
                    {@const values = draft[block.exerciseId] ?? prefill(block.exerciseId)}
                    <SetEntryForm
                        bind:reps={values.reps}
                        bind:weight={values.weight}
                        onsubmit={() => {
                            draft[block.exerciseId] = values;
                            addSet(block.exerciseId);
                        }}
                    />
                    {#if ghosts > 0}
                        {#each Array(ghosts) as _, i (i)}
                            <div class="flex items-center gap-2 rounded-md border border-dashed px-2 py-1.5 text-muted-foreground">
                                <span class="w-4 text-xs">{block.sets.length + 2 + i}.</span>
                                <span class="text-xs">serie pendiente{goal?.reps ? ` · ${goal.reps} reps` : ''}</span>
                            </div>
                        {/each}
                    {/if}
                {/if}
            </CardContent>
        </Card>
    {/each}

    {#if isActive}
        <div class="flex flex-col gap-2">
            <Button variant="outline" onclick={() => (pickerOpen = true)}>Añadir ejercicio</Button>
            <Button size="lg" onclick={finishWorkout}>Terminar entrenamiento</Button>
            <Button variant="ghost" class="text-muted-foreground" onclick={deleteSession}>Borrar sesión</Button>
        </div>
    {:else}
        <Button variant="ghost" class="text-muted-foreground" onclick={deleteSession}>Borrar sesión</Button>
    {/if}
</div>

<ExercisePickerDialog
    bind:open={pickerOpen}
    {exercises}
    onselect={(id) => (stagedIds = [...new Set([...stagedIds, id])])}
/>

<ExerciseDetailDialog bind:exerciseId={detailExerciseId} />
