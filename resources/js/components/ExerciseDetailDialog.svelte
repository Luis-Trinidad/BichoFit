<script lang="ts">
    import { Dialog, DialogContent, DialogDescription, DialogTitle } from '@/components/ui/dialog';
    import { Badge } from '@/components/ui/badge';
    import { Spinner } from '@/components/ui/spinner';
    import { show } from '@/routes/exercises';

    interface ExerciseDetail {
        id: number;
        name: string;
        muscleGroup: string;
        equipment?: string | null;
        target?: string | null;
        secondaryMuscles?: string[] | null;
        description?: string | null;
        steps?: string[] | null;
        imageUrl?: string | null;
        gifUrl?: string | null;
        attribution?: string | null;
    }

    let {
        exerciseId = $bindable(null),
    }: {
        exerciseId?: number | null;
    } = $props();

    const open = $derived(exerciseId !== null);

    let detail = $state<ExerciseDetail | null>(null);
    let loading = $state(false);

    $effect(() => {
        if (exerciseId === null) {
            detail = null;
            return;
        }
        loading = true;
        fetch(show({ exercise: exerciseId }).url, {
            headers: { Accept: 'application/json' },
        })
            .then((r) => r.json())
            .then((data: ExerciseDetail) => (detail = data))
            .catch(() => (detail = null))
            .finally(() => (loading = false));
    });
</script>

<Dialog
    open={open}
    onOpenChange={(value: boolean) => {
        if (!value) exerciseId = null;
    }}
>
    <DialogContent class="max-h-[85vh] overflow-y-auto">
        {#if loading || !detail}
            <div class="flex flex-col items-center gap-3 py-10 text-muted-foreground">
                <Spinner class="size-6" />
                <p class="text-sm">Cargando guía…</p>
            </div>
        {:else}
            <div class="flex flex-col gap-3">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <DialogTitle class="text-left">{detail.name}</DialogTitle>
                        <DialogDescription class="text-left">
                            {detail.muscleGroup}{detail.equipment ? ` · ${detail.equipment}` : ''}
                        </DialogDescription>
                    </div>
                </div>

                {#if detail.gifUrl ?? detail.imageUrl}
                    <div class="flex justify-center rounded-lg border bg-muted/40 p-2">
                        {#if detail.gifUrl}
                            <img
                                src={detail.gifUrl}
                                alt={`Guía animada de ${detail.name}`}
                                class="size-48 rounded-md object-contain"
                            />
                        {:else}
                            <img src={detail.imageUrl} alt={detail.name} class="size-48 rounded-md object-contain" />
                        {/if}
                    </div>
                {/if}

                {#if detail.target || detail.secondaryMuscles?.length}
                    <div class="flex flex-wrap gap-1.5">
                        {#if detail.target}
                            <Badge variant="secondary">{detail.target}</Badge>
                        {/if}
                        {#each detail.secondaryMuscles ?? [] as muscle (muscle)}
                            <Badge variant="outline">{muscle}</Badge>
                        {/each}
                    </div>
                {/if}

                {#if detail.steps?.length}
                    <div>
                        <h3 class="mb-2 text-sm font-semibold">Cómo se hace</h3>
                        <ol class="flex list-decimal flex-col gap-2 pl-5 text-sm leading-relaxed">
                            {#each detail.steps as step, index (index)}
                                <li>{step}</li>
                            {/each}
                        </ol>
                    </div>
                {:else if detail.description}
                    <p class="text-sm leading-relaxed">{detail.description}</p>
                {/if}

                {#if detail.attribution}
                    <p class="text-[10px] text-muted-foreground">{detail.attribution}</p>
                {/if}
            </div>
        {/if}
    </DialogContent>
</Dialog>
