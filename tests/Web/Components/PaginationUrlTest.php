<?php

namespace Tests\Web\Components;

use App\Livewire\Tables\ContextsTable;
use App\Models\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

class PaginationUrlTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_pagination_links_use_absolute_paths(): void
    {
        Context::factory()->count(15)->create();

        $html = Livewire::test(ContextsTable::class)
            ->assertOk()
            ->html();

        preg_match_all('/href="([^"]*page=[^"]*)"/', $html, $matches);

        $this->assertNotEmpty($matches[1], 'Expected pagination links to be present');

        foreach ($matches[1] as $url) {
            if ($url === '#') {
                continue;
            }
            $this->assertThat(
                $url,
                $this->logicalOr(
                    $this->stringStartsWith('/'),
                    $this->stringStartsWith('http'),
                ),
                "Pagination URL should be absolute, got: {$url}",
            );
        }
    }
}
