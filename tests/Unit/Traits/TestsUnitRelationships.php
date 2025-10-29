<?php

namespace Tests\Unit\Traits;

/**
 * Trait for testing relationships between models
 */
trait TestsUnitRelationships
{
    public function test_belongs_to_relationship(): void
    {
        if (! method_exists($this, 'getBelongsToRelationships')) {
            $this->markTestSkipped('No belongs-to relationships defined');
        }

        $modelClass = $this->getModelClass();
        $model = $modelClass::factory()->create();

        foreach ($this->getBelongsToRelationships() as $relation => $relatedClass) {
            $this->assertInstanceOf($relatedClass, $model->$relation);
        }
    }

    public function test_has_many_relationship(): void
    {
        if (! method_exists($this, 'getHasManyRelationships')) {
            $this->markTestSkipped('No has-many relationships defined');
        }

        $modelClass = $this->getModelClass();
        $model = $modelClass::factory()->create();

        foreach ($this->getHasManyRelationships() as $relation => $relatedClass) {
            $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $model->$relation);
        }
    }

    public function test_many_to_many_relationship(): void
    {
        if (! method_exists($this, 'getManyToManyRelationships')) {
            $this->markTestSkipped('No many-to-many relationships defined');
        }

        $modelClass = $this->getModelClass();
        $model = $modelClass::factory()->create();

        foreach ($this->getManyToManyRelationships() as $relation => $relatedClass) {
            $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $model->$relation);

            // Test attach/detach
            $related = $relatedClass::factory()->create();
            $model->$relation()->attach($related);
            $this->assertTrue($model->fresh()->$relation->contains($related));

            $model->$relation()->detach($related);
            $this->assertFalse($model->fresh()->$relation->contains($related));
        }
    }

    abstract protected function getModelClass(): string;
}
