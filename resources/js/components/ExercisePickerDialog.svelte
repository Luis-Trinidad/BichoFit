<script lang="ts">
    import { Dialog, DialogContent, DialogDescription, DialogTitle } from '@/components/ui/dialog';
    import { Badge } from '@/components/ui/badge';
    import { Input } from '@/components/ui/input';
    import { Spinner } from '@/components/ui/spinner';

    export interface ExerciseOption {
        id: number;
        name: string;
        nameEn?: string | null;
        muscle_group: string;
        imageUrl?: string | null;
        gifUrl?: string | null;
    }

    let {
        open = $bindable(false),
        exercises = [],
        onselect,
        title = 'Añadir ejercicio',
    }: {
        open?: boolean;
        exercises?: ExerciseOption[];
        onselect?: (id: number) => void;
        title?: string;
    } = $props();

    let search = $state('');

    const MAX_RESULTS = 60;

    const results = $derived.by(() => {
        const term = search.trim().toLowerCase();
        const filtered = term
            ? exercises.filter(
                  (e) =>
                      e.name.toLowerCase().includes(term) ||
                      (e.nameEn ?? '').toLowerCase().includes(term),
              )
            : exercises;
        return {
            items: filtered.slice(0, MAX_RESULTS),
            total: filtered.length,
        };
    });
</script>

<Dialog bind:open>
    <DialogContent class="max-h-[85vh] overflow-hidden p-0">
        <div class="flex flex-col gap-3 border-b p-4 pb-3">
            <DialogTitle>{title}</DialogTitle>
            <DialogDescription>Busca por nombre ({results.total} disponibles).</DialogDescription>
            <Input
                type="search"
                placeholder="Buscar ejercicio…"
                bind:value={search}
                aria-label="Buscar ejercicio"
            />
        </div>
        <div class="max-h-[55vh] overflow-y-auto p-2">
            {#if exercises.length === 0}
                <div class="flex flex-col items-center gap-2 py-8 text-muted-foreground">
                    <Spinner class="size-5" />
                    <p class="text-sm">Cargando catálogo…</p>
                </div>
            {:else}
                <div class="flex flex-col">
                    {#each results.items as option (option.id)}
                        <button
                            class="flex items-center gap-3 rounded-lg px-2 py-2 text-left hover:bg-muted/60"
                            onclick={() => {
                                onselect?.(option.id);
                                open = false;
                                search = '';
                            }}
                        >
                            {#if option.imageUrl}
                                <img
                                    src={option.imageUrl}
                                    alt=""
                                    loading="lazy"
                                    class="size-10 shrink-0 rounded-md bg-muted object-contain"
                                />
                            {:else}
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-md bg-muted text-xs text-muted-foreground">
                                    {option.name.slice(0, 2)}
                                </div>
                            {/if}
                            <span class="flex-1 truncate text-sm font-medium">{option.name}</span>
                            <Badge variant="outline" class="shrink-0 text-xs">{option.muscle_group}</Badge>
                        </button>
                    {/each}
                    {#if results.total > results.items.length}
                        <p class="py-3 text-center text-xs text-muted-foreground">
                            Mostrando {results.items.length} de {results.total}. Afina la búsqueda.
                        </p>
                    {:else if results.items.length === 0}
                        <p class="py-6 text-center text-sm text-muted-foreground">Sin resultados para “{search}”.</p>
                    {/if}
                </div>
            {/if}
        </div>
    </DialogContent>
</Dialog>
