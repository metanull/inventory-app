<?php

namespace Tests\Unit\Livewire;

use App\Livewire\SearchableSelect;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests for SearchableSelect staticOptions ceiling guard (Story 2.1).
 *
 * Verifies that mount() throws InvalidArgumentException when staticOptions
 * exceeds the configured maximum, and that the limit is configurable.
 */
class SearchableSelectStaticOptionsCeilingTest extends TestCase
{
    use RefreshDatabase;

    private function makeOptions(int $count): array
    {
        return array_map(
            fn ($i) => ['id' => (string) $i, 'internal_name' => "Option {$i}"],
            range(1, $count)
        );
    }

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

    public function test_component_mounts_when_static_options_are_within_ceiling(): void
    {
        $options = $this->makeOptions(3);

        Livewire::test(SearchableSelect::class, [
            'staticOptions' => $options,
            'name' => 'test_field',
        ])->assertOk();
    }

    public function test_component_throws_when_static_options_exceed_ceiling(): void
    {
        $options = $this->makeOptions(51);

        try {
            Livewire::test(SearchableSelect::class, [
                'staticOptions' => $options,
                'name' => 'test_field',
            ]);
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (\Throwable $e) {
            $root = $this->getRootException($e);
            $this->assertInstanceOf(\InvalidArgumentException::class, $root);
            $this->assertMatchesRegularExpression(
                '/SearchableSelect received \d+ staticOptions but the configured maximum is \d+/',
                $root->getMessage()
            );
        }
    }

    public function test_exception_message_includes_count_max_and_guidance(): void
    {
        config()->set('interface.searchable_select.static_options_max', 10);

        $options = $this->makeOptions(15);

        try {
            Livewire::test(SearchableSelect::class, [
                'staticOptions' => $options,
                'name' => 'test_field',
            ]);
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (\Throwable $e) {
            $root = $this->getRootException($e);
            $this->assertInstanceOf(\InvalidArgumentException::class, $root);
            $this->assertStringContainsString('SearchableSelect received 15 staticOptions', $root->getMessage());
            $this->assertStringContainsString('the configured maximum is 10', $root->getMessage());
            $this->assertStringContainsString('dynamic mode', $root->getMessage());
        }
    }

    public function test_config_override_raises_the_ceiling(): void
    {
        config()->set('interface.searchable_select.static_options_max', 100);

        $options = $this->makeOptions(60);

        Livewire::test(SearchableSelect::class, [
            'staticOptions' => $options,
            'name' => 'test_field',
        ])->assertOk();
    }

    public function test_config_override_lowers_the_ceiling(): void
    {
        config()->set('interface.searchable_select.static_options_max', 2);

        $options = $this->makeOptions(3);

        try {
            Livewire::test(SearchableSelect::class, [
                'staticOptions' => $options,
                'name' => 'test_field',
            ]);
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (\Throwable $e) {
            $root = $this->getRootException($e);
            $this->assertInstanceOf(\InvalidArgumentException::class, $root);
        }
    }

    public function test_ceiling_is_not_applied_in_dynamic_mode(): void
    {
        config()->set('interface.searchable_select.static_options_max', 2);

        Livewire::test(SearchableSelect::class, [
            'modelClass' => Item::class,
            'name' => 'item_id',
        ])->assertOk();
    }
}
