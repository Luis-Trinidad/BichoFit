<script module lang="ts">
    import { index } from '@/routes/routines';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Rutinas',
                href: index(),
            },
        ],
    };
</script>

<script lang="ts">
    import { Link, router } from '@inertiajs/svelte';
    import { toast } from 'svelte-sonner';
    import AppHead from '@/components/AppHead.svelte';
    import ExerciseDetailDialog from '@/components/ExerciseDetailDialog.svelte';
    import ExercisePickerDialog from '@/components/ExercisePickerDialog.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { update as updateRoutine, destroy as destroyRoutine } from '@/routes/routines';
    import { store as startSession } from '@/routes/workout-sessions';

    interface RoutineItemRow {
        id: number;
        exerciseId: number;
        dayOfWeek: number;
        name: string;
        muscleGroup: string;
        equipment?: string | null;
        imageUrl?: string | null;
        target: string | null;
    }

    interface RoutineProp {
        id: number;
        name: string;
        notes: string | null;
        items: RoutineItemRow[];
    }

    const DAYS = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

    let {
        routine,
        exercises = [],
    }: {
        routine: RoutineProp;
        exercises?: import('@/components/ExercisePickerDialog.svelte').ExerciseOption[];
    } = $props();

    // --- borrador local: nada se guarda hasta pulsar Guardar cambios ---
    interface DraftItem {
        key: number; // id real, o temporal negativo para los nuevos
        id: number | null;
        exerciseId: number;
        dayOfWeek: number;
        name: string;
        muscleGroup: string;
        equipment?: string | null;
        imageUrl?: string | null;
        target: string | null;
    }

    function toDraft(routineProp: RoutineProp) {
        return {
            name: routineProp.name,
            items: routineProp.items.map((item) => ({ ...item, key: item.id, id: item.id })),
        };
    }

    let draft = $state(toDraft(routine));
    let savedSignature = $state(JSON.stringify(toDraft(routine)));

    const dirty = $derived(JSON.stringify(draft) !== savedSignature);

    let nextTempKey = -1;
    let editingName = $state(false);
    let pickerOpen = $state(false);
    let pickerDay = $state(1);
    let collapsedDays = $state<number[]>([]);
    let detailExerciseId = $state<number | null>(null);

    function toggleDay(day: number) {
        collapsedDays = collapsedDays.includes(day)
            ? collapsedDays.filter((d) => d !== day)
            : [...collapsedDays, day];
    }

    const dayBlocks = $derived(
        DAYS.map((label, index) => ({
            day: index + 1,
            label,
            items: draft.items.filter((item) => item.dayOfWeek === index + 1),
        })),
    );

    /** Objetivo "4x10" → {sets:4, reps:10}. */
    function parseItemTarget(target: string | null): { sets: string; reps: string } {
        const match = /^(\d+)\s*[x×]\s*(\d+)/i.exec((target ?? '').trim());

        return match ? { sets: match[1], reps: match[2] } : { sets: '', reps: '' };
    }

    function setTargetPart(key: number, part: 'sets' | 'reps', value: string) {
        const item = draft.items.find((entry) => entry.key === key);
        if (!item) return;
        const current = parseItemTarget(item.target);
        const next = { ...current, [part]: value };
        item.target = next.sets !== '' && next.reps !== '' ? `${next.sets}x${next.reps}` : null;
    }

    function dayOfWeekOf(key: number): number {
        return draft.items.find((item) => item.key === key)?.dayOfWeek ?? 1;
    }

    /** Mover dentro de su día: el orden del array dentro del día es el orden real. */
    function move(key: number, direction: 'up' | 'down') {
        const sameDay = draft.items.filter((item) => item.dayOfWeek === dayOfWeekOf(key));
        const pos = sameDay.findIndex((item) => item.key === key);
        const swapWith = direction === 'up' ? sameDay[pos - 1] : sameDay[pos + 1];
        if (!swapWith) return;

        const indexA = draft.items.findIndex((item) => item.key === key);
        const indexB = draft.items.findIndex((item) => item.key === swapWith.key);
        const temp = draft.items[indexA];
        draft.items[indexA] = draft.items[indexB];
        draft.items[indexB] = temp;
    }

    function removeItem(key: number) {
        draft.items = draft.items.filter((item) => item.key !== key);
    }

    function addExercise(exerciseId: number) {
        const option = exercises.find((e) => e.id === exerciseId);
        if (!option) return;
        draft.items.push({
            key: nextTempKey--,
            id: null as unknown as number,
            exerciseId,
            dayOfWeek: pickerDay ?? 1,
            name: option.name,
            muscleGroup: option.muscle_group,
            imageUrl: option.imageUrl ?? null,
            target: null,
        });
        collapsedDays = collapsedDays.filter((d) => d !== pickerDay);
    }

    function discard() {
        const fresh = toDraft(routine);
        draft = fresh;
        savedSignature = JSON.stringify(fresh);
        editingName = false;
    }

    function save() {
        // posiciones 1..n dentro de cada día según el orden del borrador
        const items = draft.items.map((item) => {
            const sameDay = draft.items.filter((entry) => entry.dayOfWeek === item.dayOfWeek);

            return {
                id: item.id,
                exercise_id: item.exerciseId,
                day_of_week: item.dayOfWeek,
                position: sameDay.findIndex((entry) => entry.key === item.key) + 1,
                target: item.target,
            };
        });

        router.patch(
            updateRoutine({ routine: routine.id }).url,
            { name: draft.name, items },
            {
                preserveScroll: true,
                onSuccess: () => {
                    // la prop ya viene fresca del servidor: resincronizar el borrador
                    draft = toDraft(routine);
                    savedSignature = JSON.stringify(draft);
                    toast.success('Rutina guardada');
                },
                onError: () => toast.error('No se pudo guardar la rutina'),
            },
        );
    }

    function deleteRoutine() {
        if (confirm(`¿Borrar la rutina "${routine.name}"? Tus entrenamientos pasados no se afectan.`)) {
            router.delete(destroyRoutine({ routine: routine.id }).url);
        }
    }

    function startFromRoutine() {
        if (dirty && !confirm('Tienes cambios sin guardar. ¿Entrenar la versión guardada?')) return;
        router.post(startSession().url, { routine_id: routine.id });
    }
