<script module lang="ts">
    import { index } from '@/routes/exercises';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Ejercicios',
                href: index(),
            },
        ],
    };
</script>

<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import { toast } from 'svelte-sonner';
    import AppHead from '@/components/AppHead.svelte';
    import ExerciseDetailDialog from '@/components/ExerciseDetailDialog.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent } from '@/components/ui/card';
    import {
        Dialog,
        DialogContent,
        DialogDescription,
        DialogFooter,
        DialogTitle,
    } from '@/components/ui/dialog';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import {
        Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
    } from '@/components/ui/select';
    import { destroy as destroyExercise, store as storeExercise } from '@/routes/exercises';
    import { page } from '@inertiajs/svelte';
    import InputError from '@/components/InputError.svelte';

    interface ExerciseItem {
        id: number;
        name: string;
        nameEn?: string | null;
        isCustom: boolean;
        equipment?: string | null;
        imageUrl?: string | null;
        hasGuide: boolean;
    }

    let { groups }: { groups: Record<string, ExerciseItem[]> } = $props();

    const MUSCLE_GROUPS = [
        'Pecho',
        'Espalda',
        'Hombros',
        'Bíceps',
        'Tríceps',
        'Piernas',
        'Glúteos',
        'Core',
        'Cardio',
        'Antebrazo',
        'Cuello',
        'Otro',
    ];

    let search = $state('');
    let expandedGroups = $state<string[]>([]);
    let detailExerciseId = $state<number | null>(null);

    const MAX_SEARCH_RESULTS = 80;

    const searchResults = $derived.by(() => {
        const term = search.trim().toLowerCase();
        if (!term) return null;
        const matches = Object.values(groups)
            .flat()
            .filter(
                (e) =>
                    e.name.toLowerCase().includes(term) ||
                    (e.nameEn ?? '').toLowerCase().includes(term),
            );
        return { items: matches.slice(0, MAX_SEARCH_RESULTS), total: matches.length };
    });

    function toggleGroup(group: string) {
        expandedGroups = expandedGroups.includes(group)
            ? expandedGroups.filter((g) => g !== group)
            : [...expandedGroups, group];
    }

    // --- crear ejercicio propio ---
    let createOpen = $state(false);
    let name = $state('');
    let muscleGroup = $state('Pecho');

    function submit() {
        router.post(
            storeExercise().url,
            { name, muscle_group: muscleGroup },
            {
                onSuccess: () => {
                    createOpen = false;
                    name = '';
                    muscleGroup = 'Pecho';
                    toast.success('Ejercicio creado');
                },
            },
        );
    }

    function remove(exercise: ExerciseItem) {
        if (confirm(`¿Borrar "${exercise.name}"? Los entrenamientos pasados no se afectan.`)) {
            router.delete(destroyExercise({ exercise: exercise.id }).url, { preserveScroll: true });
        }
    }
</script>

<AppHead title="Ejercicios" />

