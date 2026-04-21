<?php

namespace Tests\Web\Pages;

use App\Models\Language;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebCrud;

class TagTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebCrud;

    protected function getRouteName(): string
    {
        return 'tags';
    }

    protected function getModelClass(): string
    {
        return Tag::class;
    }

    protected function getFormData(): array
    {
        return Tag::factory()->make()->toArray();
    }

    public function test_show_page_passes_item_count_from_controller(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->get(route('tags.show', $tag));

        $response->assertOk()
            ->assertViewHas('itemCount', 0);
    }

    public function test_edit_page_passes_languages_from_controller(): void
    {
        Language::factory()->count(3)->create();
        $tag = Tag::factory()->create();

        $response = $this->get(route('tags.edit', $tag));

        $response->assertOk()
            ->assertViewHas('languages');
    }

    public function test_create_page_passes_languages_from_controller(): void
    {
        Language::factory()->count(3)->create();

        $response = $this->get(route('tags.create'));

        $response->assertOk()
            ->assertViewHas('languages');
    }
}
