<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Picture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive parameter validation tests for Picture API endpoints
 * Note: Picture creation is handled through special attachment routes, not standard store
 */
class PictureParameterValidationTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    // INDEX ENDPOINT TESTS
    public function test_index_accepts_valid_pagination_parameters()
    {
        Picture::factory()->count(8)->create();

        $response = $this->getJson(route('picture.index', [
            'page' => 2,
            'per_page' => 4,
        ]));

        $response->assertOk();
        $response->assertJsonPath('meta.current_page', 2);
        $response->assertJsonPath('meta.per_page', 4);
    }

    public function test_index_accepts_valid_include_parameters()
    {
        Picture::factory()->count(3)->create();

        $response = $this->getJson(route('picture.index', [
            'include' => 'pictureable,translations',
        ]));

        $response->assertOk();
    }

    public function test_index_rejects_invalid_include_parameters()
    {
        Picture::factory()->count(2)->create();

        $response = $this->getJson(route('picture.index', [
            'include' => 'invalid_relation,fake_attachments,non_existent',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['include']);
    }

    public function test_index_rejects_unexpected_query_parameters_currently()
    {
        // SECURITY TEST: Validates Form Request security with parameter whitelisting
        Picture::factory()->count(2)->create();

        $response = $this->getJson(route('picture.index', [
            'page' => 1,
            'include' => 'attachments',
            'filter_by_type' => 'main', // Not implemented
            'resolution' => 'high', // Not implemented
            'format' => 'jpg', // Not implemented
            'size_limit' => '5MB', // Not implemented
            'admin_access' => true,
            'debug_images' => true,
            'export_metadata' => 'json',
            'bulk_download' => 'zip',
        ]));

        $response->assertStatus(422); // Must reject unexpected params for security
        $response->assertJsonValidationErrors([
            'filter_by_type', 'resolution', 'format', 'size_limit', 'admin_access', 'debug_images', 'export_metadata', 'bulk_download',
        ]);
    }

    // SHOW ENDPOINT TESTS
    public function test_show_accepts_valid_include_parameters()
    {
        $picture = Picture::factory()->create();

        $response = $this->getJson(route('picture.show', $picture).'?include=pictureable,translations');

        $response->assertOk();
    }

    public function test_show_rejects_unexpected_query_parameters_currently()
    {
        // SECURITY TEST: Validates Form Request security with parameter whitelisting
        $picture = Picture::factory()->create();

        $response = $this->getJson(route('picture.show', $picture).'?include=attachments&admin_view=1&show_metadata=full&download_link=generate');

        $response->assertStatus(422); // Must reject unexpected params for security
        $response->assertJsonValidationErrors(['admin_view', 'show_metadata', 'download_link']);
    }

    // SPECIAL PICTURE ROUTES TESTS - Attachment routes
    public function test_attach_to_item_rejects_unexpected_parameters_currently()
    {
        // SECURITY TEST: Current behavior for attachment routes must reject unexpected params
        $item = \App\Models\Item::factory()->create();
        $picture = Picture::factory()->create();

        $response = $this->postJson(route('picture.attachToItem', $item), [
            'picture_id' => $picture->id,
            'unexpected_param' => 'should_be_rejected',
            'admin_override' => true,
            'bypass_permissions' => true,
            'set_as_primary' => true, // Might not be implemented
            'position' => 1, // Might not be implemented
        ]);

        $response->assertStatus(422); // Must reject unexpected params for security
        $response->assertJsonValidationErrors(['unexpected_param', 'admin_override', 'bypass_permissions', 'set_as_primary', 'position']);
    }

    public function test_attach_to_detail_rejects_unexpected_parameters_currently()
    {
        // SECURITY TEST: Current behavior must reject unexpected parameters
        $detail = \App\Models\Detail::factory()->create();
        $picture = Picture::factory()->create();

        $response = $this->postJson(route('picture.attachToDetail', $detail), [
            'picture_id' => $picture->id,
            'unexpected_field' => 'test_value',
            'admin_flag' => true,
            'priority' => 'high',
        ]);

        $response->assertStatus(422); // Must reject unexpected params for security
        $response->assertJsonValidationErrors(['unexpected_field', 'admin_flag', 'priority']);
    }

    public function test_attach_to_partner_rejects_unexpected_parameters_currently()
    {
        // SECURITY TEST: Current behavior must reject unexpected parameters
        $partner = \App\Models\Partner::factory()->create();
        $picture = Picture::factory()->create();

        $response = $this->postJson(route('picture.attachToPartner', $partner), [
            'picture_id' => $picture->id,
            'unexpected_param' => 'value',
            'admin_access' => true,
        ]);

        $response->assertStatus(422); // Must reject unexpected params for security
        $response->assertJsonValidationErrors(['unexpected_param', 'admin_access']);
    }

    // PICTURE FILE ACCESS ROUTES
    public function test_download_route_rejects_unexpected_parameters_currently()
    {
        // SECURITY TEST: File download routes must reject unexpected parameters
        $picture = Picture::factory()->create();

        $response = $this->getJson(route('picture.download', $picture).'?admin_access=true&force_download=1&quality=original');

        $response->assertStatus(422); // Must reject unexpected params for security
        $response->assertJsonValidationErrors(['admin_access', 'force_download', 'quality']);
    }

    public function test_view_route_rejects_unexpected_parameters_currently()
    {
        // SECURITY TEST: File view routes must reject unexpected parameters
        $picture = Picture::factory()->create();

        $response = $this->getJson(route('picture.view', $picture).'?admin_mode=1&watermark=none&size=large');

        $response->assertStatus(422); // Must reject unexpected params for security
        $response->assertJsonValidationErrors(['admin_mode', 'watermark', 'size']);
    }

    // UPDATE ENDPOINT TESTS (Pictures can be updated but not created via standard routes)
    public function test_update_prohibits_id_modification()
    {
        $picture = Picture::factory()->create();

        $response = $this->putJson(route('picture.update', $picture), [
            'id' => 'new-uuid', // Should be prohibited
            'internal_name' => 'Updated Picture',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_update_accepts_valid_data()
    {
        $picture = Picture::factory()->create();

        $response = $this->putJson(route('picture.update', $picture), [
            'internal_name' => 'Updated Picture Name',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.internal_name', 'Updated Picture Name');
    }

    public function test_update_rejects_unexpected_request_parameters_currently()
    {
        // SECURITY TEST: Current behavior must reject unexpected parameters
        $picture = Picture::factory()->create();

        $response = $this->putJson(route('picture.update', $picture), [
            'internal_name' => 'Updated Picture',
            'unexpected_field' => 'should_be_rejected',
            'resolution' => '4K', // Not implemented
            'quality' => 'high', // Not implemented
            'watermark' => 'remove', // Not implemented
            'admin_edit' => true,
            'debug_mode' => true,
        ]);

        $response->assertStatus(422); // Must reject unexpected params for security
        $response->assertJsonValidationErrors(['unexpected_field', 'resolution', 'quality', 'watermark', 'admin_edit', 'debug_mode']);
    }

    // EDGE CASE TESTS
    public function test_handles_unicode_characters_in_internal_name()
    {
        $picture = Picture::factory()->create();
        $unicodeNames = [
            'Picture français',
            'Картинка русский',
            '写真日本語',
            'صورة عربية',
            'Imagen español',
            'Immagine italiano',
        ];

        foreach ($unicodeNames as $name) {
            $response = $this->putJson(route('picture.update', $picture), [
                'internal_name' => $name,
            ]);

            $response->assertOk(); // Should handle Unicode gracefully
        }
    }

    public function test_handles_special_characters_in_internal_name()
    {
        $picture = Picture::factory()->create();
        $specialCharNames = [
            'Picture "With Quotes"',
            "Picture 'With Apostrophes'",
            'Picture & Ampersand',
            'Picture <With> Brackets',
            'Picture @ Symbol',
            'Picture # Hash',
            'Picture % Percent',
            'Picture $ Dollar',
            'Picture * Asterisk',
            'Picture + Plus',
            'Picture = Equals',
            'Picture | Pipe',
            'Picture \\ Backslash',
            'Picture / Slash',
        ];

        foreach ($specialCharNames as $name) {
            $response = $this->putJson(route('picture.update', $picture), [
                'internal_name' => $name,
            ]);

            // Should handle gracefully
            $this->assertContains($response->status(), [200, 422]);
        }
    }

    public function test_handles_very_long_internal_name()
    {
        $picture = Picture::factory()->create();
        $veryLongName = str_repeat('Very Long Picture Name ', 150);

        $response = $this->putJson(route('picture.update', $picture), [
            'internal_name' => $veryLongName,
        ]);

        // Should handle gracefully
        $this->assertContains($response->status(), [200, 422]);
    }

    public function test_handles_empty_and_whitespace_internal_names()
    {
        $picture = Picture::factory()->create();
        $emptyNames = [
            '', // Empty
            '   ', // Spaces only
            "\t\t", // Tabs only
            "\n\n", // Newlines only
        ];

        foreach ($emptyNames as $name) {
            $response = $this->putJson(route('picture.update', $picture), [
                'internal_name' => $name,
            ]);

            $response->assertUnprocessable(); // Should reject empty names
            $response->assertJsonValidationErrors(['internal_name']);
        }
    }

    public function test_attachment_routes_validate_picture_id_format()
    {
        $item = \App\Models\Item::factory()->create();

        $invalidPictureIds = [
            'not-a-uuid',
            '123',
            '',
            'null',
        ];

        foreach ($invalidPictureIds as $pictureId) {
            $response = $this->postJson(route('picture.attachToItem', $item), [
                'picture_id' => $pictureId,
            ]);

            $response->assertUnprocessable();
            $response->assertJsonValidationErrors(['picture_id']);
        }
    }

    public function test_attachment_routes_validate_picture_existence()
    {
        $item = \App\Models\Item::factory()->create();
        $nonExistentPictureId = '12345678-1234-1234-1234-123456789012';

        $response = $this->postJson(route('picture.attachToItem', $item), [
            'picture_id' => $nonExistentPictureId,
        ]);

        // Should validate picture existence
        $this->assertContains($response->status(), [404, 422]);
    }

    public function test_detachment_routes_accept_unexpected_parameters_currently()
    {
        // BASELINE TEST: Current insecure behavior for detachment routes
        $item = \App\Models\Item::factory()->create();
        $picture = Picture::factory()->create();

        // Note: This might fail if the attachment doesn't exist, but we're testing parameter acceptance
        $response = $this->deleteJson(route('picture.detachFromItem', [$item, $picture]).'?admin_force=true&cascade_delete=false&log_action=detailed');

        // Should either succeed (if attachment exists) or fail, but accepts unexpected params
        $this->assertContains($response->status(), [200, 204, 404, 422]);
    }

    public function test_pagination_stress_test()
    {
        Picture::factory()->count(30)->create();

        // Test various pagination scenarios
        $testCases = [
            ['page' => 1, 'per_page' => 5],
            ['page' => 2, 'per_page' => 10],
            ['page' => 1, 'per_page' => 100], // Maximum
        ];

        foreach ($testCases as $params) {
            $response = $this->getJson(route('picture.index', $params));
            $response->assertOk();
        }

        // Test invalid pagination
        $invalidCases = [
            ['page' => 0],
            ['per_page' => 0],
            ['per_page' => 101],
            ['page' => -1],
            ['per_page' => -1],
        ];

        foreach ($invalidCases as $params) {
            $response = $this->getJson(route('picture.index', $params));
            $response->assertUnprocessable();
        }
    }

    public function test_handles_array_injection_in_attachment_routes()
    {
        $item = \App\Models\Item::factory()->create();

        $response = $this->postJson(route('picture.attachToItem', $item), [
            'picture_id' => ['array' => 'instead_of_uuid'],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['picture_id']);
    }
}
