<?php

namespace Tests\Unit\Requests\Web;

use App\Enums\ItemType;
use App\Http\Requests\Web\IndexCollectionRequest;
use App\Http\Requests\Web\IndexItemRequest;
use App\Http\Requests\Web\IndexPartnerRequest;
use App\Models\Collection;
use App\Models\Context;
use App\Models\Country;
use App\Models\Item;
use App\Models\Language;
use App\Models\Partner;
use App\Models\Project;
use App\Models\Tag;
use App\Support\Web\Lists\CollectionListDefinition;
use App\Support\Web\Lists\ItemListDefinition;
use App\Support\Web\Lists\ListInputNormalizer;
use App\Support\Web\Lists\PartnerListDefinition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IndexListRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_item_contract_documents_expected_query_parameters_and_sorts(): void
    {
        $definition = new ItemListDefinition;

        $this->assertSame(
            ['q', 'sort', 'direction', 'page', 'per_page', 'partner_id', 'collection_id', 'project_id', 'country_id', 'parent_id', 'type', 'hierarchy', 'tags'],
            $definition->queryParameters(),
        );

        $this->assertSame(['internal_name', 'created_at', 'updated_at'], array_keys($definition->sorts()));
    }

    public function test_partner_and_collection_contracts_document_expected_filters_and_eager_loads(): void
    {
        $partnerDefinition = new PartnerListDefinition;
        $collectionDefinition = new CollectionListDefinition;

        $this->assertSame([], $partnerDefinition->filterParameters());
        $this->assertSame(['country'], $partnerDefinition->eagerLoads());

        $this->assertSame(['context_id', 'language_id', 'parent_id', 'hierarchy'], $collectionDefinition->filterParameters());
        $this->assertSame(['context', 'language'], $collectionDefinition->eagerLoads());
    }

    public function test_common_query_values_are_normalized_against_the_canonical_contract(): void
    {
        $normalized = app(ListInputNormalizer::class)->normalize([
            'q' => '  Needle  ',
            'sort' => 'not-allowed',
            'direction' => 'SIDEWAYS',
            'page' => 0,
            'per_page' => 13,
        ], new ItemListDefinition);

        $this->assertSame('Needle', $normalized['q']);
        $this->assertSame('internal_name', $normalized['sort']);
        $this->assertSame('asc', $normalized['direction']);
        $this->assertSame(1, $normalized['page']);
        $this->assertSame((int) config('interface.pagination.default_per_page'), $normalized['per_page']);
    }

    public function test_item_specific_filters_are_normalized_before_validation(): void
    {
        $normalized = app(ListInputNormalizer::class)->normalize([
            'type' => ' '.ItemType::OBJECT->value.' ',
            'hierarchy' => 'yes',
            'tags' => ['  first-tag  ', '', 'first-tag', 'second-tag '],
        ], new ItemListDefinition);

        $this->assertSame(ItemType::OBJECT->value, $normalized['type']);
        $this->assertTrue($normalized['hierarchy']);
        $this->assertSame(['first-tag', 'second-tag'], $normalized['tags']);
    }

    public function test_item_index_request_accepts_documented_filters_and_sort_keys(): void
    {
        $parent = Item::factory()->create();
        $partner = Partner::factory()->create();
        $project = Project::factory()->create();
        $collection = Collection::factory()->create();
        $country = Country::factory()->create();
        $tag = Tag::factory()->create();

        $request = new IndexItemRequest;
        $validator = Validator::make([
            'q' => 'needle',
            'sort' => 'updated_at',
            'direction' => 'desc',
            'page' => 2,
            'per_page' => 20,
            'partner_id' => $partner->id,
            'collection_id' => $collection->id,
            'project_id' => $project->id,
            'country_id' => $country->id,
            'parent_id' => $parent->id,
            'type' => ItemType::MONUMENT->value,
            'hierarchy' => true,
            'tags' => [$tag->id],
        ], $request->rules());

        $validator->validate();

        $this->assertFalse($validator->errors()->any());
    }

    public function test_partner_and_collection_index_requests_accept_documented_contracts(): void
    {
        $context = Context::factory()->create();
        $language = Language::factory()->create();
        $parentCollection = Collection::factory()->create([
            'context_id' => $context->id,
            'language_id' => $language->id,
        ]);

        $partnerRequest = new IndexPartnerRequest;
        $partnerValidator = Validator::make([
            'q' => 'museum',
            'page' => 2,
            'sort' => 'created_at',
            'direction' => 'desc',
            'per_page' => 20,
        ], $partnerRequest->rules());
        $partnerValidator->validate();

        $collectionRequest = new IndexCollectionRequest;
        $collectionValidator = Validator::make([
            'context_id' => $context->id,
            'language_id' => $language->id,
            'parent_id' => $parentCollection->id,
            'hierarchy' => true,
            'sort' => 'display_order',
            'direction' => 'asc',
            'per_page' => 50,
        ], $collectionRequest->rules());
        $collectionValidator->validate();

        $this->assertFalse($partnerValidator->errors()->any());
        $this->assertFalse($collectionValidator->errors()->any());
    }
}
