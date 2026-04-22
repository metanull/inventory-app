<?php

namespace Tests\Unit\Livewire\Support;

use App\Livewire\Support\OptionsLookup;
use App\Models\Item;
use App\Models\Project;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Minimal stub for testing OptionsLookup methods in isolation.
 *
 * Exposes the protected normalizeScopes method so the test can call it directly.
 */
class OptionsLookupStub
{
    use OptionsLookup;

    public string $search = '';

    public $staticOptions = null;

    public ?string $modelClass = null;

    public string $displayField = 'internal_name';

    public ?string $filterColumn = null;

    public ?string $filterOperator = '!=';

    public $filterValue = null;

    public $scopes = null;

    public int $perPage = 50;

    public function exposeNormalizeScopes(mixed $scopes, ?string $modelClass): array
    {
        return $this->normalizeScopes($scopes, $modelClass);
    }
}

/**
 * Unit tests for the OptionsLookup trait (Story 3.1).
 *
 * Uses a lightweight stub class to exercise the trait in complete isolation
 * from Livewire's component infrastructure.
 */
class OptionsLookupTest extends TestCase
{
    use RefreshDatabase;

    private function makeStub(array $properties = []): OptionsLookupStub
    {
        $stub = new OptionsLookupStub;
        foreach ($properties as $key => $value) {
            $stub->$key = $value;
        }

        return $stub;
    }

    // ── resolveStaticOptions ──────────────────────────────────────────────────

    public function test_resolve_static_options_returns_all_when_search_is_empty(): void
    {
        $stub = $this->makeStub([
            'staticOptions' => [
                ['id' => '1', 'internal_name' => 'Alpha'],
                ['id' => '2', 'internal_name' => 'Beta'],
            ],
        ]);

        $this->assertCount(2, $stub->resolveStaticOptions());
    }

    public function test_resolve_static_options_filters_by_search(): void
    {
        $stub = $this->makeStub([
            'staticOptions' => [
                ['id' => '1', 'internal_name' => 'Alpha'],
                ['id' => '2', 'internal_name' => 'Beta'],
            ],
            'search' => 'alp',
        ]);

        $result = $stub->resolveStaticOptions();

        $this->assertCount(1, $result);
        $this->assertEquals('Alpha', $result->first()['internal_name']);
    }

    public function test_resolve_static_options_is_case_insensitive(): void
    {
        $stub = $this->makeStub([
            'staticOptions' => [['id' => '1', 'internal_name' => 'Alpha']],
            'search' => 'ALPHA',
        ]);

        $this->assertCount(1, $stub->resolveStaticOptions());
    }

    // ── resolveOptionsQuery ───────────────────────────────────────────────────

    public function test_resolve_options_query_returns_builder(): void
    {
        $stub = $this->makeStub(['modelClass' => Item::class]);

        $this->assertInstanceOf(Builder::class, $stub->resolveOptionsQuery());
    }

    public function test_resolve_options_query_respects_per_page_limit(): void
    {
        Item::factory()->count(10)->create();

        $stub = $this->makeStub(['modelClass' => Item::class, 'perPage' => 3]);

        $this->assertCount(3, $stub->resolveOptionsQuery()->get());
    }

    // ── applySearch ───────────────────────────────────────────────────────────

    public function test_apply_search_orders_prefix_matches_before_infix(): void
    {
        $prefixItem = Item::factory()->create(['internal_name' => 'Alpha Item']);
        $infixItem = Item::factory()->create(['internal_name' => 'Not Alpha']);

        $stub = $this->makeStub(['modelClass' => Item::class, 'perPage' => 50]);

        $results = $stub->applySearch(Item::query(), 'Alpha')->get();

        $this->assertEquals($prefixItem->id, $results->first()->id);
        $this->assertEquals($infixItem->id, $results->last()->id);
    }

    public function test_apply_search_with_empty_string_returns_all_ordered(): void
    {
        Item::factory()->count(3)->create();

        $stub = $this->makeStub(['modelClass' => Item::class, 'perPage' => 50]);

        $results = $stub->applySearch(Item::query(), '')->get();

        $this->assertCount(3, $results);
    }

    // ── applyScopes ───────────────────────────────────────────────────────────

    public function test_apply_scopes_filters_with_named_scope(): void
    {
        $enabled = Project::factory()->withEnabled()->create(['internal_name' => 'Enabled']);
        $disabled = Project::factory()->create(['internal_name' => 'Disabled', 'is_enabled' => false]);

        $stub = $this->makeStub([
            'scopes' => [['scope' => 'isEnabled', 'args' => []]],
        ]);

        $results = $stub->applyScopes(Project::query())->get();

        $this->assertTrue($results->contains('id', $enabled->id));
        $this->assertFalse($results->contains('id', $disabled->id));
    }

    public function test_apply_scopes_with_null_scopes_returns_unfiltered(): void
    {
        Project::factory()->count(3)->create();

        $stub = $this->makeStub(['scopes' => null]);

        $results = $stub->applyScopes(Project::query())->get();

        $this->assertCount(3, $results);
    }

    // ── normalizeScopes ───────────────────────────────────────────────────────

    public function test_normalize_scopes_accepts_single_string(): void
    {
        $stub = $this->makeStub();

        $result = $stub->exposeNormalizeScopes('isEnabled', Project::class);

        $this->assertCount(1, $result);
        $this->assertEquals('isEnabled', $result[0]['scope']);
        $this->assertEquals([], $result[0]['args']);
    }

    public function test_normalize_scopes_accepts_array_of_strings(): void
    {
        $stub = $this->makeStub();

        $result = $stub->exposeNormalizeScopes(['isEnabled', 'isLaunched'], Project::class);

        $this->assertCount(2, $result);
        $this->assertEquals('isEnabled', $result[0]['scope']);
        $this->assertEquals('isLaunched', $result[1]['scope']);
    }

    public function test_normalize_scopes_accepts_fully_specified_array_with_args(): void
    {
        $stub = $this->makeStub();

        $result = $stub->exposeNormalizeScopes(
            [['scope' => 'byCategory', 'args' => ['keyword']]],
            Tag::class
        );

        $this->assertCount(1, $result);
        $this->assertEquals('byCategory', $result[0]['scope']);
        $this->assertEquals(['keyword'], $result[0]['args']);
    }

    public function test_normalize_scopes_throws_for_non_alphanumeric_name(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/non-alphanumeric/');

        $stub = $this->makeStub();
        $stub->exposeNormalizeScopes('invalid-scope', null);
    }

    public function test_normalize_scopes_throws_for_unknown_scope_on_model(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/does not exist on model/');

        $stub = $this->makeStub();
        $stub->exposeNormalizeScopes('nonExistentScope', Project::class);
    }

    public function test_normalize_scopes_throws_for_non_array_non_string_input(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $stub = $this->makeStub();
        $stub->exposeNormalizeScopes(123, null);
    }

    public function test_normalize_scopes_throws_for_object_scope_arg(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Scope arguments must be/');

        $stub = $this->makeStub();
        $stub->exposeNormalizeScopes(
            [['scope' => 'byCategory', 'args' => [new \stdClass]]],
            Tag::class
        );
    }

    public function test_normalize_scopes_throws_for_malformed_scope_entry(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $stub = $this->makeStub();
        // Array entry without 'scope' key
        $stub->exposeNormalizeScopes([['invalid_key' => 'isEnabled']], null);
    }
}
