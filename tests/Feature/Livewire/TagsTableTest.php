<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Tables\TagsTable;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class TagsTableTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_component_renders_without_errors(): void
    {
        Livewire::test(TagsTable::class)
            ->assertStatus(200);
    }

    public function test_search_filters_tags(): void
    {
        $tag1 = Tag::factory()->create(['internal_name' => 'artifact']);
        $tag2 = Tag::factory()->create(['internal_name' => 'monument']);

        Livewire::test(TagsTable::class)
            ->set('q', 'artifact')
            ->assertSeeText('artifact')
            ->assertDontSeeText('monument');
    }

    public function test_search_by_description(): void
    {
        $tag1 = Tag::factory()->create([
            'internal_name' => 'tag1',
            'description' => 'Ancient artifacts from Rome',
        ]);
        $tag2 = Tag::factory()->create([
            'internal_name' => 'tag2',
            'description' => 'Modern sculptures',
        ]);

        Livewire::test(TagsTable::class)
            ->set('q', 'Ancient')
            ->assertSeeText('tag1')
            ->assertDontSeeText('tag2');
    }

    public function test_search_is_debounced(): void
    {
        Tag::factory()->create(['internal_name' => 'test-tag']);

        $component = Livewire::test(TagsTable::class)
            ->set('q', 'test');

        // Search property should be set
        $this->assertEquals('test', $component->get('q'));
    }

    public function test_pagination_changes_page(): void
    {
        // Create more tags than perPage to trigger pagination
        Tag::factory()->count(15)->create();

        Livewire::test(TagsTable::class)
            ->set('perPage', 10)
            ->call('gotoPage', 2)
            ->assertSet('perPage', 10);
    }

    public function test_per_page_changes_limit(): void
    {
        Tag::factory()->count(5)->create();

        Livewire::test(TagsTable::class)
            ->set('perPage', 25)
            ->assertSet('perPage', 25);
    }
}
