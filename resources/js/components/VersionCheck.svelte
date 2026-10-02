<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import { RefreshCw } from '@lucide/svelte';
    import { Button } from '@/components/ui/button';

    // Versión inicial: la que traía el HTML al cargar
    let currentVersion = $state<string | null>(null);
    let needsUpdate = $state(false);

    $effect(() => {
        if (currentVersion !== null) return;

        // leer la versión del meta tag (lo incrusta el backend en cada render)
        const meta = document.querySelector('meta[name="app-version"]');
        currentVersion = meta?.getAttribute('content') ?? null;

        if (!currentVersion) return;

        const check = setInterval(async () => {
            try {
                const response = await fetch('/version', {
                    headers: { Accept: 'application/json' },
                    cache: 'no-store',
                });
                const data = await response.json();
                if (data.version && data.version !== currentVersion) {
                    needsUpdate = true;
                    clearInterval(check);
                }
            } catch {
                // sin conexión o error: seguir intentando
            }
        }, 15_000); // cada 15 segundos

        return () => clearInterval(check);
    });

    function hardRefresh() {
        // limpiar service workers y caché del navegador antes de recargar
        if ('caches' in window) {
            caches.keys().then((names) => names.forEach((n) => caches.delete(n)));
        }
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then((regs) => regs.forEach((r) => r.unregister()));
        }
        window.location.href = window.location.pathname + '?v=' + Date.now();
    }
</script>

{#if needsUpdate}
    <div class="fixed inset-0 z-[100] flex items-center justify-center bg-background/95 backdrop-blur">
        <div class="mx-4 flex max-w-sm flex-col items-center gap-4 rounded-2xl border bg-card p-8 text-center shadow-xl">
            <div class="flex size-16 items-center justify-center rounded-full bg-primary/10 text-primary">
                <RefreshCw class="size-8" />
            </div>
            <div>
                <h2 class="text-lg font-bold">Nueva versión disponible</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Se actualizó BichoFit. Actualiza para seguir usando la última versión.
                </p>
            </div>
            <Button size="lg" class="w-full" onclick={hardRefresh}>
                Actualizar ahora
            </Button>
        </div>
    </div>
{/if}