<div class="flex flex-col gap-4 p-4">
    <header class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">Ejercicios</h1>
            <p class="text-sm text-muted-foreground">
                {Object.values(groups).flat().length.toLocaleString('es')} ejercicios con guía visual.
            </p>
        </div>
        <Button variant="outline" onclick={() => (createOpen = true)}>Crear</Button>
    </header>

    <Input
        type="search"
        placeholder="Buscar ejercicio… (ej. banca, curl, sentadilla)"
        bind:value={search}
        aria-label="Buscar ejercicio"
    />

    {#if searchResults}
        <Card>
            <CardContent class="flex flex-col divide-y p-1">
                {#each searchResults.items as exercise (exercise.id)}
                    <div class="flex items-center gap-3 py-2 pl-2 pr-1">
                        <button
                            class="flex min-w-0 flex-1 items-center gap-3 text-left"
                            onclick={() => (detailExerciseId = exercise.id)}
                        >
                            {#if exercise.imageUrl}
                                <img
                                    src={exercise.imageUrl}
                                    alt=""
                                    loading="lazy"
                                    class="size-10 shrink-0 rounded-md bg-muted object-contain"
                                />
                            {:else}
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-md bg-muted text-xs text-muted-foreground">
                                    {exercise.name.slice(0, 2)}
                                </div>
                            {/if}
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-medium">{exercise.name}</span>
                                {#if exercise.equipment}
                                    <span class="block truncate text-xs text-muted-foreground">
                                        {exercise.equipment}{exercise.hasGuide ? ' · guía' : ''}
                                    </span>
                                {/if}
                            </span>
                        </button>
                        {#if exercise.isCustom}
                            <Badge variant="secondary" class="shrink-0 text-xs">tuyo</Badge>
                            <Button
                                size="sm"
                                variant="ghost"
                                class="h-7 shrink-0 px-2 text-muted-foreground"
                                aria-label="Borrar ejercicio"
                                onclick={() => remove(exercise)}
                            >
                                ✕
                            </Button>
                        {/if}
                    </div>
                {:else}
                    <p class="py-6 text-center text-sm text-muted-foreground">Sin resultados para “{search}”.</p>
                {/each}
                {#if searchResults.total > searchResults.items.length}
                    <p class="py-3 text-center text-xs text-muted-foreground">
                        Mostrando {searchResults.items.length} de {searchResults.total}. Afina la búsqueda.
                    </p>
                {/if}
            </CardContent>
        </Card>
    {:else}
        {#each Object.entries(groups) as [group, items] (group)}
            <Card>
                <button
                    class="flex w-full items-center justify-between px-4 py-3 text-left"
                    onclick={() => toggleGroup(group)}
                    aria-expanded={expandedGroups.includes(group)}
                >
                    <span class="flex items-center gap-2 font-semibold">
                        {group}
                        <Badge variant="outline">{items.length}</Badge>
                    </span>
                    <span class="text-muted-foreground">{expandedGroups.includes(group) ? '▲' : '▼'}</span>
                </button>
                {#if expandedGroups.includes(group)}
                    <div class="flex flex-col divide-y border-t">
                        {#each items as exercise (exercise.id)}
                            <div class="flex items-center gap-3 py-2 pl-4 pr-2">
                                <button
                                    class="flex min-w-0 flex-1 items-center gap-3 text-left"
                                    onclick={() => (detailExerciseId = exercise.id)}
                                >
                                    {#if exercise.imageUrl}
                                        <img
                                            src={exercise.imageUrl}
                                            alt=""
                                            loading="lazy"
                                            class="size-9 shrink-0 rounded-md bg-muted object-contain"
                                        />
                                    {:else}
                                        <div class="flex size-9 shrink-0 items-center justify-center rounded-md bg-muted text-xs text-muted-foreground">
                                            {exercise.name.slice(0, 2)}
                                        </div>
                                    {/if}
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm">{exercise.name}</span>
                                        {#if exercise.equipment}
                                            <span class="block truncate text-xs text-muted-foreground">
                                                {exercise.equipment}
                                            </span>
                                        {/if}
                                    </span>
                                </button>
                                {#if exercise.isCustom}
                                    <Badge variant="secondary" class="shrink-0 text-xs">tuyo</Badge>
                                    <Button
                                        size="sm"
                                        variant="ghost"
                                        class="h-7 shrink-0 px-2 text-muted-foreground"
                                        aria-label="Borrar ejercicio"
                                        onclick={() => remove(exercise)}
                                    >
                                        ✕
                                    </Button>
                                {/if}
                            </div>
                        {/each}
                    </div>
                {/if}
            </Card>
        {/each}
    {/if}
</div>

<ExerciseDetailDialog bind:exerciseId={detailExerciseId} />

<Dialog bind:open={createOpen}>    <DialogContent>
        <div class="flex flex-col gap-1.5">
            <DialogTitle>Nuevo ejercicio</DialogTitle>
            <DialogDescription>Solo visible para ti.</DialogDescription>
        </div>
        <form class="flex flex-col gap-4" onsubmit={(event) => { event.preventDefault(); submit(); }}>
            <div class="grid gap-2">
                <Label for="exercise-name">Nombre</Label>
                <Input id="exercise-name" bind:value={name} placeholder="Ej. Press landmine" required maxlength={255} />
                <InputError message={page.props.errors.name as string | undefined} />
            </div>
            <div class="grid gap-2">
                <Label>Grupo muscular</Label>
                <Select type="single" bind:value={muscleGroup}>
                    <SelectTrigger class="w-full" aria-label="Grupo muscular">{muscleGroup}</SelectTrigger>
                    <SelectContent>
                        {#each MUSCLE_GROUPS as group (group)}
                            <SelectItem value={group}>{group}</SelectItem>
                        {/each}
                    </SelectContent>
                </Select>
            </div>
            <DialogFooter>
                <Button type="submit">Guardar</Button>
            </DialogFooter>
        </form>
    </DialogContent>
</Dialog>

