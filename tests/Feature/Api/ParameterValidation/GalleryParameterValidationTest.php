<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Gallery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive parameter validation tests for Gallery API endpoints
 */
class GalleryParameterValidationTest extends TestCase
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
        Gallery::factory()->count(30)->create();

        $response = $this->getJson(route('gallery.index', [
            'page' => 2,
            'per_page' => 12,
        ]));

        $response->assertOk();
        $response->assertJsonPath('meta.current_page', 2);
        $response->assertJsonPath('meta.per_page', 12);
    }

    public function test_index_accepts_valid_include_parameters()
    {
        Gallery::factory()->count(4)->create();

        $response = $this->getJson(route('gallery.index', [
            'include' => 'translations,exhibitions,pictures',
        ]));

        $response->assertOk();
    }

    public function test_index_rejects_invalid_include_parameters()
    {
        Gallery::factory()->count(2)->create();

        $response = $this->getJson(route('gallery.index', [
            'include' => 'invalid_relation,fake_galleries,non_existent',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['include']);
    }

    public function test_index_rejects_unexpected_query_parameters_currently()
    {
        Gallery::factory()->count(3)->create();

        $response = $this->getJson(route('gallery.index', [
            'page' => 1,
            'include' => 'translations',
            'gallery_type' => 'permanent', // Not implemented
            'location' => 'main_building', // Not implemented
            'capacity' => 'large', // Not implemented
            'accessibility' => 'wheelchair', // Not implemented
            'admin_access' => true,
            'debug_galleries' => true,
            'export_format' => 'xml',
            'bulk_operation' => 'update_all',
        ]));

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['gallery_type']);
    }

    // SHOW ENDPOINT TESTS
    public function test_show_accepts_valid_include_parameters()
    {
        $gallery = Gallery::factory()->create();

        $response = $this->getJson(route('gallery.show', $gallery).'?include=translations,exhibitions,pictures');

        $response->assertOk();
    }

    public function test_show_rejects_unexpected_query_parameters_currently()
    {
        $gallery = Gallery::factory()->create();

        $response = $this->getJson(route('gallery.show', $gallery).'?include=translations&show_floor_plan=true&visitor_flow=detailed');

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['show_floor_plan']);
    }

    // STORE ENDPOINT TESTS
    public function test_store_validates_required_fields()
    {
        $response = $this->postJson(route('gallery.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_store_validates_unique_internal_name()
    {
        $existingGallery = Gallery::factory()->create();

        $response = $this->postJson(route('gallery.store'), [
            'internal_name' => $existingGallery->internal_name,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_store_prohibits_id_field()
    {
        $response = $this->postJson(route('gallery.store'), [
            'id' => 'some-uuid', // Should be prohibited
            'internal_name' => 'Test Gallery',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_store_accepts_valid_data()
    {
        $response = $this->postJson(route('gallery.store'), [
            'internal_name' => 'Main Exhibition Gallery',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.internal_name', 'Main Exhibition Gallery');
    }

    public function test_store_accepts_optional_backward_compatibility_field()
    {
        $response = $this->postJson(route('gallery.store'), [
            'internal_name' => 'Legacy Gallery',
            'backward_compatibility' => 'old_gallery_123',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.backward_compatibility', 'old_gallery_123');
    }

    public function test_store_rejects_unexpected_request_parameters_currently()
    {
        $response = $this->postJson(route('gallery.store'), [
            'internal_name' => 'Test Gallery',
            'unexpected_field' => 'should_be_rejected',
            'floor_number' => '2', // Not implemented
            'square_meters' => '500', // Not implemented
            'max_capacity' => '200', // Not implemented
            'lighting_type' => 'led', // Not implemented
            'climate_control' => true, // Not implemented
            'security_level' => 'high', // Not implemented
            'admin_created' => true,
            'malicious_payload' => '<img src="x" onerror="alert(1)">',
            'sql_injection' => "'; DROP TABLE galleries; --",
            'privilege_escalation' => 'curator_access',
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // UPDATE ENDPOINT TESTS
    public function test_update_validates_unique_internal_name()
    {
        $gallery1 = Gallery::factory()->create();
        $gallery2 = Gallery::factory()->create();

        $response = $this->putJson(route('gallery.update', $gallery1), [
            'internal_name' => $gallery2->internal_name,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_update_prohibits_id_modification()
    {
        $gallery = Gallery::factory()->create();

        $response = $this->putJson(route('gallery.update', $gallery), [
            'id' => 'new-uuid', // Should be prohibited
            'internal_name' => 'Updated Gallery',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_update_accepts_same_internal_name()
    {
        $gallery = Gallery::factory()->create();

        $response = $this->putJson(route('gallery.update', $gallery), [
            'internal_name' => $gallery->internal_name, // Same name should be allowed
        ]);

        $response->assertOk();
    }

    public function test_update_rejects_unexpected_request_parameters_currently()
    {
        $gallery = Gallery::factory()->create();

        $response = $this->putJson(route('gallery.update', $gallery), [
            'internal_name' => 'Updated Gallery',
            'unexpected_field' => 'should_be_rejected',
            'change_layout' => 'open_plan',
            'update_security' => 'enhanced',
            'renovate_lighting' => true,
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // EDGE CASE TESTS
    public function test_handles_unicode_characters_in_internal_name()
    {
        $unicodeNames = [
            'Galerie française',
            'Галерея русская',
            'ギャラリー日本語',
            'معرض عربي',
            'Galería española',
            'Galleria italiana',
            'Galeria polska',
            'Γκαλερί ελληνική',
            'Galleri dansk',
            'Galéria magyar',
        ];

        foreach ($unicodeNames as $name) {
            $response = $this->postJson(route('gallery.store'), [
                'internal_name' => $name,
            ]);

            $response->assertCreated(); // Should handle Unicode gracefully
        }
    }

    public function test_handles_special_characters_in_internal_name()
    {
        $specialCharNames = [
            'Gallery "Main Hall"',
            "Gallery 'East Wing'",
            'Gallery & Exhibition Space',
            'Gallery: Contemporary Art',
            'Gallery (Level 2)',
            'Gallery - North Section',
            'Gallery @ Museum',
            'Gallery #1: Masterworks',
            'Gallery 50% Capacity',
            'Gallery $ Premium',
            'Gallery * Featured',
            'Gallery + Annex',
            'Gallery = Excellence',
            'Gallery | Special',
        ];

        foreach ($specialCharNames as $name) {
            $response = $this->postJson(route('gallery.store'), [
                'internal_name' => $name,
            ]);

            // Should handle gracefully
            $this->assertContains($response->status(), [201, 422]);
        }
    }

    public function test_handles_very_long_internal_name()
    {
        $veryLongName = str_repeat('Very Long Gallery Name With Detailed Architectural Description And Historical Significance ', 20);

        $response = $this->postJson(route('gallery.store'), [
            'internal_name' => $veryLongName,
        ]);

        // Should handle gracefully
        $this->assertContains($response->status(), [201, 422]);
    }

    public function test_handles_empty_and_whitespace_internal_names()
    {
        $emptyNames = [
            '', // Empty
            '   ', // Spaces only
            "\t\t", // Tabs only
            "\n\n", // Newlines only
            "  \t\n  ", // Mixed whitespace
        ];

        foreach ($emptyNames as $name) {
            $response = $this->postJson(route('gallery.store'), [
                'internal_name' => $name,
            ]);

            $response->assertUnprocessable(); // Should reject empty names
            $response->assertJsonValidationErrors(['internal_name']);
        }
    }

    public function test_handles_null_vs_empty_string_for_optional_fields()
    {
        $gallery = Gallery::factory()->create();

        $testCases = [
            ['backward_compatibility' => null],
            ['backward_compatibility' => ''],
        ];

        foreach ($testCases as $data) {
            $updateData = array_merge([
                'internal_name' => 'Test Gallery Update',
            ], $data);

            $response = $this->putJson(route('gallery.update', $gallery), $updateData);

            // Should handle gracefully
            $this->assertContains($response->status(), [200, 422]);
        }
    }

    public function test_pagination_with_many_galleries()
    {
        Gallery::factory()->count(60)->create();

        $testCases = [
            ['page' => 1, 'per_page' => 10],
            ['page' => 3, 'per_page' => 20],
            ['page' => 1, 'per_page' => 100], // Maximum
        ];

        foreach ($testCases as $params) {
            $response = $this->getJson(route('gallery.index', $params));
            $response->assertOk();
        }

        // Test invalid pagination
        $invalidCases = [
            ['page' => 0],
            ['per_page' => 0],
            ['per_page' => 101],
            ['page' => -1],
        ];

        foreach ($invalidCases as $params) {
            $response = $this->getJson(route('gallery.index', $params));
            $response->assertUnprocessable();
        }
    }

    public function test_handles_array_injection_attempts()
    {
        $response = $this->postJson(route('gallery.store'), [
            'internal_name' => ['array' => 'instead_of_string'],
            'backward_compatibility' => ['malicious' => 'array'],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_handles_gallery_name_variations()
    {
        $galleryNameVariations = [
            'Main Exhibition Hall',
            'East Wing Gallery',
            'Contemporary Art Space',
            'Sculpture Garden',
            'Interactive Media Room',
            'Children\'s Discovery Area',
            'Temporary Exhibition Space',
            'Permanent Collection Gallery',
            'Special Events Hall',
            'Education Center',
            'Research Library',
            'Conservation Lab',
            'Storage Facility',
            'Loading Dock',
            'Café Gallery',
            'Gift Shop Area',
            'Auditorium',
            'Conference Room',
            'Studio Space',
            'Workshop Area',
        ];

        foreach ($galleryNameVariations as $name) {
            $response = $this->postJson(route('gallery.store'), [
                'internal_name' => $name,
            ]);

            $response->assertCreated(); // Should handle various gallery name formats
        }
    }

    public function test_handles_case_sensitivity_for_internal_name_uniqueness()
    {
        Gallery::factory()->create(['internal_name' => 'Main Gallery']);

        $caseSensitivityTests = [
            'main gallery', // lowercase
            'MAIN GALLERY', // uppercase
            'Main gallery', // different case
            'main GALLERY', // mixed case
        ];

        foreach ($caseSensitivityTests as $name) {
            $response = $this->postJson(route('gallery.store'), [
                'internal_name' => $name,
            ]);

            // Depending on database collation, these might be unique or not
            $this->assertContains($response->status(), [201, 422]);
        }
    }

    public function test_handles_gallery_name_with_numbers_and_levels()
    {
        $numberedGalleryNames = [
            'Gallery 1',
            'Gallery 2A',
            'Gallery 3B',
            'Level 1 Gallery',
            'Floor 2 East',
            'Room 101',
            'Hall A',
            'Wing B',
            'Section C',
            'Zone D',
        ];

        foreach ($numberedGalleryNames as $name) {
            $response = $this->postJson(route('gallery.store'), [
                'internal_name' => $name,
            ]);

            $response->assertCreated(); // Should handle numbered gallery names
        }
    }

    public function test_handles_gallery_name_with_directional_references()
    {
        $directionalGalleryNames = [
            'North Gallery',
            'South Wing',
            'East Corridor',
            'West Hall',
            'Northeast Corner',
            'Southwest Section',
            'Upper Level',
            'Lower Level',
            'Ground Floor',
            'Basement Gallery',
        ];

        foreach ($directionalGalleryNames as $name) {
            $response = $this->postJson(route('gallery.store'), [
                'internal_name' => $name,
            ]);

            $response->assertCreated(); // Should handle directional gallery names
        }
    }

    public function test_handles_gallery_name_with_function_descriptions()
    {
        $functionalGalleryNames = [
            'Permanent Collection',
            'Rotating Exhibitions',
            'Interactive Displays',
            'Multimedia Presentations',
            'Hands-On Learning',
            'Quiet Contemplation',
            'Group Activities',
            'Private Viewing',
            'VIP Access',
            'Staff Only',
        ];

        foreach ($functionalGalleryNames as $name) {
            $response = $this->postJson(route('gallery.store'), [
                'internal_name' => $name,
            ]);

            $response->assertCreated(); // Should handle functional gallery names
        }
    }
}
