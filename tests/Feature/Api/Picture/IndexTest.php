<?php

namespace Tests\Feature\Api\Picture;

use App\Models\Picture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_list_pictures(): void
    {
        $pictures = collect([
            Picture::factory()->forItem()->create(),
            Picture::factory()->forDetail()->create(),
            Picture::factory()->forPartner()->create(),
        ]);

        $response = $this->getJson(route('picture.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'internal_name',
                    'backward_compatibility',
                    'copyright_text',
                    'copyright_url',
                    'path',
                    'upload_name',
                    'upload_extension',
                    'upload_mime_type',
                    'upload_size',
                    'pictureable_type',
                    'pictureable_id',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);

        $response->assertJsonCount(3, 'data');
    }

    public function test_returns_empty_array_when_no_pictures(): void
    {
        $response = $this->getJson(route('picture.index'));

        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    public function test_requires_authentication(): void
    {
        $this->withoutAuthentication();

        $response = $this->getJson(route('picture.index'));

        $response->assertUnauthorized();
    }

    public function test_includes_polymorphic_relationship_data(): void
    {
        $itemPicture = Picture::factory()->forItem()->create();
        $detailPicture = Picture::factory()->forDetail()->create();
        $partnerPicture = Picture::factory()->forPartner()->create();

        $response = $this->getJson(route('picture.index'));

        $response->assertOk();
        $response->assertJsonCount(3, 'data');

        // Check that each picture has the correct pictureable_type
        $responseData = $response->json('data');
        $pictureableTypes = collect($responseData)->pluck('pictureable_type')->sort()->values();

        $expectedTypes = [
            'App\\Models\\Detail',
            'App\\Models\\Item',
            'App\\Models\\Partner',
        ];

        $this->assertEquals($expectedTypes, $pictureableTypes->toArray());
    }

    private function withoutAuthentication(): void
    {
        $this->user = null;
        $this->app['auth']->forgetGuards();
    }
}
