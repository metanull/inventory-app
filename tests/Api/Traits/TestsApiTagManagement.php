<?php

namespace Tests\Api\Traits;

use App\Models\Tag;

/**
 * Taggable Resource Test Trait
 *
 * Provides tests for resources that can be tagged.
 * Tests tag attachment, detachment, and syncing operations.
 */
trait TestsApiTagManagement
{
    abstract protected function getResourceName(): string;

    abstract protected function getModelClass(): string;

    public function test_can_attach_single_tag(): void
    {
        $modelClass = $this->getModelClass();
        $resource = $modelClass::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->postJson(route($this->getResourceName().'.attachTag', $resource), [
            'tag_id' => $tag->id,
        ]);

        $response->assertOk();
        $this->assertTrue($resource->fresh()->tags->contains($tag));
    }

    public function test_can_detach_single_tag(): void
    {
        $modelClass = $this->getModelClass();
        $resource = $modelClass::factory()->create();
        $tag = Tag::factory()->create();
        $resource->tags()->attach($tag);

        $response = $this->deleteJson(route($this->getResourceName().'.detachTag', $resource), [
            'tag_id' => $tag->id,
        ]);

        $response->assertOk();
        $this->assertFalse($resource->fresh()->tags->contains($tag));
    }

    public function test_can_detach_multiple_tags(): void
    {
        $modelClass = $this->getModelClass();
        $resource = $modelClass::factory()->create();
        $tags = Tag::factory()->count(3)->create();
        $resource->tags()->attach($tags);

        $response = $this->deleteJson(route($this->getResourceName().'.detachTags', $resource), [
            'tag_ids' => $tags->take(2)->pluck('id')->toArray(),
        ]);

        $response->assertOk();
        $this->assertCount(1, $resource->fresh()->tags);
    }

    public function test_can_update_tags(): void
    {
        $modelClass = $this->getModelClass();
        $resource = $modelClass::factory()->create();
        $existingTags = Tag::factory()->count(2)->create();
        $resource->tags()->attach($existingTags);

        $newTags = Tag::factory()->count(2)->create();

        $response = $this->patchJson(route($this->getResourceName().'.updateTags', $resource), [
            'attach' => $newTags->pluck('id')->toArray(),
            'detach' => $existingTags->take(1)->pluck('id')->toArray(),
        ]);

        $response->assertOk();

        $freshTags = $resource->fresh()->tags;
        $this->assertCount(3, $freshTags); // 1 existing + 2 new
        $this->assertFalse($freshTags->contains($existingTags->first()));
    }

    public function test_attach_tag_prevents_duplicates(): void
    {
        $modelClass = $this->getModelClass();
        $resource = $modelClass::factory()->create();
        $tag = Tag::factory()->create();
        $resource->tags()->attach($tag);

        $response = $this->postJson(route($this->getResourceName().'.attachTag', $resource), [
            'tag_id' => $tag->id,
        ]);

        $response->assertOk();
        $this->assertCount(1, $resource->fresh()->tags);
    }

    public function test_detach_non_attached_tag_succeeds(): void
    {
        $modelClass = $this->getModelClass();
        $resource = $modelClass::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->deleteJson(route($this->getResourceName().'.detachTag', $resource), [
            'tag_id' => $tag->id,
        ]);

        $response->assertOk();
    }

    public function test_tag_operations_validate_tag_exists(): void
    {
        $modelClass = $this->getModelClass();
        $resource = $modelClass::factory()->create();

        $response = $this->postJson(route($this->getResourceName().'.attachTag', $resource), [
            'tag_id' => 'nonexistent-uuid',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['tag_id']);
    }
}
