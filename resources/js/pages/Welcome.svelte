<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import Dumbbell from '@lucide/svelte/icons/dumbbell';
    import CalendarDays from '@lucide/svelte/icons/calendar-days';
    import Scale from '@lucide/svelte/icons/scale';
    import type { Component } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import AppLogoIcon from '@/components/AppLogoIcon.svelte';
    import { toUrl } from '@/lib/utils';
    import { dashboard, login } from '@/routes';
    import { register } from '@/routes';

    const auth = $derived(page.props.auth);

    const features: { icon: Component<{ class?: string }>; title: string; text: string }[] = [
        {
            icon: Dumbbell,
            title: 'Registra cada serie',
            text: 'Reps, peso y descansos al momento. Tu historial completo, siempre a la mano.',
        },
        {
            icon: CalendarDays,
            title: 'Rutinas semanales',
            text: 'Arma tu plan día por día y lánzalo con cuenta regresiva. La app te guía serie por serie.',
        },
        {
            icon: Scale,
            title: 'Tu báscula conectada',
            text: 'Manda la captura de tu báscula y el OCR registra peso, grasa, músculo y más.',
        },
    ];
</script>

<AppHead title="BichoFit">
    <meta
        name="description"
        content="Tu gimnasio, tus números, tu progreso. Registra tus entrenamientos, arma rutinas y sigue tu composición corporal."
    />
</AppHead>

<div class="flex min-h-dvh flex-col items-center bg-background px-6 py-10 text-foreground">
    <main class="flex w-full max-w-lg flex-1 flex-col items-center justify-center gap-10 text-center">
        <div class="flex flex-col items-center gap-3">
            <AppLogoIcon class="size-20 fill-current text-primary" />
            <h1 class="text-4xl font-bold tracking-tight">BichoFit</h1>
            <p class="text-lg font-medium text-muted-foreground">
                Tu gimnasio, tus números, tu progreso.
            </p>
        </div>

        <div class="flex flex-col gap-4">
            {#each features as feature (feature.title)}
                <div class="flex items-start gap-4 rounded-2xl border bg-card p-4 text-left">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <feature.icon class="size-5" />
                    </span>
                    <div>
                        <p class="font-semibold">{feature.title}</p>
                        <p class="text-sm text-muted-foreground">{feature.text}</p>
                    </div>
                </div>
            {/each}
        </div>

        {#if auth.user}
            <Link
                href={toUrl(dashboard())}
                class="flex h-14 w-full items-center justify-center rounded-full bg-primary text-base font-semibold text-primary-foreground shadow-lg shadow-primary/25 transition-all active:scale-[0.98]"
            >
                Entrar a la app
            </Link>
        {:else}
            <Link
                href={toUrl(login())}
                class="flex h-14 w-full items-center justify-center rounded-full bg-primary text-base font-semibold text-primary-foreground shadow-lg shadow-primary/25 transition-all active:scale-[0.98]"
            >
                Entrar
            </Link>
        {/if}
    </main>

    <footer class="pt-8 text-xs text-muted-foreground">
        BichoFit — hecho por y para bichos que entrenan
    </footer>
</div>
