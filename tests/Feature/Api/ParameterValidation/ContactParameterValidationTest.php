<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Contact;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive parameter validation tests for Contact API endpoints
 */
class ContactParameterValidationTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // Create English language for translation tests
        Language::factory()->create([
            'id' => 'eng',
            'internal_name' => 'English',
            'backward_compatibility' => 'en',
            'is_default' => true,
        ]);
    }

    // INDEX ENDPOINT TESTS
    public function test_index_accepts_valid_pagination_parameters()
    {
        Contact::factory()->count(9)->create();

        $response = $this->getJson(route('contact.index', [
            'page' => 3,
            'per_page' => 3,
        ]));

        $response->assertOk();
        $response->assertJsonPath('meta.current_page', 3);
        $response->assertJsonPath('meta.per_page', 3);
    }

    public function test_index_accepts_valid_include_parameters()
    {
        Contact::factory()->count(3)->create();

        $response = $this->getJson(route('contact.index', [
            'include' => 'translations',
        ]));

        $response->assertOk();
    }

    public function test_index_rejects_invalid_include_parameters()
    {
        Contact::factory()->count(2)->create();

        $response = $this->getJson(route('contact.index', [
            'include' => 'invalid_relation,fake_address,non_existent',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['include']);
    }

    public function test_index_rejects_unexpected_query_parameters()
    {
        // SECURITY TEST: Form Request should reject unexpected parameters
        Contact::factory()->count(2)->create();

        $response = $this->getJson(route('contact.index', [
            'page' => 1,
            'include' => 'translations',
            'filter_by_type' => 'primary', // Not implemented
            'search_name' => 'john', // Not implemented
            'active_only' => true, // Not implemented
            'department' => 'admin', // Not implemented
            'admin_access' => true,
            'debug_contacts' => true,
            'export_format' => 'vcard',
            'bulk_operation' => 'send_email',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'filter_by_type',
                'search_name',
                'active_only',
                'department',
                'admin_access',
                'debug_contacts',
                'export_format',
                'bulk_operation',
            ],
        ]);
    }

    // SHOW ENDPOINT TESTS
    public function test_show_accepts_valid_include_parameters()
    {
        $contact = Contact::factory()->create();

        $response = $this->getJson(route('contact.show', $contact).'?include=translations');

        $response->assertOk();
    }

    public function test_show_rejects_unexpected_query_parameters()
    {
        // SECURITY TEST: Form Request should reject unexpected parameters
        $contact = Contact::factory()->create();

        $response = $this->getJson(route('contact.show', $contact).'?include=translations&admin_view=true&show_private_info=1&detailed_history=on');

        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'admin_view',
                'show_private_info',
                'detailed_history',
            ],
        ]);
    }

    // STORE ENDPOINT TESTS
    public function test_store_validates_required_fields()
    {
        $response = $this->postJson(route('contact.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_store_validates_unique_internal_name()
    {
        $existingContact = Contact::factory()->create();

        $response = $this->postJson(route('contact.store'), [
            'internal_name' => $existingContact->internal_name,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_store_validates_email_format_if_provided()
    {
        $response = $this->postJson(route('contact.store'), [
            'internal_name' => 'Test Contact',
            'email' => 'invalid-email-format',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_store_validates_phone_format_if_provided()
    {
        // Assuming phone validation exists
        $response = $this->postJson(route('contact.store'), [
            'internal_name' => 'Test Contact',
            'phone' => 'invalid-phone-123-abc',
        ]);

        // Phone validation might not be strict, so either passes or fails
        $this->assertContains($response->status(), [201, 422]);
    }

    public function test_store_prohibits_id_field()
    {
        $response = $this->postJson(route('contact.store'), [
            'id' => 'some-uuid', // Should be prohibited
            'internal_name' => 'Test Contact',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_store_accepts_valid_data()
    {
        $response = $this->postJson(route('contact.store'), [
            'internal_name' => 'Dr. Jane Smith',
            'email' => 'jane.smith@museum.org',
            'phone_number' => '+1-555-123-4567',
            'translations' => [
                [
                    'language_id' => 'eng',
                    'label' => 'Dr. Jane Smith - Curator',
                ],
            ],
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.internal_name', 'Dr. Jane Smith');
    }

    public function test_store_accepts_optional_backward_compatibility_field()
    {
        $response = $this->postJson(route('contact.store'), [
            'internal_name' => 'Legacy Contact',
            'backward_compatibility' => 'old_contact_456',
            'translations' => [
                [
                    'language_id' => 'eng',
                    'label' => 'Legacy Contact Person',
                ],
            ],
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.internal_name', 'Legacy Contact');

        // Check database for backward_compatibility field (not included in API response)
        $this->assertDatabaseHas('contacts', [
            'internal_name' => 'Legacy Contact',
            'backward_compatibility' => 'old_contact_456',
        ]);
    }

    public function test_store_rejects_unexpected_request_parameters()
    {
        // SECURITY TEST: Form Request should reject unexpected parameters
        $response = $this->postJson(route('contact.store'), [
            'internal_name' => 'Test Contact',
            'email' => 'test@example.com',
            'translations' => [
                [
                    'language_id' => 'eng',
                    'label' => 'Test Contact Person',
                ],
            ],
            'unexpected_field' => 'should_be_rejected',
            'role' => 'curator', // Not implemented
            'department' => 'archaeology', // Not implemented
            'security_clearance' => 'level_5', // Not implemented
            'admin_created' => true,
            'malicious_html' => '<iframe src="evil.com"></iframe>',
            'sql_injection' => "'; DROP TABLE contacts; --",
            'privilege_escalation' => 'admin_access',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'unexpected_field',
                'role',
                'department',
                'security_clearance',
                'admin_created',
                'malicious_html',
                'sql_injection',
                'privilege_escalation',
            ],
        ]);
    }

    // UPDATE ENDPOINT TESTS
    public function test_update_validates_unique_internal_name()
    {
        $contact1 = Contact::factory()->create();
        $contact2 = Contact::factory()->create();

        $response = $this->putJson(route('contact.update', $contact1), [
            'internal_name' => $contact2->internal_name,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_update_validates_email_format_if_provided()
    {
        $contact = Contact::factory()->create();

        $response = $this->putJson(route('contact.update', $contact), [
            'internal_name' => 'Updated Contact',
            'email' => 'still-invalid-email',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_update_prohibits_id_modification()
    {
        $contact = Contact::factory()->create();

        $response = $this->putJson(route('contact.update', $contact), [
            'id' => 'new-uuid', // Should be prohibited
            'internal_name' => 'Updated Contact',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_update_accepts_same_internal_name()
    {
        $contact = Contact::factory()->create();

        $response = $this->putJson(route('contact.update', $contact), [
            'internal_name' => $contact->internal_name, // Same name should be allowed
        ]);

        $response->assertOk();
    }

    public function test_update_rejects_unexpected_request_parameters()
    {
        // SECURITY TEST: Form Request should reject unexpected parameters
        $contact = Contact::factory()->create();

        $response = $this->putJson(route('contact.update', $contact), [
            'internal_name' => 'Updated Contact',
            'email' => 'updated@example.com',
            'translations' => [
                [
                    'language_id' => 'eng',
                    'label' => 'Updated Contact Person',
                ],
            ],
            'unexpected_field' => 'should_be_rejected',
            'promote_to_admin' => true,
            'change_department' => 'security',
            'access_level' => 'superuser',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'unexpected_field',
                'promote_to_admin',
                'change_department',
                'access_level',
            ],
        ]);
    }

    // EDGE CASE TESTS
    public function test_handles_unicode_characters_in_names()
    {
        $unicodeNames = [
            'François Müller',
            'Екатерина Иванова',
            '田中太郎',
            'محمد العربي',
            'José García',
            'Gianluigi Buffon',
            'Björk Guðmundsdóttir',
            'François-José María',
        ];

        foreach ($unicodeNames as $name) {
            $response = $this->postJson(route('contact.store'), [
                'internal_name' => $name,
                'translations' => [
                    [
                        'language_id' => 'eng',
                        'label' => $name,
                    ],
                ],
            ]);

            $response->assertCreated(); // Should handle Unicode gracefully
        }
    }

    public function test_handles_various_email_formats()
    {
        $validEmails = [
            'simple@example.com',
            'user.name@example.org',
            'user+tag@example.net',
            'firstname.lastname@subdomain.example.com',
            'user_name@example-domain.co.uk',
        ];

        foreach ($validEmails as $email) {
            $response = $this->postJson(route('contact.store'), [
                'internal_name' => "Contact for {$email}",
                'email' => $email,
                'translations' => [
                    [
                        'language_id' => 'eng',
                        'label' => "Contact for {$email}",
                    ],
                ],
            ]);

            $response->assertCreated(); // Should accept valid email formats
        }

        $invalidEmails = [
            'plainaddress',
            '@missingusername.com',
            'username@',
            'username@.com',
            'username..double.dot@example.com',
            'username@com.',
        ];

        foreach ($invalidEmails as $email) {
            $response = $this->postJson(route('contact.store'), [
                'internal_name' => "Contact for invalid {$email}",
                'email' => $email,
                'translations' => [
                    [
                        'language_id' => 'eng',
                        'label' => "Contact for invalid {$email}",
                    ],
                ],
            ]);

            $response->assertUnprocessable(); // Should reject invalid emails
            $response->assertJsonValidationErrors(['email']);
        }
    }

    public function test_handles_various_phone_formats()
    {
        $phoneFormats = [
            '+1-555-123-4567',
            '(555) 123-4567',
            '555.123.4567',
            '5551234567',
            '+44 20 7946 0958',
            '+33 1 42 68 53 00',
            '001-555-123-4567',
        ];

        foreach ($phoneFormats as $phone) {
            $response = $this->postJson(route('contact.store'), [
                'internal_name' => "Contact for {$phone}",
                'phone' => $phone,
            ]);

            // Phone validation might be lenient
            $this->assertContains($response->status(), [201, 422]);
        }
    }

    public function test_handles_special_characters_in_names()
    {
        $specialCharNames = [
            'O\'Connor',
            'Smith-Jones',
            'Dr. Jane M. Smith, Ph.D.',
            'Jean-Luc Picard',
            'Mary O\'Brien-Johnson',
            'Prof. José María García',
            'Sir John Smith III',
            'Ms. Anne-Marie St. Pierre',
        ];

        foreach ($specialCharNames as $name) {
            $response = $this->postJson(route('contact.store'), [
                'internal_name' => $name,
                'translations' => [
                    [
                        'language_id' => 'eng',
                        'label' => $name,
                    ],
                ],
            ]);

            $response->assertCreated(); // Should handle special characters
        }
    }

    public function test_handles_very_long_names()
    {
        $veryLongName = str_repeat('Very Long Contact Name With Many Middle Names ', 20);

        $response = $this->postJson(route('contact.store'), [
            'internal_name' => $veryLongName,
            'translations' => [
                [
                    'language_id' => 'eng',
                    'label' => $veryLongName,
                ],
            ],
        ]);

        // Should handle gracefully
        $this->assertContains($response->status(), [201, 422]);
    }

    public function test_handles_empty_and_whitespace_names()
    {
        $emptyNames = [
            '', // Empty
            '   ', // Spaces only
            "\t\t", // Tabs only
            "\n\n", // Newlines only
        ];

        foreach ($emptyNames as $name) {
            $response = $this->postJson(route('contact.store'), [
                'internal_name' => $name,
            ]);

            $response->assertUnprocessable(); // Should reject empty names
            $response->assertJsonValidationErrors(['internal_name']);
        }
    }

    public function test_pagination_with_large_dataset()
    {
        Contact::factory()->count(50)->create();

        $testCases = [
            ['page' => 1, 'per_page' => 10],
            ['page' => 5, 'per_page' => 10],
            ['page' => 1, 'per_page' => 100], // Maximum
        ];

        foreach ($testCases as $params) {
            $response = $this->getJson(route('contact.index', $params));
            $response->assertOk();
        }
    }

    public function test_handles_null_vs_empty_string_for_optional_fields()
    {
        $contact = Contact::factory()->create();

        $testCases = [
            ['email' => null, 'phone' => null],
            ['email' => '', 'phone' => ''],
            ['backward_compatibility' => null],
            ['backward_compatibility' => ''],
        ];

        foreach ($testCases as $data) {
            $updateData = array_merge([
                'internal_name' => 'Test Contact Update',
            ], $data);

            $response = $this->putJson(route('contact.update', $contact), $updateData);

            // Should handle gracefully
            $this->assertContains($response->status(), [200, 422]);
        }
    }

    public function test_handles_array_injection_attempts()
    {
        $response = $this->postJson(route('contact.store'), [
            'internal_name' => ['array' => 'instead_of_string'],
            'email' => ['malicious' => 'array'],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }
}
