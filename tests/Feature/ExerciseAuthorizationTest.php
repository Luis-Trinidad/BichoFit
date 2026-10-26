<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExerciseAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_puede_ver_ejercicio_personalizado_ajeno(): void
    {
        $owner = User::factory()->create();
        $exercise = Exercise::factory()->create(['user_id' => $owner->id]);

        $this->actingAs(User::factory()->create())
            ->getJson(route('exercises.show', $exercise))
            ->assertForbidden();
    }

    public function test_puede_ver_propio_y_global(): void
    {
        $user = User::factory()->create();
        $own = Exercise::factory()->create(['user_id' => $user->id]);
        $global = Exercise::factory()->create(['user_id' => null]);

        $this->actingAs($user)->getJson(route('exercises.show', $own))->assertOk();
        $this->actingAs($user)->getJson(route('exercises.show', $global))->assertOk();
    }

    public function test_ocr_tiene_limite_de_peticiones(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($user)->postJson(route('body-scans.ocr'));
        }

        $this->actingAs($user)->postJson(route('body-scans.ocr'))->assertStatus(429);
    }
}
