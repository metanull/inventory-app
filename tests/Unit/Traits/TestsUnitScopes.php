<?php

namespace Tests\Unit\Traits;

/**
 * Trait for testing model scopes
 */
trait TestsUnitScopes
{
    abstract protected function getModelClass(): string;

    public function test_scope_returns_query_builder(): void
    {
        if (! method_exists($this, 'getScopes')) {
            $this->markTestSkipped('No scopes defined');
        }

        $modelClass = $this->getModelClass();

        foreach ($this->getScopes() as $scope => $params) {
            $query = $modelClass::$scope(...$params);
            $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $query);
        }
    }

    public function test_scope_filters_correctly(): void
    {
        if (! method_exists($this, 'getScopeTests')) {
            $this->markTestSkipped('No scope tests defined');
        }

        foreach ($this->getScopeTests() as $test) {
            $this->runScopeTest($test);
        }
    }

    protected function runScopeTest(array $test): void
    {
        $modelClass = $this->getModelClass();
        $scope = $test['scope'];
        $params = $test['params'] ?? [];
        $setup = $test['setup'] ?? null;
        $expectedCount = $test['expectedCount'];

        if ($setup) {
            $setup();
        }

        $results = $modelClass::$scope(...$params)->get();
        $this->assertCount($expectedCount, $results);
    }
}
