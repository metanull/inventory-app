<?php

namespace Tests\Unit\Livewire;

use App\Livewire\SearchableSelect;
use App\Models\Project;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests for SearchableSelect scope parameter (Story 2.2).
 *
 * Verifies that named Eloquent scopes are accepted, normalised,
 * applied to dynamic queries, and rejected when invalid.
 */
class SearchableSelectScopeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Walk the exception chain to find the root cause.
     * Livewire 3 wraps mount() exceptions in ViewException.
     */
    private function getRootException(\Throwable $e): \Throwable
    {
        while ($e->getPrevious() !== null) {
            $e = $e->getPrevious();
        }

        return $e;
    }

    public function test_valid_single_scope_filters_results(): void
    {
        $enabledProject = Project::factory()->withEnabled()->create(['internal_name' => 'Alpha']);
        $disabledProject = Project::factory()->create(['internal_name' => 'Beta', 'is_enabled' => false]);

        $component = Livewire::test(SearchableSelect::class, [
            'modelClass' => Project::class,
            'name' => 'project_id',
            'scopes' => 'isEnabled',
        ]);

        $options = $component->get('options');

        $this->assertTrue($options->contains('id', $enabledProject->id));
        $this->assertFalse($options->contains('id', $disabledProject->id));
    }

    public function test_multiple_scopes_are_composed(): void
    {
        $enabledOnly = Project::factory()->withEnabled()->create(['internal_name' => 'Enabled Only', 'is_launched' => false]);
        $launchedOnly = Project::factory()->create(['internal_name' => 'Launched Only', 'is_enabled' => false, 'is_launched' => true]);
        $both = Project::factory()->withEnabled()->create(['internal_name' => 'Both', 'is_launched' => true]);

        $component = Livewire::test(SearchableSelect::class, [
            'modelClass' => Project::class,
            'name' => 'project_id',
            'scopes' => ['isEnabled', 'isLaunched'],
        ]);

        $options = $component->get('options');

        $this->assertFalse($options->contains('id', $enabledOnly->id));
        $this->assertFalse($options->contains('id', $launchedOnly->id));
        $this->assertTrue($options->contains('id', $both->id));
    }

    public function test_scope_with_arguments_filters_results(): void
    {
        $keywordTag = Tag::factory()->keyword()->create(['internal_name' => 'Keyword Tag']);
        $materialTag = Tag::factory()->material()->create(['internal_name' => 'Material Tag']);

        $component = Livewire::test(SearchableSelect::class, [
            'modelClass' => Tag::class,
            'name' => 'tag_id',
            'scopes' => [['scope' => 'byCategory', 'args' => ['keyword']]],
        ]);

        $options = $component->get('options');

        $this->assertTrue($options->contains('id', $keywordTag->id));
        $this->assertFalse($options->contains('id', $materialTag->id));
    }

    public function test_scope_with_arguments_can_be_combined_with_filter_column(): void
    {
        $keywordTagA = Tag::factory()->keyword()->create(['internal_name' => 'Keyword A']);
        $keywordTagB = Tag::factory()->keyword()->create(['internal_name' => 'Keyword B']);

        $component = Livewire::test(SearchableSelect::class, [
            'modelClass' => Tag::class,
            'name' => 'tag_id',
            'scopes' => [['scope' => 'byCategory', 'args' => ['keyword']]],
            'filterColumn' => 'id',
            'filterOperator' => '!=',
            'filterValue' => $keywordTagA->id,
        ]);

        $options = $component->get('options');

        $this->assertFalse($options->contains('id', $keywordTagA->id));
        $this->assertTrue($options->contains('id', $keywordTagB->id));
    }

    public function test_invalid_scope_name_with_non_alphanumeric_characters_throws(): void
    {
        try {
            Livewire::test(SearchableSelect::class, [
                'modelClass' => Project::class,
                'name' => 'project_id',
                'scopes' => 'invalid-scope!',
            ]);
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (\Throwable $e) {
            $root = $e;
            while ($root->getPrevious() !== null) {
                $root = $root->getPrevious();
            }
            $this->assertInstanceOf(\InvalidArgumentException::class, $root);
            $this->assertStringContainsString('non-alphanumeric', $root->getMessage());
        }
    }

    public function test_unknown_scope_on_model_throws(): void
    {
        try {
            Livewire::test(SearchableSelect::class, [
                'modelClass' => Project::class,
                'name' => 'project_id',
                'scopes' => 'nonExistentScope',
            ]);
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (\Throwable $e) {
            $root = $e;
            while ($root->getPrevious() !== null) {
                $root = $root->getPrevious();
            }
            $this->assertInstanceOf(\InvalidArgumentException::class, $root);
            $this->assertStringContainsString('does not exist on model', $root->getMessage());
        }
    }

    public function test_scopes_accepted_as_string_shape(): void
    {
        Project::factory()->withEnabled()->create(['internal_name' => 'Enabled']);
        Project::factory()->create(['internal_name' => 'Disabled', 'is_enabled' => false]);

        Livewire::test(SearchableSelect::class, [
            'modelClass' => Project::class,
            'name' => 'project_id',
            'scopes' => 'isEnabled',
        ])->assertOk();
    }

    public function test_scopes_accepted_as_array_of_strings_shape(): void
    {
        Project::factory()->withEnabled()->create(['internal_name' => 'Enabled', 'is_launched' => true]);

        Livewire::test(SearchableSelect::class, [
            'modelClass' => Project::class,
            'name' => 'project_id',
            'scopes' => ['isEnabled', 'isLaunched'],
        ])->assertOk();
    }

    public function test_scopes_accepted_as_array_of_scope_arrays_shape(): void
    {
        Tag::factory()->keyword()->create(['internal_name' => 'Keyword']);

        Livewire::test(SearchableSelect::class, [
            'modelClass' => Tag::class,
            'name' => 'tag_id',
            'scopes' => [['scope' => 'byCategory', 'args' => ['keyword']]],
        ])->assertOk();
    }
}
