<script lang="ts">
    import { Minus, Plus } from '@lucide/svelte';
    import { Button } from '@/components/ui/button';
    import { cn } from '@/lib/utils';

    let {
        reps = $bindable(''),
        weight = $bindable(''),
        onsubmit,
    }: {
        reps?: string;
        weight?: string;
        onsubmit: () => void;
    } = $props();

    const REPS_STEP = 1;
    const WEIGHT_STEP = 2.5;

    function step(field: 'reps' | 'weight', delta: number) {
        const raw = field === 'reps' ? reps : weight;
        const base = Number.isNaN(Number(raw)) || raw === '' ? 0 : Number(raw);
        let next = field === 'reps'
            ? Math.max(1, Math.round(base + delta * REPS_STEP))
            : Math.max(0, Math.round((base + delta * WEIGHT_STEP) * 100) / 100);

        if (field === 'reps') {
            reps = String(next);
        } else {
            weight = String(next);
        }
    }

    const repsValue = $derived(Number(reps));
    const weightValue = $derived(Number(weight));
    const ready = $derived(reps !== '' && weight !== '' && !Number.isNaN(repsValue) && !Number.isNaN(weightValue));
</script>

<form
    class="mt-2 flex flex-col gap-2"
    onsubmit={(event) => {
        event.preventDefault();
        onsubmit();
    }}
>
    <div class="grid grid-cols-2 gap-2">
        <div class="flex flex-col items-center gap-1">
            <span class="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">Reps</span>
            <div class="flex w-full items-center justify-center gap-1">
                <Button
                    type="button"
                    variant="outline"
                    size="icon"
                    class="size-10 shrink-0 rounded-full"
                    aria-label="Quitar repetición"
                    onclick={() => step('reps', -1)}
                >
                    <Minus class="size-4" />
                </Button>
                <input
                    type="number"
                    inputmode="numeric"
                    min="1"
                    max="255"
                    class="h-10 w-16 rounded-md border bg-transparent text-center text-lg font-bold tabular-nums outline-none focus-visible:ring-[3px]"
                    bind:value={reps}
                    aria-label="Repeticiones"
                    required
                />
                <Button
                    type="button"
                    variant="outline"
                    size="icon"
                    class="size-10 shrink-0 rounded-full"
                    aria-label="Agregar repetición"
                    onclick={() => step('reps', 1)}
                >
                    <Plus class="size-4" />
                </Button>
            </div>
        </div>

        <div class="flex flex-col items-center gap-1">
            <span class="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">Peso (kg)</span>
            <div class="flex w-full items-center justify-center gap-1">
                <Button
                    type="button"
                    variant="outline"
                    size="icon"
                    class="size-10 shrink-0 rounded-full"
                    aria-label="Quitar 2.5 kg"
                    onclick={() => step('weight', -1)}
                >
                    <Minus class="size-4" />
                </Button>
                <input
                    type="number"
                    inputmode="decimal"
                    step="0.5"
                    min="0"
                    max="2000"
                    class="h-10 w-20 rounded-md border bg-transparent text-center text-lg font-bold tabular-nums outline-none focus-visible:ring-[3px]"
                    bind:value={weight}
                    aria-label="Peso en kg"
                    required
                />
                <Button
                    type="button"
                    variant="outline"
                    size="icon"
                    class="size-10 shrink-0 rounded-full"
                    aria-label="Agregar 2.5 kg"
                    onclick={() => step('weight', 1)}
                >
                    <Plus class="size-4" />
                </Button>
            </div>
        </div>
    </div>

    <Button
        type="submit"
        size="lg"
        class={cn('h-12 w-full text-base font-semibold tabular-nums active:scale-[0.98]')}
    >
        {#if ready}
            ＋ Serie · {repsValue} × {weightValue.toLocaleString('es')} kg
        {:else}
            ＋ Registrar serie
        {/if}
    </Button>
</form>
