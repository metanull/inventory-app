<?php

namespace Tests\Feature\Api\Auth;

use App\Models\AvailableImage;
use App\Models\Collection;
use App\Models\CollectionTranslation;
use App\Models\Context;
use App\Models\Country;
use App\Models\Glossary;
use App\Models\GlossarySpelling;
use App\Models\GlossaryTranslation;
use App\Models\ImageUpload;
use App\Models\Item;
use App\Models\ItemTranslation;
use App\Models\Language;
use App\Models\Location;
use App\Models\LocationTranslation;
use App\Models\Partner;
use App\Models\PartnerImage;
use App\Models\PartnerTranslation;
use App\Models\PartnerTranslationImage;
use App\Models\Project;
use App\Models\Province;
use App\Models\ProvinceTranslation;
use App\Models\Tag;
use App\Models\Theme;
use App\Models\ThemeTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test that API routes are properly protected by auth:sanctum middleware.
 *
 * This test ensures that all API endpoints requiring authentication
 * properly reject unauthenticated requests with 401 status.
 *
 * Replaces 60+ individual AnonymousTest.php files with centralized testing.
 */
class ApiAuthenticationMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Data provider for protected API index routes.
     *
     * @return array<string, array{route: string}>
     */
    public static function protectedIndexRoutesProvider(): array
    {
        return [
            'available-image.index' => ['route' => 'available-image.index'],
            'collection.index' => ['route' => 'collection.index'],
            'collection-translation.index' => ['route' => 'collection-translation.index'],
            'context.index' => ['route' => 'context.index'],
            'country.index' => ['route' => 'country.index'],
            'glossary.index' => ['route' => 'glossary.index'],
            'glossary-spelling.index' => ['route' => 'glossary-spelling.index'],
            'glossary-translation.index' => ['route' => 'glossary-translation.index'],
            'image-upload.index' => ['route' => 'image-upload.index'],
            'item.index' => ['route' => 'item.index'],
            'item-translation.index' => ['route' => 'item-translation.index'],
            'language.index' => ['route' => 'language.index'],
            'location.index' => ['route' => 'location.index'],
            'location-translation.index' => ['route' => 'location-translation.index'],
            'partner.index' => ['route' => 'partner.index'],
            'partner-image.index' => ['route' => 'partner-image.index'],
            'partner-translation.index' => ['route' => 'partner-translation.index'],
            'partner-translation-image.index' => ['route' => 'partner-translation-image.index'],
            'project.index' => ['route' => 'project.index'],
            'province.index' => ['route' => 'province.index'],
            'province-translation.index' => ['route' => 'province-translation.index'],
            'tag.index' => ['route' => 'tag.index'],
            'theme.index' => ['route' => 'theme.index'],
            'theme-translation.index' => ['route' => 'theme-translation.index'],
        ];
    }

    /**
     * Data provider for protected API show routes.
     *
     * @return array<string, array{route: string, model: string}>
     */
    public static function protectedShowRoutesProvider(): array
    {
        return [
            'available-image.show' => ['route' => 'available-image.show', 'model' => AvailableImage::class],
            'collection.show' => ['route' => 'collection.show', 'model' => Collection::class],
            'collection-translation.show' => ['route' => 'collection-translation.show', 'model' => CollectionTranslation::class],
            'context.show' => ['route' => 'context.show', 'model' => Context::class],
            'country.show' => ['route' => 'country.show', 'model' => Country::class],
            'glossary.show' => ['route' => 'glossary.show', 'model' => Glossary::class],
            'glossary-spelling.show' => ['route' => 'glossary-spelling.show', 'model' => GlossarySpelling::class],
            'glossary-translation.show' => ['route' => 'glossary-translation.show', 'model' => GlossaryTranslation::class],
            'image-upload.show' => ['route' => 'image-upload.show', 'model' => ImageUpload::class],
            'item.show' => ['route' => 'item.show', 'model' => Item::class],
            'item-translation.show' => ['route' => 'item-translation.show', 'model' => ItemTranslation::class],
            'language.show' => ['route' => 'language.show', 'model' => Language::class],
            'location.show' => ['route' => 'location.show', 'model' => Location::class],
            'location-translation.show' => ['route' => 'location-translation.show', 'model' => LocationTranslation::class],
            'partner.show' => ['route' => 'partner.show', 'model' => Partner::class],
            'partner-image.show' => ['route' => 'partner-image.show', 'model' => PartnerImage::class],
            'partner-translation.show' => ['route' => 'partner-translation.show', 'model' => PartnerTranslation::class],
            'partner-translation-image.show' => ['route' => 'partner-translation-image.show', 'model' => PartnerTranslationImage::class],
            'project.show' => ['route' => 'project.show', 'model' => Project::class],
            'province.show' => ['route' => 'province.show', 'model' => Province::class],
            'province-translation.show' => ['route' => 'province-translation.show', 'model' => ProvinceTranslation::class],
            'tag.show' => ['route' => 'tag.show', 'model' => Tag::class],
            'theme.show' => ['route' => 'theme.show', 'model' => Theme::class],
            'theme-translation.show' => ['route' => 'theme-translation.show', 'model' => ThemeTranslation::class],
        ];
    }

    /**
     * Data provider for protected API create/store routes.
     *
     * @return array<string, array{route: string}>
     */
    public static function protectedStoreRoutesProvider(): array
    {
        return [
            'collection.store' => ['route' => 'collection.store'],
            'collection-translation.store' => ['route' => 'collection-translation.store'],
            'context.store' => ['route' => 'context.store'],
            'country.store' => ['route' => 'country.store'],
            'glossary.store' => ['route' => 'glossary.store'],
            'glossary-spelling.store' => ['route' => 'glossary-spelling.store'],
            'glossary-translation.store' => ['route' => 'glossary-translation.store'],
            'image-upload.store' => ['route' => 'image-upload.store'],
            'item.store' => ['route' => 'item.store'],
            'item-translation.store' => ['route' => 'item-translation.store'],
            'language.store' => ['route' => 'language.store'],
            'location.store' => ['route' => 'location.store'],
            'location-translation.store' => ['route' => 'location-translation.store'],
            'partner.store' => ['route' => 'partner.store'],
            'partner-image.store' => ['route' => 'partner-image.store'],
            'partner-translation.store' => ['route' => 'partner-translation.store'],
            'partner-translation-image.store' => ['route' => 'partner-translation-image.store'],
            'project.store' => ['route' => 'project.store'],
            'province.store' => ['route' => 'province.store'],
            'province-translation.store' => ['route' => 'province-translation.store'],
            'tag.store' => ['route' => 'tag.store'],
            'theme.store' => ['route' => 'theme.store'],
            'theme-translation.store' => ['route' => 'theme-translation.store'],
        ];
    }

    /**
     * Data provider for protected special PATCH routes (setDefault, setEnabled, setLaunched).
     *
     * @return array<string, array{route: string, param: string, data: array<string, mixed>}>
     */
    public static function protectedSpecialPatchRoutesProvider(): array
    {
        return [
            'language.setDefault' => [
                'route' => 'language.setDefault',
                'model' => Language::class,
                'data' => ['is_default' => true],
            ],
            'context.setDefault' => [
                'route' => 'context.setDefault',
                'model' => Context::class,
                'data' => ['is_default' => true],
            ],
            'project.setEnabled' => [
                'route' => 'project.setEnabled',
                'model' => Project::class,
                'data' => ['enabled' => true],
            ],
            'project.setLaunched' => [
                'route' => 'project.setLaunched',
                'model' => Project::class,
                'data' => ['launched' => true],
            ],
        ];
    }

    /**
     * Data provider for protected special GET routes (getDefault, enabled list).
     *
     * @return array<string, array{route: string}>
     */
    public static function protectedSpecialGetRoutesProvider(): array
    {
        return [
            'language.getDefault' => ['route' => 'language.getDefault'],
            'context.getDefault' => ['route' => 'context.getDefault'],
            'project.enabled' => ['route' => 'project.enabled'],
        ];
    }

    /**
     * Data provider for protected special DELETE routes (clearDefault).
     *
     * @return array<string, array{route: string}>
     */
    public static function protectedSpecialDeleteRoutesProvider(): array
    {
        return [
            'language.clearDefault' => ['route' => 'language.clearDefault'],
            'context.clearDefault' => ['route' => 'context.clearDefault'],
        ];
    }

    /**
     * Test that unauthenticated requests to index routes return 401.
     *
     * @dataProvider protectedIndexRoutesProvider
     */
    public function test_unauthenticated_index_requests_return_401(string $route): void
    {
        $response = $this->getJson(route($route));

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    /**
     * Test that unauthenticated requests to show routes return 401.
     *
     * @dataProvider protectedShowRoutesProvider
     */
    public function test_unauthenticated_show_requests_return_401(string $route, string $model): void
    {
        $instance = $model::factory()->create();

        $response = $this->getJson(route($route, $instance));

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    /**
     * Test that unauthenticated requests to store routes return 401.
     *
     * @dataProvider protectedStoreRoutesProvider
     */
    public function test_unauthenticated_store_requests_return_401(string $route): void
    {
        $response = $this->postJson(route($route), []);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    /**
     * Test that authenticated requests to index routes pass middleware.
     * (They may still fail on permissions, but should not return 401)
     */
    public function test_authenticated_requests_pass_middleware(): void
    {
        $user = User::factory()->create();

        // Test a sample of routes
        $response = $this->actingAs($user, 'sanctum')->getJson(route('item.index'));
        $this->assertNotEquals(401, $response->status(), 'Authenticated request should not return 401');

        $response = $this->actingAs($user, 'sanctum')->getJson(route('collection.index'));
        $this->assertNotEquals(401, $response->status(), 'Authenticated request should not return 401');

        $response = $this->actingAs($user, 'sanctum')->getJson(route('partner.index'));
        $this->assertNotEquals(401, $response->status(), 'Authenticated request should not return 401');
    }

    /**
     * Test that unauthenticated requests return JSON, not HTML redirects.
     */
    public function test_unauthenticated_requests_return_json_not_redirect(): void
    {
        $response = $this->getJson(route('item.index'));

        $response->assertStatus(401);
        $response->assertHeader('Content-Type', 'application/json');
        $this->assertNull($response->headers->get('Location'), 'API should not redirect');
    }

    /**
     * Test that unauthenticated requests to special PATCH routes return 401.
     *
     * @dataProvider protectedSpecialPatchRoutesProvider
     */
    public function test_unauthenticated_special_patch_requests_return_401(string $route, string $model, array $data): void
    {
        $instance = $model::factory()->create();

        $response = $this->patchJson(route($route, $instance), $data);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    /**
     * Test that unauthenticated requests to special GET routes return 401.
     *
     * @dataProvider protectedSpecialGetRoutesProvider
     */
    public function test_unauthenticated_special_get_requests_return_401(string $route): void
    {
        $response = $this->getJson(route($route));

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    /**
     * Test that unauthenticated requests to special DELETE routes return 401.
     *
     * @dataProvider protectedSpecialDeleteRoutesProvider
     */
    public function test_unauthenticated_special_delete_requests_return_401(string $route): void
    {
        $response = $this->deleteJson(route($route));

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    /**
     * Test that public routes are accessible without authentication.
     */
    public function test_public_routes_accessible_without_authentication(): void
    {
        // Info endpoints
        $response = $this->getJson(route('info.index'));
        $response->assertStatus(200);

        $response = $this->getJson(route('info.health'));
        $response->assertStatus(200);

        $response = $this->getJson(route('info.version'));
        $response->assertStatus(200);
    }

    /**
     * Test that markdown utility routes are accessible without authentication.
     */
    public function test_markdown_routes_accessible_without_authentication(): void
    {
        $response = $this->postJson(route('markdown.toHtml'), [
            'markdown' => '# Test',
        ]);
        $response->assertStatus(200);

        $response = $this->getJson(route('markdown.allowedElements'));
        $response->assertStatus(200);
    }
}