</script>

<AppHead title={routine.name} />

<div class="flex flex-col gap-4 p-4 pb-44">
    <header class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            {#if editingName}
                <form
                    class="flex items-center gap-2"
                    onsubmit={(event) => {
                        event.preventDefault();
                        editingName = false;
                    }}
                >
                    <Input bind:value={draft.name} class="h-9" aria-label="Nombre de la rutina" />
                    <Button size="sm" type="submit" disabled={!draft.name.trim()}>OK</Button>
                </form>
            {:else}
                <button class="text-left" onclick={() => (editingName = true)}>
                    <h1 class="truncate text-2xl font-bold">{draft.name}</h1>
                    <p class="text-xs text-muted-foreground">Toca el nombre para renombrar</p>
                </button>
            {/if}
        </div>
        <Badge variant="secondary" class="shrink-0">{draft.items.length} ejercicios</Badge>
    </header>

    <div class="flex flex-col gap-4">
        {#each dayBlocks as day (day.day)}
            <section class="flex flex-col gap-2">
                <div class="flex items-center justify-between px-1">
                    <button
                        class="flex min-w-0 flex-1 items-center gap-2 py-1 text-left"
                        onclick={() => toggleDay(day.day)}
                        aria-expanded={!collapsedDays.includes(day.day)}
                    >
                        <h2 class="text-sm font-bold tracking-wide uppercase {day.items.length === 0 ? 'text-muted-foreground/50' : ''}">
                            {day.label}
                            {#if day.items.length > 0}
                                <span class="ml-1 font-normal text-muted-foreground">({day.items.length})</span>
                            {/if}
                        </h2>
                        <span class="text-xs text-muted-foreground">
                            {collapsedDays.includes(day.day) ? '▼' : '▲'}
                        </span>
                    </button>
                    <button
                        class="shrink-0 text-xs font-medium text-primary underline-offset-4 hover:underline"
                        onclick={() => {
                            pickerDay = day.day;
                            pickerOpen = true;
                        }}
                    >
                        + Agregar
                    </button>
                </div>

                {#if !collapsedDays.includes(day.day)}
                    {#each day.items as item, index (item.key)}
                        <Card>
                            <CardContent class="flex items-center gap-3 py-3">
                                <div class="flex shrink-0 flex-col items-center gap-0.5 text-muted-foreground">
                                    <button
                                        class="px-1 text-xs disabled:opacity-25"
                                        aria-label="Subir"
                                        disabled={index === 0}
                                        onclick={() => move(item.key, 'up')}
                                    >
                                        ▲
                                    </button>
                                    <span class="text-xs font-semibold">{index + 1}</span>
                                    <button
                                        class="px-1 text-xs disabled:opacity-25"
                                        aria-label="Bajar"
                                        disabled={index === day.items.length - 1}
                                        onclick={() => move(item.key, 'down')}
                                    >
                                        ▼
                                    </button>
                                </div>

                                <button
                                    class="flex min-w-0 flex-1 items-center gap-3 text-left"
                                    onclick={() => (detailExerciseId = item.exerciseId)}
                                >
                                    {#if item.imageUrl}
                                        <img
                                            src={item.imageUrl}
                                            alt=""
                                            loading="lazy"
                                            class="size-10 shrink-0 rounded-md bg-muted object-contain"
                                        />
                                    {:else}
                                        <div class="flex size-10 shrink-0 items-center justify-center rounded-md bg-muted text-xs text-muted-foreground">
                                            {item.name.slice(0, 2)}
                                        </div>
                                    {/if}
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-medium">{item.name}</span>
                                        <span class="block truncate text-xs text-muted-foreground">
                                            {item.muscleGroup}{item.equipment ? ` · ${item.equipment}` : ''}
                                        </span>
                                    </span>
                                </button>

                                <Button
                                    size="sm"
                                    variant="ghost"
                                    class="h-7 shrink-0 px-2 text-muted-foreground"
                                    aria-label="Quitar de la rutina"
                                    onclick={() => removeItem(item.key)}
                                >
                                    ✕
                                </Button>
                            </CardContent>
                            <div class="flex items-center gap-2 border-t px-4 py-2">
                                <span class="text-xs font-medium text-muted-foreground">Objetivo</span>
                                <Input
                                    type="number"
                                    inputmode="numeric"
                                    min="1"
                                    max="20"
                                    placeholder="4"
                                    class="h-8 w-14 text-center text-sm tabular-nums"
                                    value={parseItemTarget(item.target).sets}
                                    oninput={(event) => setTargetPart(item.key, 'sets', event.currentTarget.value)}
                                    aria-label="Series del objetivo"
                                />
                                <span class="text-sm text-muted-foreground">×</span>
                                <Input
                                    type="number"
                                    inputmode="numeric"
                                    min="1"
                                    max="100"
                                    placeholder="10"
                                    class="h-8 w-14 text-center text-sm tabular-nums"
                                    value={parseItemTarget(item.target).reps}
                                    oninput={(event) => setTargetPart(item.key, 'reps', event.currentTarget.value)}
                                    aria-label="Repeticiones del objetivo"
                                />
                                <span class="text-xs text-muted-foreground">reps</span>
                            </div>
                        </Card>
                    {/each}
                {/if}
            </section>
        {/each}
    </div>

    <div class="flex flex-col gap-2">
        {#if draft.items.length > 0}
            <Button size="lg" variant="outline" onclick={startFromRoutine}>
                Entrenar esta rutina
                {#if dirty}
                    <span class="text-xs text-muted-foreground">(versión guardada)</span>
                {/if}
            </Button>
        {/if}
        <Link href="/routines" class="py-1 text-center text-sm text-muted-foreground underline-offset-4 hover:underline">
            ← Volver a rutinas
        </Link>
        <Button variant="ghost" class="text-muted-foreground" onclick={deleteRoutine}>Borrar rutina</Button>
    </div>
</div>

{#if dirty}
    <div class="fixed inset-x-0 z-50 px-4 bottom-[calc(6.9rem+env(safe-area-inset-bottom))]">
        <div class="mx-auto flex max-w-lg items-center justify-center gap-3">
            <Button
                variant="outline"
                class="h-12 flex-1 rounded-full bg-background shadow-lg shadow-black/10"
                onclick={discard}
            >
                Descartar
            </Button>
            <Button
                class="h-12 flex-[2] rounded-full shadow-lg shadow-primary/25"
                onclick={save}
            >
                Guardar cambios
            </Button>
        </div>
    </div>
{/if}

<ExercisePickerDialog
    bind:open={pickerOpen}
    {exercises}
    onselect={(id) => addExercise(id)}
/>

<ExerciseDetailDialog bind:exerciseId={detailExerciseId} />
