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
    import AppHead from '@/components/AppHead.svelte';
    import ExerciseDetailDialog from '@/components/ExerciseDetailDialog.svelte';
    import ExercisePickerDialog from '@/components/ExercisePickerDialog.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { destroy as destroyRoutine, update as updateRoutine } from '@/routes/routines';
    import { store as storeItem, update as updateItem, destroy as destroyItem } from '@/routes/routine-items';
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

    const DAYS = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

    const dayBlocks = $derived(
        DAYS.map((label, index) => ({
            day: index + 1,
            label,
            items: routine.items.filter((item) => item.dayOfWeek === index + 1),
        })),
    );

    interface RoutineProp {
        id: number;
        name: string;
        notes: string | null;
        items: RoutineItemRow[];
    }

    let {
        routine,
        exercises = [],
    }: {
        routine: RoutineProp;
        exercises?: import('@/components/ExercisePickerDialog.svelte').ExerciseOption[];
    } = $props();

    let pickerOpen = $state(false);
    let pickerDay = $state(1);
    let detailExerciseId = $state<number | null>(null);
    let editingName = $state(false);
    let nameDraft = $state(routine.name);

    // Objetivo estructurado: "3x8-12" → series=3, reps=8. Auto-guardado al cambiar.
    let targetDraft = $state<Record<number, { sets: string; reps: string }>>({});

    function parseItemTarget(target: string | null): { sets: string; reps: string } {
        const match = /^(\d+)\s*[x×]\s*(\d+)/i.exec((target ?? '').trim());

        return match ? { sets: match[1], reps: match[2] } : { sets: '', reps: '' };
    }

    function saveTarget(itemId: number) {
        const draft = targetDraft[itemId];
        if (!draft) return;
        const hasBoth = draft.sets !== '' && draft.reps !== '';
        const hasNone = draft.sets === '' && draft.reps === '';
        const value = hasBoth ? `${draft.sets}x${draft.reps}` : hasNone ? null : undefined;

        if (value === undefined) return; // incompleto: no guardar aún
        router.patch(updateItem({ item: itemId }).url, { target: value }, { preserveScroll: true });
    }

    function move(itemId: number, direction: 'up' | 'down') {
        router.patch(updateItem({ item: itemId }).url, { direction }, { preserveScroll: true });
    }

    function removeItem(itemId: number) {
        router.delete(destroyItem({ item: itemId }).url, { preserveScroll: true });
    }

    function rename() {
        router.patch(updateRoutine({ routine: routine.id }).url, { name: nameDraft }, {
            preserveScroll: true,
            onSuccess: () => (editingName = false),
        });
    }

    function deleteRoutine() {
        if (confirm(`¿Borrar la rutina "${routine.name}"? Tus entrenamientos pasados no se afectan.`)) {
            router.delete(destroyRoutine({ routine: routine.id }).url);
        }
    }

    function startFromRoutine() {
        router.post(startSession().url, { routine_id: routine.id });
    }
</script>

<AppHead title={routine.name} />

<div class="flex flex-col gap-4 p-4">
    <header class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            {#if editingName}
                <form
                    class="flex items-center gap-2"
                    onsubmit={(event) => {
                        event.preventDefault();
                        rename();
                    }}
                >
                    <Input bind:value={nameDraft} class="h-9" maxlength="100" aria-label="Nombre de la rutina" />
                    <Button size="sm" type="submit" disabled={!nameDraft.trim()}>Guardar</Button>
                </form>
            {:else}
                <button class="text-left" onclick={() => { nameDraft = routine.name; editingName = true; }}>
                    <h1 class="truncate text-2xl font-bold">{routine.name}</h1>
                    <p class="text-xs text-muted-foreground">Toca el nombre para renombrar</p>
                </button>
            {/if}
        </div>
        <Badge variant="secondary" class="shrink-0">{routine.items.length} ejercicios</Badge>
    </header>

    <div class="flex flex-col gap-4">
        {#each dayBlocks as day (day.day)}
            <section class="flex flex-col gap-2">
                <div class="flex items-center justify-between px-1">
                    <h2 class="text-sm font-bold tracking-wide uppercase {day.items.length === 0 ? 'text-muted-foreground/50' : ''}">
                        {day.label}
                        {#if day.items.length > 0}
                            <span class="ml-1 font-normal text-muted-foreground">({day.items.length})</span>
                        {/if}
                    </h2>
                    <button
                        class="text-xs font-medium text-primary underline-offset-4 hover:underline"
                        onclick={() => {
                            pickerDay = day.day;
                            pickerOpen = true;
                        }}
                    >
                        + Agregar
                    </button>
                </div>

                {#each day.items as item, index (item.id)}
            <Card>
                <CardContent class="flex items-center gap-3 py-3">
                    <div class="flex shrink-0 flex-col items-center gap-0.5 text-muted-foreground">
                        <button
                            class="px-1 text-xs disabled:opacity-25"
                            aria-label="Subir"
                            disabled={index === 0}
                            onclick={() => move(item.id, 'up')}
                        >
                            ▲
                        </button>
                        <span class="text-xs font-semibold">{index + 1}</span>
                        <button
                            class="px-1 text-xs disabled:opacity-25"
                            aria-label="Bajar"
                            disabled={index === day.items.length - 1}
                            onclick={() => move(item.id, 'down')}
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
                        onclick={() => removeItem(item.id)}
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
                        value={(targetDraft[item.id] ?? parseItemTarget(item.target)).sets}
                        oninput={(event) => {
                            const current = targetDraft[item.id] ?? parseItemTarget(item.target);
                            targetDraft[item.id] = { ...current, sets: event.currentTarget.value };
                        }}
                        onchange={() => saveTarget(item.id)}
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
                        value={(targetDraft[item.id] ?? parseItemTarget(item.target)).reps}
                        oninput={(event) => {
                            const current = targetDraft[item.id] ?? parseItemTarget(item.target);
                            targetDraft[item.id] = { ...current, reps: event.currentTarget.value };
                        }}
                        onchange={() => saveTarget(item.id)}
                        aria-label="Repeticiones del objetivo"
                    />
                    <span class="text-xs text-muted-foreground">reps</span>
                </div>
            </Card>
                {/each}
            </section>
        {/each}
    </div>

    <div class="flex flex-col gap-2">
        {#if routine.items.length > 0}
            <Button size="lg" onclick={startFromRoutine}>Entrenar esta rutina</Button>
        {/if}
        <Link href="/routines" class="py-1 text-center text-sm text-muted-foreground underline-offset-4 hover:underline">
            ← Volver a rutinas
        </Link>
        <Button variant="ghost" class="text-muted-foreground" onclick={deleteRoutine}>Borrar rutina</Button>
    </div>
</div>

<ExercisePickerDialog
    bind:open={pickerOpen}
    {exercises}
    onselect={(id) =>
        router.post(
            storeItem({ routine: routine.id }).url,
            { exercise_id: id, day_of_week: pickerDay },
            { preserveScroll: true },
        )}
/>

<ExerciseDetailDialog bind:exerciseId={detailExerciseId} />
