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
    import { store } from '@/routes/routines';
    import { show } from '@/routes/routines';

    interface RoutineDaySummary {
        dayName: string;
        exercises: string[];
    }

    interface RoutineRow {
        id: number;
        name: string;
        notes: string | null;
        days: RoutineDaySummary[];
    }

    let { routines }: { routines: RoutineRow[] } = $props();

    let createOpen = $state(false);
    let name = $state('');

    function create() {
        router.post(
            store().url,
            { name },
            {
                onSuccess: () => {
                    createOpen = false;
                    name = '';
                },
            },
        );
    }
</script>

<AppHead title="Rutinas" />

<div class="flex flex-col gap-4 p-4">
    <header class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">Rutinas</h1>
            <p class="text-sm text-muted-foreground">Arma tus plantillas y lánzalas en un tap.</p>
        </div>
        <Button onclick={() => (createOpen = true)}>Nueva</Button>
    </header>

    <div class="flex flex-col gap-3">
        {#each routines as routine (routine.id)}
            <Link href={show({ routine: routine.id }).url} class="block">
                <Card class="transition-colors hover:bg-muted/40">
                    <CardContent class="flex flex-col gap-1 py-4">
                        <p class="font-semibold">{routine.name}</p>
                        {#if routine.days.length}
                            <p class="line-clamp-2 text-sm text-muted-foreground">
                                {routine.days
                                    .map((day) => `${day.dayName}: ${day.exercises.join(', ')}`)
                                    .join(' · ')}
                            </p>
                        {:else}
                            <p class="text-sm text-muted-foreground">Sin ejercicios todavía</p>
                        {/if}
                    </CardContent>
                </Card>
            </Link>
        {:else}
            <Card>
                <CardContent class="py-6 text-center text-sm text-muted-foreground">
                    Crea tu primera rutina: nómbrala, agrégale ejercicios en orden y listón.
                </CardContent>
            </Card>
        {/each}
    </div>
</div>

<Dialog bind:open={createOpen}>
    <DialogContent>
        <div class="flex flex-col gap-1.5">
            <DialogTitle>Nueva rutina</DialogTitle>
            <DialogDescription>Ponle un nombre: Push, Pierna, Full body…</DialogDescription>
        </div>
        <form class="flex flex-col gap-4" onsubmit={(event) => { event.preventDefault(); create(); }}>
            <div class="grid gap-2">
                <Label for="routine-name">Nombre</Label>
                <Input id="routine-name" bind:value={name} placeholder="Ej. Push A" required maxlength="100" />
            </div>
            <DialogFooter>
                <Button type="submit" disabled={!name.trim()}>Crear y agregar ejercicios</Button>
            </DialogFooter>
        </form>
    </DialogContent>
</Dialog>
