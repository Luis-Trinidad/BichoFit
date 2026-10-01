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

    let {
        routine,
        exercises = [],
    }: {
        routine: RoutineProp;
        exercises?: import('@/components/ExercisePickerDialog.svelte').ExerciseOption[];
    } = $props();

    let pickerOpen = $state(false);
    let detailExerciseId = $state<number | null>(null);
    let editingName = $state(false);
    let nameDraft = $state(routine.name);

    // borradores del objetivo por línea (ej. "3x8-12")
    let targetDraft = $state<Record<number, string>>({});

    function addTarget(itemId: number) {
        router.patch(updateItem({ item: itemId }).url, { target: targetDraft[itemId] ?? null }, { preserveScroll: true });
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

    <div class="flex flex-col gap-2">
        {#each routine.items as item, index (item.id)}
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
                            disabled={index === routine.items.length - 1}
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
                    <Input
                        placeholder="objetivo, ej. 3x8-12"
                        class="h-7 flex-1 text-xs"
                        value={targetDraft[item.id] ?? item.target ?? ''}
                        oninput={(event) => (targetDraft[item.id] = event.currentTarget.value)}
                        maxlength="50"
                        aria-label="Objetivo de series y repeticiones"
                    />
                    <Button
                        size="sm"
                        variant="ghost"
                        class="h-7 px-2 text-xs"
                        onclick={() => addTarget(item.id)}
                        disabled={(targetDraft[item.id] ?? item.target ?? null) === item.target}
                    >
                        Listo
                    </Button>
                </div>
            </Card>
        {:else}
            <Card>
                <CardContent class="flex flex-col items-center gap-3 py-8 text-center">
                    <p class="text-sm text-muted-foreground">
                        Agrega ejercicios en el orden que quieras entrenarlos.
                    </p>
                </CardContent>
            </Card>
        {/each}
    </div>

    <div class="flex flex-col gap-2">
        <Button variant="outline" onclick={() => (pickerOpen = true)}>Agregar ejercicio</Button>
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
    onselect={(id) => router.post(storeItem({ routine: routine.id }).url, { exercise_id: id }, { preserveScroll: true })}
/>

<ExerciseDetailDialog bind:exerciseId={detailExerciseId} />
