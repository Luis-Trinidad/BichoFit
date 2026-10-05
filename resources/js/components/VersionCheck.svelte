<script lang="ts">
    import { RefreshCw } from '@lucide/svelte';
    import { Button } from '@/components/ui/button';
    import { page } from '@inertiajs/svelte';

    let needsUpdate = $state(false);

    /** Versión actual del cliente: se fija una sola vez al cargar la app. */
    function initialVersion(): string | null {
        return document.querySelector('meta[name="app-version"]')?.getAttribute('content') ?? null;
    }

    async function checkOnce(clientVersion: string): Promise<string | null> {
        try {
            const response = await fetch(`/version?t=${Date.now()}`, {
                headers: { Accept: 'application/json' },
                cache: 'no-store',
            });
            if (!response.ok) return null;
            const data = await response.json();
            return data.version && data.version !== clientVersion ? data.version : null;
        } catch {
            return null;
        }
    }

    function hardRefresh() {
        if ('caches' in window) {
            caches.keys().then((names) => names.forEach((n) => caches.delete(n)));
        }
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then((regs) => regs.forEach((r) => r.unregister()));
        }
        // recarga dura: salta caché de disco y de memorias intermedias
        window.location.href = window.location.pathname + '?v=' + Date.now();
    }

    $effect(() => {
        const clientVersion = initialVersion();
        if (!clientVersion) return;

        let stopped = false;
        let timer: ReturnType<typeof setInterval> | undefined;

        async function check() {
            if (stopped || needsUpdate) return;
            const newVersion = await checkOnce(clientVersion);
            if (newVersion) needsUpdate = true;
        }

        // Poll periódico mientras la app está abierta
        timer = setInterval(check, 15_000);

        // PWA: verificar en cuanto la app vuelve al frente (caso real:
        // la abres al día siguiente tras un deploy nocturno)
        const onVisible = () => {
            if (document.visibilityState === 'visible') void check();
        };
        document.addEventListener('visibilitychange', onVisible);

        // Y una verificación inmediata al montar
        void check();

        return () => {
            stopped = true;
            if (timer) clearInterval(timer);
            document.removeEventListener('visibilitychange', onVisible);
        };
    });
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
                    Se actualizó BichoFit. Toca actualizar para usar la última versión.
                </p>
            </div>
            <Button size="lg" class="w-full" onclick={hardRefresh}>
                Actualizar ahora
            </Button>
        </div>
    </div>
{/if}
