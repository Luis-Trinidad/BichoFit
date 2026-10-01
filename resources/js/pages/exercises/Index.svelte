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
    import { page, router } from '@inertiajs/svelte';
    import { toast } from 'svelte-sonner';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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

    interface ExerciseItem {
        id: number;
        name: string;
        isCustom: boolean;
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
        'Otro',
    ];

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
            <p class="text-sm text-muted-foreground">Catálogo global y tus ejercicios personalizados.</p>
        </div>
        <Button onclick={() => (createOpen = true)}>Crear</Button>
    </header>

    {#each Object.entries(groups) as [group, items] (group)}
        <Card>
            <CardHeader class="pb-2">
                <CardTitle class="flex items-center gap-2 text-base">
                    {group}
                    <Badge variant="outline">{items.length}</Badge>
                </CardTitle>
            </CardHeader>
            <CardContent class="flex flex-col divide-y">
                {#each items as exercise (exercise.id)}
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm">{exercise.name}</span>
                        {#if exercise.isCustom}
                            <div class="flex items-center gap-2">
                                <Badge variant="secondary" class="text-xs">tuyo</Badge>
                                <Button
                                    size="sm"
                                    variant="ghost"
                                    class="h-7 px-2 text-muted-foreground"
                                    aria-label="Borrar ejercicio"
                                    onclick={() => remove(exercise)}
                                >
                                    ✕
                                </Button>
                            </div>
                        {/if}
                    </div>
                {/each}
            </CardContent>
        </Card>
    {/each}
</div>

<Dialog bind:open={createOpen}>
    <DialogContent>
        <div class="flex flex-col gap-1.5">
            <DialogTitle>Nuevo ejercicio</DialogTitle>
            <DialogDescription>Solo visible para ti.</DialogDescription>
        </div>
        <form class="flex flex-col gap-4" onsubmit={(event) => { event.preventDefault(); submit(); }}>
            <div class="grid gap-2">
                <Label for="exercise-name">Nombre</Label>
                <Input id="exercise-name" bind:value={name} placeholder="Ej. Press landmine" required maxlength="255" />
                <InputError message={page.props.errors.name} />
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
