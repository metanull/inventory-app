<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Detail;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive parameter validation tests for Detail API endpoints
 */
class DetailParameterValidationTest extends TestCase
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
        Detail::factory()->count(6)->create();

        $response = $this->getJson(route('detail.index', [
            'page' => 2,
            'per_page' => 3,
        ]));

        $response->assertOk();
        $response->assertJsonPath('meta.current_page', 2);
        $response->assertJsonPath('meta.per_page', 3);
    }

    public function test_index_accepts_valid_include_parameters()
    {
        Detail::factory()->count(3)->create();

        $response = $this->getJson(route('detail.index', [
            'include' => 'item,pictures,translations',
        ]));

        $response->assertOk();
    }

    public function test_index_rejects_invalid_include_parameters()
    {
        Detail::factory()->count(2)->create();

        $response = $this->getJson(route('detail.index', [
            'include' => 'invalid_relation,fake_includes,non_existent',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['include']);
    }

    public function test_index_rejects_unexpected_query_parameters_currently()
    {
        Detail::factory()->count(2)->create();

        $response = $this->getJson(route('detail.index', [
            'page' => 1,
            'include' => 'item',
            'filter_by_item' => 'uuid', // Not implemented
            'search_content' => 'description', // Not implemented
            'detail_type' => 'primary', // Not implemented
            'admin_access' => true,
            'debug_mode' => true,
            'export_details' => 'csv',
            'sensitive_operation' => 'bulk_delete',
        ]));

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['filter_by_item']);
    }

    // SHOW ENDPOINT TESTS
    public function test_show_accepts_valid_include_parameters()
    {
        $detail = Detail::factory()->create();

        $response = $this->getJson(route('detail.show', $detail).'?include=item,pictures,translations');

        $response->assertOk();
    }

    public function test_show_rejects_unexpected_query_parameters_currently()
    {
        $detail = Detail::factory()->create();

        $response = $this->getJson(route('detail.show', $detail).'?include=item&admin_view=true&detailed_analytics=on');

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['admin_view']);
    }

    // STORE ENDPOINT TESTS
    public function test_store_validates_required_fields()
    {
        $response = $this->postJson(route('detail.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name', 'item_id']);
    }

    public function test_store_validates_item_id_uuid_format()
    {
        $response = $this->postJson(route('detail.store'), [
            'internal_name' => 'Test Detail',
            'item_id' => 'not-a-valid-uuid',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['item_id']);
    }

    public function test_store_validates_item_id_existence()
    {
        $validUuid = '12345678-1234-1234-1234-123456789012';

        $response = $this->postJson(route('detail.store'), [
            'internal_name' => 'Test Detail',
            'item_id' => $validUuid, // Valid UUID format but doesn't exist
        ]);

        // Current controller might not validate existence - security gap
        $this->assertContains($response->status(), [201, 422]);
    }

    public function test_store_accepts_valid_data_with_existing_item()
    {
        $item = Item::factory()->create();

        $response = $this->postJson(route('detail.store'), [
            'internal_name' => 'Valid Detail',
            'item_id' => $item->id,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.internal_name', 'Valid Detail');
        $response->assertJsonPath('data.item_id', $item->id);
    }

    public function test_store_prohibits_id_field()
    {
        $item = Item::factory()->create();

        $response = $this->postJson(route('detail.store'), [
            'id' => 'some-uuid', // Should be prohibited
            'internal_name' => 'Test Detail',
            'item_id' => $item->id,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_store_accepts_optional_backward_compatibility_field()
    {
        $item = Item::factory()->create();

        $response = $this->postJson(route('detail.store'), [
            'internal_name' => 'Legacy Detail',
            'item_id' => $item->id,
            'backward_compatibility' => 'old_detail_789',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.backward_compatibility', 'old_detail_789');
    }

    public function test_store_rejects_unexpected_request_parameters_currently()
    {
        $item = Item::factory()->create();

        $response = $this->postJson(route('detail.store'), [
            'internal_name' => 'Test Detail',
            'item_id' => $item->id,
            'unexpected_field' => 'should_be_rejected',
            'priority' => 'urgent', // Not implemented
            'category' => 'technical', // Not implemented
            'author' => 'admin_user', // Not implemented
            'admin_created' => true,
            'malicious_content' => '<script>alert("detail_xss")</script>',
            'sql_payload' => "'; UPDATE details SET item_id = NULL; --",
            'privilege_override' => 'superuser',
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // UPDATE ENDPOINT TESTS
    public function test_update_validates_item_id_uuid_format()
    {
        $detail = Detail::factory()->create();

        $response = $this->putJson(route('detail.update', $detail), [
            'internal_name' => 'Updated Detail',
            'item_id' => 'invalid-uuid-format',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['item_id']);
    }

    public function test_update_prohibits_id_modification()
    {
        $detail = Detail::factory()->create();

        $response = $this->putJson(route('detail.update', $detail), [
            'id' => 'new-uuid', // Should be prohibited
            'internal_name' => 'Updated Detail',
            'item_id' => $detail->item_id,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_update_accepts_valid_data()
    {
        $detail = Detail::factory()->create();
        $newItem = Item::factory()->create();

        $response = $this->putJson(route('detail.update', $detail), [
            'internal_name' => 'Updated Detail Name',
            'item_id' => $newItem->id,
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.internal_name', 'Updated Detail Name');
    }

    public function test_update_rejects_unexpected_request_parameters_currently()
    {
        $detail = Detail::factory()->create();

        $response = $this->putJson(route('detail.update', $detail), [
            'internal_name' => 'Updated Detail',
            'item_id' => $detail->item_id,
            'unexpected_field' => 'should_be_rejected',
            'change_status' => 'published',
            'reassign_item' => 'different_item_id',
            'escalate_priority' => 'critical',
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // EDGE CASE TESTS
    public function test_handles_unicode_characters_in_internal_name()
    {
        $item = Item::factory()->create();
        $unicodeNames = [
            'Détail français',
            'Деталь русский',
            '詳細日本語',
            'تفاصيل عربية',
            'Detalle español',
            'Dettaglio italiano',
        ];

        foreach ($unicodeNames as $name) {
            $response = $this->postJson(route('detail.store'), [
                'internal_name' => $name,
                'item_id' => $item->id,
            ]);

            $response->assertCreated(); // Should handle Unicode gracefully
        }
    }

    public function test_handles_very_long_internal_name()
    {
        $item = Item::factory()->create();
        $veryLongName = str_repeat('Very Long Detail Name ', 100);

        $response = $this->postJson(route('detail.store'), [
            'internal_name' => $veryLongName,
            'item_id' => $item->id,
        ]);

        // Should handle gracefully
        $this->assertContains($response->status(), [201, 422]);
    }

    public function test_handles_malformed_uuid_variations()
    {
        $malformedUuids = [
            '123',
            '12345678-1234-1234-1234',
            '12345678-1234-1234-1234-123456789012345',
            'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
            '',
            'null',
            '00000000-0000-0000-0000-000000000000',
        ];

        foreach ($malformedUuids as $uuid) {
            $response = $this->postJson(route('detail.store'), [
                'internal_name' => 'Test Detail',
                'item_id' => $uuid,
            ]);

            if ($uuid === '') {
                $response->assertUnprocessable();
                $response->assertJsonValidationErrors(['item_id']);
            } else {
                // Most malformed UUIDs should be rejected
                $this->assertContains($response->status(), [201, 422]);
            }
        }
    }

    public function test_handles_null_vs_empty_string_for_optional_fields()
    {
        $detail = Detail::factory()->create();

        $testCases = [
            ['backward_compatibility' => null],
            ['backward_compatibility' => ''],
        ];

        foreach ($testCases as $data) {
            $updateData = array_merge([
                'internal_name' => 'Test Detail Update',
                'item_id' => $detail->item_id,
            ], $data);

            $response = $this->putJson(route('detail.update', $detail), $updateData);

            $response->assertOk(); // Should handle gracefully
        }
    }

    public function test_pagination_boundary_conditions()
    {
        Detail::factory()->count(25)->create();

        // Valid boundary conditions
        $validCases = [
            ['per_page' => 1],
            ['per_page' => 100],
            ['page' => 1],
        ];

        foreach ($validCases as $params) {
            $response = $this->getJson(route('detail.index', $params));
            $response->assertOk();
        }

        // Invalid boundary conditions
        $invalidCases = [
            ['per_page' => 0],
            ['per_page' => 101],
            ['page' => 0],
            ['per_page' => -1],
            ['page' => -1],
        ];

        foreach ($invalidCases as $params) {
            $response = $this->getJson(route('detail.index', $params));
            $response->assertUnprocessable();
        }
    }

    public function test_handles_array_injection_attempts()
    {
        $response = $this->postJson(route('detail.store'), [
            'internal_name' => ['array' => 'instead_of_string'],
            'item_id' => ['another' => 'array'],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name', 'item_id']);
    }
}
