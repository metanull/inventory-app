<?php

namespace Tests\Feature\Api\Internationalization;

use App\Models\Author;
use App\Models\Contextualization;
use App\Models\Internationalization;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_authenticated_user_can_create_internationalization(): void
    {
        $contextualization = Contextualization::factory()->create();
        $language = Language::factory()->create();
        $data = Internationalization::factory()->make([
            'contextualization_id' => $contextualization->id,
            'language_id' => $language->id,
        ])->toArray();

        $response = $this->postJson(route('internationalization.store'), $data);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'contextualization_id',
                    'language_id',
                    'name',
                    'description',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.contextualization_id', $data['contextualization_id'])
            ->assertJsonPath('data.language_id', $data['language_id'])
            ->assertJsonPath('data.name', $data['name'])
            ->assertJsonPath('data.description', $data['description']);

        $this->assertDatabaseHas('internationalizations', [
            'contextualization_id' => $data['contextualization_id'],
            'language_id' => $data['language_id'],
            'name' => $data['name'],
            'description' => $data['description'],
        ]);
    }

    public function test_store_with_all_optional_fields(): void
    {
        $contextualization = Contextualization::factory()->create();
        $language = Language::factory()->create();
        $author = Author::factory()->create();
        $data = [
            'contextualization_id' => $contextualization->id,
            'language_id' => $language->id,
            'name' => $this->faker->words(3, true),
            'alternate_name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'type' => 'Manuscript',
            'holder' => $this->faker->company(),
            'owner' => $this->faker->name(),
            'initial_owner' => $this->faker->name(),
            'dates' => '12th century',
            'location' => $this->faker->city(),
            'dimensions' => '30cm x 20cm',
            'place_of_production' => $this->faker->city(),
            'method_for_datation' => 'Carbon dating',
            'method_for_provenance' => 'Historical records',
            'obtention' => 'Donation',
            'bibliography' => 'Smith, J. (2020). Ancient Artifacts.',
            'extra' => ['notes' => 'Special item'],
            'author_id' => $author->id,
            'text_copy_editor_id' => $author->id,
            'translator_id' => $author->id,
            'translation_copy_editor_id' => $author->id,
            'backward_compatibility' => $this->faker->uuid(),
        ];

        $response = $this->postJson(route('internationalization.store'), $data);

        $response->assertCreated();
        $this->assertDatabaseHas('internationalizations', [
            'contextualization_id' => $data['contextualization_id'],
            'language_id' => $data['language_id'],
            'name' => $data['name'],
            'alternate_name' => $data['alternate_name'],
            'type' => $data['type'],
            'holder' => $data['holder'],
        ]);
    }

    public function test_store_requires_contextualization_id(): void
    {
        $data = Internationalization::factory()->make()->toArray();
        unset($data['contextualization_id']);

        $response = $this->postJson(route('internationalization.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['contextualization_id']);
    }

    public function test_store_requires_language_id(): void
    {
        $data = Internationalization::factory()->make()->toArray();
        unset($data['language_id']);

        $response = $this->postJson(route('internationalization.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['language_id']);
    }

    public function test_store_requires_name(): void
    {
        $data = Internationalization::factory()->make()->toArray();
        unset($data['name']);

        $response = $this->postJson(route('internationalization.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_requires_description(): void
    {
        $data = Internationalization::factory()->make()->toArray();
        unset($data['description']);

        $response = $this->postJson(route('internationalization.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['description']);
    }

    public function test_store_validates_contextualization_id_exists(): void
    {
        $data = Internationalization::factory()->make([
            'contextualization_id' => $this->faker->uuid(),
        ])->toArray();

        $response = $this->postJson(route('internationalization.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['contextualization_id']);
    }

    public function test_store_validates_language_id_exists(): void
    {
        $data = Internationalization::factory()->make([
            'language_id' => 'xxx',
        ])->toArray();

        $response = $this->postJson(route('internationalization.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['language_id']);
    }

    public function test_store_validates_language_id_length(): void
    {
        $data = Internationalization::factory()->make([
            'language_id' => 'en',
        ])->toArray();

        $response = $this->postJson(route('internationalization.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['language_id']);
    }

    public function test_store_validates_author_id_exists(): void
    {
        $data = Internationalization::factory()->make([
            'author_id' => $this->faker->uuid(),
        ])->toArray();

        $response = $this->postJson(route('internationalization.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['author_id']);
    }

    public function test_store_prohibits_id_field(): void
    {
        $data = Internationalization::factory()->make([
            'id' => $this->faker->uuid(),
        ])->toArray();

        $response = $this->postJson(route('internationalization.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['id']);
    }

    public function test_store_enforces_unique_constraint_on_contextualization_language_combination(): void
    {
        $contextualization = Contextualization::factory()->create();
        $language = Language::factory()->create();

        // Create first internationalization
        Internationalization::factory()->create([
            'contextualization_id' => $contextualization->id,
            'language_id' => $language->id,
        ]);

        // Try to create duplicate
        $data = Internationalization::factory()->make([
            'contextualization_id' => $contextualization->id,
            'language_id' => $language->id,
        ])->toArray();

        $response = $this->postJson(route('internationalization.store'), $data);

        $response->assertUnprocessable();
    }

    public function test_store_allows_multiple_languages_for_same_contextualization(): void
    {
        $contextualization = Contextualization::factory()->create();
        $language1 = Language::factory()->create(['id' => 'eng']);
        $language2 = Language::factory()->create(['id' => 'fra']);

        $data1 = Internationalization::factory()->make([
            'contextualization_id' => $contextualization->id,
            'language_id' => $language1->id,
        ])->toArray();

        $data2 = Internationalization::factory()->make([
            'contextualization_id' => $contextualization->id,
            'language_id' => $language2->id,
        ])->toArray();

        $response1 = $this->postJson(route('internationalization.store'), $data1);
        $response2 = $this->postJson(route('internationalization.store'), $data2);

        $response1->assertCreated();
        $response2->assertCreated();
    }

    public function test_store_converts_empty_strings_to_null(): void
    {
        $data = Internationalization::factory()->make([
            'alternate_name' => '',
            'type' => '',
            'holder' => '',
        ])->toArray();

        $response = $this->postJson(route('internationalization.store'), $data);

        $response->assertCreated();
        $internationalization = Internationalization::find($response->json('data.id'));
        $this->assertNull($internationalization->alternate_name);
        $this->assertNull($internationalization->type);
        $this->assertNull($internationalization->holder);
    }
}
