<?php

namespace Tests\Feature;

use App\Models\BodyScan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia as InertiaPage;
use Tests\TestCase;

class BodyScanTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_ve_solo_sus_mediciones(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        BodyScan::factory()->for($user)->count(2)->create();
        BodyScan::factory()->for($other)->create();

        $this->actingAs($user)
            ->get(route('body-scans.index'))
            ->assertOk()
            ->assertInertia(fn (InertiaPage $page) => $page->has('scans', 2));
    }

    public function test_guarda_una_medicion_confirmada(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('body-scans.store'), [
                'scanned_at' => '2026-09-30',
                'weight_kg' => 74.5,
                'body_fat_pct' => 18.2,
                'muscle_mass_kg' => 55.1,
                'source' => 'ocr',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('body_scans', [
            'user_id' => $user->id,
            'weight_kg' => 74.5,
            'body_fat_pct' => 18.2,
            'source' => 'ocr',
        ]);
    }

    public function test_rechaza_valores_imposibles(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('body-scans.store'), [
                'scanned_at' => '2026-09-30',
                'weight_kg' => 900,
                'body_fat_pct' => 95,
            ])
            ->assertSessionHasErrors(['weight_kg', 'body_fat_pct']);

        $this->assertDatabaseCount('body_scans', 0);
    }

    public function test_no_puede_borrar_medicion_ajena(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $scan = BodyScan::factory()->for($owner)->create();

        $this->actingAs($intruder)
            ->delete(route('body-scans.destroy', $scan))
            ->assertForbidden();

        $this->assertDatabaseHas('body_scans', ['id' => $scan->id]);
    }

    public function test_ocr_con_imagen_invalida_da_error_de_validacion(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('body-scans.ocr'), [
                'image' => UploadedFile::fake()->create('nota.txt', 10, 'text/plain'),
            ])
            ->assertSessionHasErrors(['image']);
    }
}
