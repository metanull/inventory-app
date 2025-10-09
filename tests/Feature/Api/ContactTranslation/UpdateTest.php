<?php

namespace Tests\Feature\Api\ContactTranslation;

use App\Models\ContactTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class UpdateTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createDataUser();
        $this->actingAs($this->user);
    }

    public function test_can_update_contact_translation()
    {
        $contact = \App\Models\Contact::factory()->withoutTranslations()->create();
        $language = \App\Models\Language::factory()->create();
        $translation = ContactTranslation::factory()->create([
            'contact_id' => $contact->id,
            'language_id' => $language->id,
        ]);
        $data = ['label' => 'Updated Label'];

        $response = $this->putJson(route('contact-translation.update', $translation->id), $data);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'contact_id',
                    'language_id',
                    'label',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.label', 'Updated Label');

        $this->assertDatabaseHas('contact_translations', [
            'id' => $translation->id,
            'label' => 'Updated Label',
        ]);
    }

    public function test_returns_404_for_nonexistent_contact_translation()
    {
        $data = ['label' => 'Updated Label'];
        $response = $this->putJson(route('contact-translation.update', 'nonexistent-id'), $data);
        $response->assertNotFound();
    }
}
