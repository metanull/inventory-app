<?php

namespace Tests\Web\Components;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

class ListComponentsTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_parent_context_header_renders_breadcrumbs_parent_link_and_back_button(): void
    {
        $view = $this->blade(
            '<x-list.parent-context-header :breadcrumbs="$breadcrumbs" title="Item Translations" description="Scoped listing." parent-label="Item" parent-value="Temple of Bel" parent-url="/web/items/1" back-url="/web/items" back-label="Back to items" />',
            [
                'breadcrumbs' => [
                    ['label' => 'Items', 'url' => '/web/items'],
                    ['label' => 'Temple of Bel', 'url' => '/web/items/1'],
                    ['label' => 'Translations'],
                ],
            ]
        );

        $view->assertSee('Item Translations');
        $view->assertSee('Scoped listing.');
        $view->assertSee('Temple of Bel');
        $view->assertSee('Back to items');
        $view->assertSee('/web/items/1', false);
    }

    public function test_code_component_renders_code_values_and_empty_fallback(): void
    {
        $codeView = $this->blade('<x-list.code value="fra" />');
        $emptyView = $this->blade('<x-list.code :value="null" />');

        $codeView->assertSee('FRA');
        $emptyView->assertSee('-');
    }

    public function test_index_page_layout_renders_parent_context_before_page_slot(): void
    {
        $view = $this->blade(
            '<x-layout.index-page entity="items" :parent-context="$parentContext"><div>Filter Bar Placeholder</div></x-layout.index-page>',
            [
                'parentContext' => [
                    'breadcrumbs' => [
                        ['label' => 'Items', 'url' => '/web/items'],
                        ['label' => 'Temple of Bel'],
                    ],
                    'title' => 'Item Translations',
                    'parent_label' => 'Item',
                    'parent_value' => 'Temple of Bel',
                    'back_url' => '/web/items',
                ],
            ]
        );

        $view->assertSeeInOrder(['Item Translations', 'Filter Bar Placeholder']);
        $view->assertSee('Temple of Bel');
        $view->assertSee('/web/items', false);
    }
}
