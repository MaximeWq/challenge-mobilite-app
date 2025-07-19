<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Utilisateur;

class ActivityTest extends TestCase
{
    use RefreshDatabase;


    /**
     * Vérifie qu'un utilisateur ne peut pas modifier une activité d'un jour précédent.
     */
    public function test_user_cannot_update_past_activity()
    {
        $user = Utilisateur::factory()->create();
        $this->actingAs($user, 'sanctum');

        $activity = $user->activites()->create([
            'date' => now()->subDay()->toDateString(),
            'type' => 'velo',
            'distance_km' => 10,
        ]);

        $response = $this->putJson("/api/activities/{$activity->id}", [
            'distance_km' => 15,
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Impossible de modifier une activité des jours précédents',
        ]);
    }

    /**
     * Vérifie qu'un admin peut lister les utilisateurs.
     */
    public function test_admin_can_list_users()
    {
        $admin = Utilisateur::factory()->create(['is_admin' => true]);
        $this->actingAs($admin, 'sanctum');

        $response = $this->getJson('/api/admin/users');
        $response->assertStatus(200);
        $response->assertJsonStructure(['status', 'data', 'meta']);
    }

    /**
     * Vérifie qu'un utilisateur ne peut pas supprimer une activité d'un autre utilisateur.
     */
    public function test_user_cannot_delete_another_users_activity()
    {
        $user1 = Utilisateur::factory()->create();
        $user2 = Utilisateur::factory()->create();
        $activity = $user2->activites()->create([
            'date' => now()->toDateString(),
            'type' => 'velo',
            'distance_km' => 10,
        ]);

        $this->actingAs($user1, 'sanctum');
        $response = $this->deleteJson("/api/activities/{$activity->id}");
        $response->assertStatus(403);
    }

    /**
     * Vérifie la conversion automatique des pas en kilomètres pour la marche/course.
     */
    public function test_steps_are_converted_to_km()
    {
        $user = Utilisateur::factory()->create();
        $this->actingAs($user, 'sanctum');

        $data = [
            'date' => now()->toDateString(),
            'type' => 'marche_course',
            'pas' => 3000,
        ];

        $response = $this->postJson('/api/activities', $data);
        $response->assertStatus(201);
        $this->assertEquals(2.0, $response->json('data.activite.distance_km'));
    }

    /**
     * Vérifie qu'un utilisateur non authentifié ne peut pas accéder aux routes protégées.
     */
    public function test_guest_cannot_access_protected_routes()
    {
        $response = $this->getJson('/api/activities');
        $response->assertStatus(401); // Unauthorized
    }

    /**
     * Vérifie qu'un utilisateur non admin ne peut pas accéder aux routes admin.
     */
    public function test_non_admin_cannot_access_admin_routes()
    {
        $user = Utilisateur::factory()->create(['is_admin' => false]);
        $this->actingAs($user, 'sanctum');
        $response = $this->getJson('/api/admin/users');
        $response->assertStatus(403); // Forbidden
    }

    /**
     * Vérifie qu'on ne peut pas supprimer le dernier administrateur.
     */
    public function test_cannot_delete_last_admin()
    {
        $admin = Utilisateur::factory()->create(['is_admin' => true]);
        $this->actingAs($admin, 'sanctum');
        $response = $this->deleteJson("/api/admin/users/{$admin->id}");
        $response->assertStatus(400);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Impossible de supprimer le dernier administrateur',
        ]);
    }

    /**
     * Vérifie qu'on ne peut pas créer une activité pour une date future.
     */
    public function test_cannot_create_activity_for_future_date()
    {
        $user = Utilisateur::factory()->create();
        $this->actingAs($user, 'sanctum');
        $data = [
            'date' => now()->addDay()->toDateString(),
            'type' => 'velo',
            'distance_km' => 10,
        ];
        $response = $this->postJson('/api/activities', $data);
        $response->assertStatus(422);
    }
}