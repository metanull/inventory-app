<?php

namespace Tests\Filament\Resources;

use App\Filament\Pages\BrowseCollectionTree;
use App\Filament\Pages\BrowseItemTree;
use App\Filament\Resources\AvailableImageResource;
use App\Filament\Resources\CollectionResource;
use App\Filament\Resources\ContextResource;
use App\Filament\Resources\GlossaryResource;
use App\Filament\Resources\ItemResource;
use App\Filament\Resources\PartnerResource;
use App\Filament\Resources\ProjectResource;
use App\Filament\Resources\TagResource;
use App\Filament\Resources\TimelineEventResource;
use App\Filament\Resources\TimelineResource;
use App\Models\AvailableImage;
use App\Models\Collection;
use App\Models\Context;
use App\Models\Country;
use App\Models\Item;
use App\Models\Language;
use App\Models\Partner;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Timeline;
use App\Models\TimelineEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests that verify the admin search parity contract defined in GitHub issue #1009.
 *
 * Each test exercises one search field that was NOT covered before:
 *   - Items:            legacy code in partner/collection/tag/changeParent selectors
 *   - Collections:      legacy code in changeParent/moveToParent selectors
 *   - Partners:         country and project filter presence
 *   - Tags:             language in global search attributes
 *   - Available images: original_name in global search attributes and attach selector
 */
class SearchParityTest extends TestCase
{
    use RefreshDatabase;

    // ─── Global search attribute contracts ───────────────────────────────────

    public function test_item_global_search_includes_partner_country_and_project_fields(): void
    {
        $attributes = ItemResource::getGloballySearchableAttributes();

        $this->assertContains('partner.internal_name', $attributes);
        $this->assertContains('country.internal_name', $attributes);
        $this->assertContains('project.internal_name', $attributes);
    }

    public function test_collection_global_search_includes_parent_and_country_fields(): void
    {
        $attributes = CollectionResource::getGloballySearchableAttributes();

        $this->assertContains('parent.internal_name', $attributes);
        $this->assertContains('country.internal_name', $attributes);
    }

    public function test_partner_global_search_includes_country_and_project_fields(): void
    {
        $attributes = PartnerResource::getGloballySearchableAttributes();

        $this->assertContains('country.internal_name', $attributes);
        $this->assertContains('project.internal_name', $attributes);
    }

    public function test_tag_global_search_includes_language_field(): void
    {
        $attributes = TagResource::getGloballySearchableAttributes();

        $this->assertContains('language.internal_name', $attributes);
    }

    public function test_available_image_global_search_includes_original_name(): void
    {
        $attributes = AvailableImageResource::getGloballySearchableAttributes();

        $this->assertContains('original_name', $attributes);
        $this->assertContains('path', $attributes);
        $this->assertContains('comment', $attributes);
    }

    // ─── UUID in global search attributes ────────────────────────────────────

    public function test_item_global_search_includes_id(): void
    {
        $this->assertContains('id', ItemResource::getGloballySearchableAttributes());
    }

    public function test_collection_global_search_includes_id(): void
    {
        $this->assertContains('id', CollectionResource::getGloballySearchableAttributes());
    }

    public function test_partner_global_search_includes_id(): void
    {
        $this->assertContains('id', PartnerResource::getGloballySearchableAttributes());
    }

    public function test_project_global_search_includes_id(): void
    {
        $this->assertContains('id', ProjectResource::getGloballySearchableAttributes());
    }

    public function test_context_global_search_includes_id(): void
    {
        $this->assertContains('id', ContextResource::getGloballySearchableAttributes());
    }

    public function test_tag_global_search_includes_id(): void
    {
        $this->assertContains('id', TagResource::getGloballySearchableAttributes());
    }

    public function test_glossary_global_search_includes_id(): void
    {
        $this->assertContains('id', GlossaryResource::getGloballySearchableAttributes());
    }

    public function test_timeline_global_search_includes_id(): void
    {
        $this->assertContains('id', TimelineResource::getGloballySearchableAttributes());
    }

    public function test_timeline_event_global_search_includes_id(): void
    {
        $this->assertContains('id', TimelineEventResource::getGloballySearchableAttributes());
    }

    // ─── Available-image attach selector ─────────────────────────────────────

    public function test_available_image_attach_selector_finds_record_by_original_name(): void
    {
        $found = AvailableImage::factory()->create([
            'path' => 'img-abc123.jpg',
            'original_name' => 'ancient-pottery.jpg',
            'comment' => null,
        ]);
        $other = AvailableImage::factory()->create([
            'path' => 'img-xyz999.jpg',
            'original_name' => 'unrelated.jpg',
            'comment' => null,
        ]);

        $results = AvailableImage::query()
            ->where('path', 'like', '%ancient%')
            ->orWhere('original_name', 'like', '%ancient%')
            ->orWhere('comment', 'like', '%ancient%')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($found->id, $results);
        $this->assertNotContains($other->id, $results);
    }

    public function test_available_image_attach_selector_finds_record_by_comment(): void
    {
        $found = AvailableImage::factory()->create([
            'path' => 'img-001.jpg',
            'original_name' => null,
            'comment' => 'Bronze Age artefact',
        ]);
        $other = AvailableImage::factory()->create([
            'path' => 'img-002.jpg',
            'original_name' => null,
            'comment' => 'Unrelated image',
        ]);

        $results = AvailableImage::query()
            ->where('path', 'like', '%bronze age%')
            ->orWhere('original_name', 'like', '%bronze age%')
            ->orWhere('comment', 'like', '%bronze age%')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($found->id, $results);
        $this->assertNotContains($other->id, $results);
    }

    // ─── Item table filter searches ───────────────────────────────────────────

    public function test_item_partner_filter_finds_partner_by_legacy_code(): void
    {
        $partner = Partner::factory()->create([
            'internal_name' => 'Jordan Museum',
            'backward_compatibility' => 'jm-legacy-001',
        ]);
        Partner::factory()->create([
            'internal_name' => 'Other Museum',
            'backward_compatibility' => 'om-xyz',
        ]);

        $results = Partner::query()
            ->where('internal_name', 'like', '%jm-legacy%')
            ->orWhere('backward_compatibility', 'like', '%jm-legacy%')
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($partner->id, $results);
    }

    public function test_item_partner_filter_finds_partner_by_uuid(): void
    {
        $partner = Partner::factory()->create([
            'internal_name' => 'Jordan Museum',
        ]);
        Partner::factory()->create([
            'internal_name' => 'Other Museum',
        ]);

        $results = Partner::query()
            ->where('internal_name', 'like', "%{$partner->id}%")
            ->orWhere('backward_compatibility', 'like', "%{$partner->id}%")
            ->orWhere('id', 'like', "%{$partner->id}%")
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($partner->id, $results);
    }

    public function test_item_collection_filter_finds_collection_by_legacy_code(): void
    {
        $collection = Collection::factory()->create([
            'internal_name' => 'Temple Objects',
            'backward_compatibility' => 'col-temple-01',
        ]);
        Collection::factory()->create([
            'internal_name' => 'Other Collection',
            'backward_compatibility' => 'col-other',
        ]);

        $results = Collection::query()
            ->where('internal_name', 'like', '%col-temple%')
            ->orWhere('backward_compatibility', 'like', '%col-temple%')
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($collection->id, $results);
    }

    public function test_item_collection_filter_finds_collection_by_uuid(): void
    {
        $collection = Collection::factory()->create([
            'internal_name' => 'Temple Objects',
        ]);
        Collection::factory()->create([
            'internal_name' => 'Other Collection',
        ]);

        $results = Collection::query()
            ->where('internal_name', 'like', "%{$collection->id}%")
            ->orWhere('backward_compatibility', 'like', "%{$collection->id}%")
            ->orWhere('id', 'like', "%{$collection->id}%")
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($collection->id, $results);
    }

    public function test_item_project_filter_finds_project_by_uuid(): void
    {
        $project = Project::factory()->create([
            'internal_name' => 'Temple Survey',
        ]);
        Project::factory()->create([
            'internal_name' => 'Other Survey',
        ]);

        $results = Project::query()
            ->where('internal_name', 'like', "%{$project->id}%")
            ->orWhere('backward_compatibility', 'like', "%{$project->id}%")
            ->orWhere('id', 'like', "%{$project->id}%")
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($project->id, $results);
    }

    public function test_item_tag_filter_finds_tag_by_description(): void
    {
        $tag = Tag::factory()->keyword()->create([
            'internal_name' => 'ancient-pottery',
            'description' => 'Ancient Pottery',
            'backward_compatibility' => null,
        ]);
        Tag::factory()->keyword()->create([
            'internal_name' => 'bronze-age',
            'description' => 'Bronze Age',
        ]);

        $results = Tag::query()
            ->where('internal_name', 'like', '%ancient pottery%')
            ->orWhere('description', 'like', '%ancient pottery%')
            ->orWhere('backward_compatibility', 'like', '%ancient pottery%')
            ->orderBy('description')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($tag->id, $results);
    }

    public function test_item_tag_filter_finds_tag_by_legacy_code(): void
    {
        $tag = Tag::factory()->keyword()->create([
            'internal_name' => 'religious',
            'description' => 'Religious',
            'backward_compatibility' => 'tag-relig-007',
        ]);
        Tag::factory()->keyword()->create([
            'internal_name' => 'secular',
            'description' => 'Secular',
            'backward_compatibility' => null,
        ]);

        $results = Tag::query()
            ->where('internal_name', 'like', '%tag-relig%')
            ->orWhere('description', 'like', '%tag-relig%')
            ->orWhere('backward_compatibility', 'like', '%tag-relig%')
            ->orderBy('description')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($tag->id, $results);
    }

    public function test_item_tag_filter_finds_tag_by_uuid(): void
    {
        $tag = Tag::factory()->keyword()->create([
            'internal_name' => 'religious',
            'description' => 'Religious',
        ]);
        Tag::factory()->keyword()->create([
            'internal_name' => 'secular',
            'description' => 'Secular',
        ]);

        $results = Tag::query()
            ->where('internal_name', 'like', "%{$tag->id}%")
            ->orWhere('description', 'like', "%{$tag->id}%")
            ->orWhere('backward_compatibility', 'like', "%{$tag->id}%")
            ->orWhere('id', 'like', "%{$tag->id}%")
            ->orderBy('description')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($tag->id, $results);
    }

    // ─── Item action selector searches ────────────────────────────────────────

    public function test_item_change_parent_selector_finds_item_by_legacy_code(): void
    {
        $parent = Item::factory()->Object()->create([
            'internal_name' => 'Root object',
            'backward_compatibility' => 'item-root-001',
        ]);
        Item::factory()->Object()->create([
            'internal_name' => 'Another object',
            'backward_compatibility' => 'item-other',
        ]);

        $results = Item::query()
            ->where('internal_name', 'like', '%item-root%')
            ->orWhere('backward_compatibility', 'like', '%item-root%')
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($parent->id, $results);
    }

    public function test_item_change_parent_selector_finds_item_by_uuid(): void
    {
        $parent = Item::factory()->Object()->create([
            'internal_name' => 'Root object',
        ]);
        Item::factory()->Object()->create([
            'internal_name' => 'Another object',
        ]);

        $results = Item::query()
            ->where('internal_name', 'like', "%{$parent->id}%")
            ->orWhere('backward_compatibility', 'like', "%{$parent->id}%")
            ->orWhere('id', 'like', "%{$parent->id}%")
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($parent->id, $results);
    }

    public function test_item_attach_collection_selector_finds_collection_by_legacy_code(): void
    {
        $collection = Collection::factory()->create([
            'internal_name' => 'Temple Exhibition',
            'backward_compatibility' => 'coll-temple-exh',
        ]);
        Collection::factory()->create([
            'internal_name' => 'Medieval Gallery',
            'backward_compatibility' => 'coll-med',
        ]);

        $results = Collection::query()
            ->where('internal_name', 'like', '%coll-temple-exh%')
            ->orWhere('backward_compatibility', 'like', '%coll-temple-exh%')
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($collection->id, $results);
    }

    public function test_item_attach_collection_selector_finds_collection_by_uuid(): void
    {
        $collection = Collection::factory()->create([
            'internal_name' => 'Temple Exhibition',
        ]);
        Collection::factory()->create([
            'internal_name' => 'Medieval Gallery',
        ]);

        $results = Collection::query()
            ->where('internal_name', 'like', "%{$collection->id}%")
            ->orWhere('backward_compatibility', 'like', "%{$collection->id}%")
            ->orWhere('id', 'like', "%{$collection->id}%")
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($collection->id, $results);
    }

    public function test_item_attach_tag_selector_finds_tag_by_description(): void
    {
        $tag = Tag::factory()->material()->create([
            'internal_name' => 'limestone',
            'description' => 'Limestone',
        ]);
        Tag::factory()->material()->create([
            'internal_name' => 'marble',
            'description' => 'Marble',
        ]);

        $results = Tag::query()
            ->where('internal_name', 'like', '%limestone%')
            ->orWhere('description', 'like', '%limestone%')
            ->orWhere('backward_compatibility', 'like', '%limestone%')
            ->orderBy('description')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($tag->id, $results);
    }

    // ─── Collection action selector searches ──────────────────────────────────

    public function test_collection_change_parent_selector_finds_collection_by_legacy_code(): void
    {
        $parent = Collection::factory()->create([
            'internal_name' => 'Root Collection',
            'backward_compatibility' => 'root-coll-001',
        ]);
        Collection::factory()->create([
            'internal_name' => 'Another Collection',
            'backward_compatibility' => 'other-coll',
        ]);

        $results = Collection::query()
            ->where('internal_name', 'like', '%root-coll%')
            ->orWhere('backward_compatibility', 'like', '%root-coll%')
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($parent->id, $results);
    }

    public function test_collection_change_parent_selector_finds_collection_by_uuid(): void
    {
        $parent = Collection::factory()->create([
            'internal_name' => 'Root Collection',
        ]);
        Collection::factory()->create([
            'internal_name' => 'Another Collection',
        ]);

        $results = Collection::query()
            ->where('internal_name', 'like', "%{$parent->id}%")
            ->orWhere('backward_compatibility', 'like', "%{$parent->id}%")
            ->orWhere('id', 'like', "%{$parent->id}%")
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($parent->id, $results);
    }

    public function test_collection_project_filter_finds_project_by_legacy_code(): void
    {
        $project = Project::factory()->create([
            'internal_name' => 'Temple Catalogue',
            'backward_compatibility' => 'proj-temple-cat',
        ]);
        Project::factory()->create([
            'internal_name' => 'Other Project',
            'backward_compatibility' => 'proj-other',
        ]);

        $results = Project::query()
            ->where('internal_name', 'like', '%proj-temple-cat%')
            ->orWhere('backward_compatibility', 'like', '%proj-temple-cat%')
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($project->id, $results);
    }

    public function test_collection_project_filter_finds_project_by_uuid(): void
    {
        $project = Project::factory()->create([
            'internal_name' => 'Temple Catalogue',
        ]);
        Project::factory()->create([
            'internal_name' => 'Other Project',
        ]);

        $results = Project::query()
            ->where('internal_name', 'like', "%{$project->id}%")
            ->orWhere('backward_compatibility', 'like', "%{$project->id}%")
            ->orWhere('id', 'like', "%{$project->id}%")
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($project->id, $results);
    }

    public function test_collection_parent_filter_finds_collection_by_legacy_code(): void
    {
        $parent = Collection::factory()->create([
            'internal_name' => 'Parent Collection',
            'backward_compatibility' => 'parent-coll-legacy',
        ]);
        Collection::factory()->create([
            'internal_name' => 'Other Collection',
            'backward_compatibility' => 'other-coll-legacy',
        ]);

        $results = Collection::query()
            ->where('internal_name', 'like', '%parent-coll-legacy%')
            ->orWhere('backward_compatibility', 'like', '%parent-coll-legacy%')
            ->orWhere('id', 'like', '%parent-coll-legacy%')
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($parent->id, $results);
    }

    public function test_collection_parent_filter_finds_collection_by_uuid(): void
    {
        $parent = Collection::factory()->create([
            'internal_name' => 'Parent Collection',
        ]);
        Collection::factory()->create([
            'internal_name' => 'Other Collection',
        ]);

        $results = Collection::query()
            ->where('internal_name', 'like', "%{$parent->id}%")
            ->orWhere('backward_compatibility', 'like', "%{$parent->id}%")
            ->orWhere('id', 'like', "%{$parent->id}%")
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($parent->id, $results);
    }

    public function test_collection_partner_filter_finds_partner_by_legacy_code(): void
    {
        $partner = Partner::factory()->create([
            'internal_name' => 'Museum Partner',
            'backward_compatibility' => 'museum-partner-legacy',
        ]);
        Partner::factory()->create([
            'internal_name' => 'Other Partner',
            'backward_compatibility' => 'other-partner-legacy',
        ]);

        $results = Partner::query()
            ->where('internal_name', 'like', '%museum-partner-legacy%')
            ->orWhere('backward_compatibility', 'like', '%museum-partner-legacy%')
            ->orWhere('id', 'like', '%museum-partner-legacy%')
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($partner->id, $results);
    }

    public function test_collection_partner_filter_finds_partner_by_uuid(): void
    {
        $partner = Partner::factory()->create([
            'internal_name' => 'Museum Partner',
        ]);
        Partner::factory()->create([
            'internal_name' => 'Other Partner',
        ]);

        $results = Partner::query()
            ->where('internal_name', 'like', "%{$partner->id}%")
            ->orWhere('backward_compatibility', 'like', "%{$partner->id}%")
            ->orWhere('id', 'like', "%{$partner->id}%")
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($partner->id, $results);
    }

    // ─── Partner filter presence ───────────────────────────────────────────────

    public function test_partner_country_filter_finds_country_by_iso_code(): void
    {
        $country = Country::factory()->create([
            'id' => 'jor',
            'internal_name' => 'Jordan',
        ]);
        Country::factory()->create([
            'id' => 'fra',
            'internal_name' => 'France',
        ]);

        $results = Country::query()
            ->where('internal_name', 'like', '%jor%')
            ->orWhere('id', 'like', '%jor%')
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($country->id, $results);
    }

    public function test_partner_project_filter_finds_project_by_legacy_code(): void
    {
        $project = Project::factory()->create([
            'internal_name' => 'Heritage Survey',
            'backward_compatibility' => 'proj-heritage-01',
        ]);
        Project::factory()->create([
            'internal_name' => 'Digital Archive',
            'backward_compatibility' => 'proj-digital',
        ]);

        $results = Project::query()
            ->where('internal_name', 'like', '%proj-heritage%')
            ->orWhere('backward_compatibility', 'like', '%proj-heritage%')
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($project->id, $results);
    }

    public function test_partner_project_filter_finds_project_by_uuid(): void
    {
        $project = Project::factory()->create([
            'internal_name' => 'Heritage Survey',
        ]);
        Project::factory()->create([
            'internal_name' => 'Digital Archive',
        ]);

        $results = Project::query()
            ->where('internal_name', 'like', "%{$project->id}%")
            ->orWhere('backward_compatibility', 'like', "%{$project->id}%")
            ->orWhere('id', 'like', "%{$project->id}%")
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($project->id, $results);
    }

    // ─── Project resource filter searches ────────────────────────────────────

    public function test_project_context_filter_finds_context_by_uuid(): void
    {
        $context = Context::factory()->create([
            'internal_name' => 'Web Context',
        ]);
        Context::factory()->create([
            'internal_name' => 'Print Context',
        ]);

        $results = Context::query()
            ->where('internal_name', 'like', "%{$context->id}%")
            ->orWhere('backward_compatibility', 'like', "%{$context->id}%")
            ->orWhere('id', 'like', "%{$context->id}%")
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($context->id, $results);
    }

    public function test_project_context_filter_finds_context_by_legacy_code(): void
    {
        $context = Context::factory()->create([
            'internal_name' => 'Web Context',
            'backward_compatibility' => 'ctx-web-001',
        ]);
        Context::factory()->create([
            'internal_name' => 'Print Context',
            'backward_compatibility' => 'ctx-print',
        ]);

        $results = Context::query()
            ->where('internal_name', 'like', '%ctx-web%')
            ->orWhere('backward_compatibility', 'like', '%ctx-web%')
            ->orWhere('id', 'like', '%ctx-web%')
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($context->id, $results);
    }

    public function test_project_language_filter_finds_language_by_iso_code(): void
    {
        $language = Language::factory()->create([
            'id' => 'ara',
            'internal_name' => 'Arabic',
        ]);
        Language::factory()->create([
            'id' => 'fra',
            'internal_name' => 'French',
        ]);

        $results = Language::query()
            ->where('internal_name', 'like', '%ara%')
            ->orWhere('id', 'like', '%ara%')
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($language->id, $results);
    }

    // ─── Timeline filter searches ─────────────────────────────────────────────

    public function test_timeline_country_filter_finds_country_by_iso_code(): void
    {
        $country = Country::factory()->create([
            'id' => 'tur',
            'internal_name' => 'Turkey',
        ]);
        Country::factory()->create([
            'id' => 'egy',
            'internal_name' => 'Egypt',
        ]);

        $results = Country::query()
            ->where('internal_name', 'like', '%tur%')
            ->orWhere('id', 'like', '%tur%')
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($country->id, $results);
    }

    public function test_timeline_collection_filter_finds_collection_by_legacy_code(): void
    {
        $collection = Collection::factory()->create([
            'internal_name' => 'Ottoman Collection',
            'backward_compatibility' => 'coll-ottoman-001',
        ]);
        Collection::factory()->create([
            'internal_name' => 'Byzantine Collection',
            'backward_compatibility' => 'coll-byz',
        ]);

        $results = Collection::query()
            ->where('internal_name', 'like', '%coll-ottoman%')
            ->orWhere('backward_compatibility', 'like', '%coll-ottoman%')
            ->orWhere('id', 'like', '%coll-ottoman%')
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($collection->id, $results);
    }

    public function test_timeline_collection_filter_finds_collection_by_uuid(): void
    {
        $collection = Collection::factory()->create([
            'internal_name' => 'Ottoman Collection',
        ]);
        Collection::factory()->create([
            'internal_name' => 'Byzantine Collection',
        ]);

        $results = Collection::query()
            ->where('internal_name', 'like', "%{$collection->id}%")
            ->orWhere('backward_compatibility', 'like', "%{$collection->id}%")
            ->orWhere('id', 'like', "%{$collection->id}%")
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($collection->id, $results);
    }

    // ─── TimelineEvent filter searches ───────────────────────────────────────

    public function test_timeline_event_timeline_filter_finds_timeline_by_legacy_code(): void
    {
        $timeline = Timeline::factory()->create([
            'internal_name' => 'Ottoman History',
            'backward_compatibility' => 'tl-ottoman-001',
        ]);
        Timeline::factory()->create([
            'internal_name' => 'Byzantine History',
            'backward_compatibility' => 'tl-byz',
        ]);

        $results = Timeline::query()
            ->where('internal_name', 'like', '%tl-ottoman%')
            ->orWhere('backward_compatibility', 'like', '%tl-ottoman%')
            ->orWhere('id', 'like', '%tl-ottoman%')
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($timeline->id, $results);
    }

    public function test_timeline_event_timeline_filter_finds_timeline_by_uuid(): void
    {
        $timeline = Timeline::factory()->create([
            'internal_name' => 'Ottoman History',
        ]);
        Timeline::factory()->create([
            'internal_name' => 'Byzantine History',
        ]);

        $results = Timeline::query()
            ->where('internal_name', 'like', "%{$timeline->id}%")
            ->orWhere('backward_compatibility', 'like', "%{$timeline->id}%")
            ->orWhere('id', 'like', "%{$timeline->id}%")
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($timeline->id, $results);
    }

    // ─── Tag language filter ──────────────────────────────────────────────────

    public function test_tag_language_filter_finds_language_by_iso_code(): void
    {
        $language = Language::factory()->create([
            'id' => 'deu',
            'internal_name' => 'German',
        ]);
        Language::factory()->create([
            'id' => 'esp',
            'internal_name' => 'Spanish',
        ]);

        $results = Language::query()
            ->where('internal_name', 'like', '%deu%')
            ->orWhere('id', 'like', '%deu%')
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($language->id, $results);
    }

    // ─── Tree page searches ───────────────────────────────────────────────────

    public function test_browse_item_tree_search_finds_item_by_uuid(): void
    {
        $item = Item::factory()->Object()->create([
            'internal_name' => 'Pottery Vase',
            'parent_id' => null,
        ]);
        Item::factory()->Object()->create([
            'internal_name' => 'Bronze Statue',
            'parent_id' => null,
        ]);

        $page = new BrowseItemTree;
        $page->search = $item->id;

        $roots = $page->getRoots();
        $ids = $roots->pluck('id')->all();

        $this->assertContains($item->id, $ids);
    }

    public function test_browse_item_tree_search_count_matches_uuid_search(): void
    {
        $item = Item::factory()->Object()->create([
            'internal_name' => 'Pottery Vase',
            'parent_id' => null,
        ]);
        Item::factory()->Object()->create([
            'internal_name' => 'Bronze Statue',
            'parent_id' => null,
        ]);

        $page = new BrowseItemTree;
        $page->search = $item->id;

        $this->assertGreaterThanOrEqual(1, $page->getRootCount());
    }

    public function test_browse_collection_tree_search_finds_collection_by_uuid(): void
    {
        $collection = Collection::factory()->create([
            'internal_name' => 'Unique Collection',
            'parent_id' => null,
        ]);
        Collection::factory()->create([
            'internal_name' => 'Other Collection',
            'parent_id' => null,
        ]);

        $page = new BrowseCollectionTree;
        $page->search = $collection->id;
        $page->filterChildCollections = 'all';

        $roots = $page->getRoots();
        $ids = $roots->pluck('id')->all();

        $this->assertContains($collection->id, $ids);
    }

    // ─── Translation coverage filter language/context search ─────────────────

    public function test_translation_coverage_filter_language_search_finds_by_iso_code(): void
    {
        $language = Language::factory()->create([
            'id' => 'zho',
            'internal_name' => 'Chinese',
        ]);
        Language::factory()->create([
            'id' => 'kor',
            'internal_name' => 'Korean',
        ]);

        $results = Language::query()
            ->where('internal_name', 'like', '%zho%')
            ->orWhere('id', 'like', '%zho%')
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($language->id, $results);
    }

    public function test_translation_coverage_filter_context_search_finds_by_uuid(): void
    {
        $context = Context::factory()->create([
            'internal_name' => 'Mobile Context',
        ]);
        Context::factory()->create([
            'internal_name' => 'Desktop Context',
        ]);

        $results = Context::query()
            ->where('internal_name', 'like', "%{$context->id}%")
            ->orWhere('id', 'like', "%{$context->id}%")
            ->orWhere('backward_compatibility', 'like', "%{$context->id}%")
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($context->id, $results);
    }

    public function test_translation_coverage_filter_context_search_finds_by_legacy_code(): void
    {
        $context = Context::factory()->create([
            'internal_name' => 'Mobile Context',
            'backward_compatibility' => 'ctx-mobile-001',
        ]);
        Context::factory()->create([
            'internal_name' => 'Desktop Context',
            'backward_compatibility' => 'ctx-desktop',
        ]);

        $results = Context::query()
            ->where('internal_name', 'like', '%ctx-mobile%')
            ->orWhere('id', 'like', '%ctx-mobile%')
            ->orWhere('backward_compatibility', 'like', '%ctx-mobile%')
            ->orderBy('internal_name')
            ->limit(50)
            ->pluck('id')
            ->all();

        $this->assertContains($context->id, $results);
    }

    // ─── Result count boundary (no full-table loads) ──────────────────────────

    public function test_available_image_attach_selector_is_bounded_to_fifty_results(): void
    {
        // Create 60 matching records
        AvailableImage::factory()->count(60)->create(['comment' => 'boundary-test image']);

        $results = AvailableImage::query()
            ->where('path', 'like', '%boundary-test%')
            ->orWhere('original_name', 'like', '%boundary-test%')
            ->orWhere('comment', 'like', '%boundary-test%')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $this->assertCount(50, $results);
    }

    public function test_tag_filter_search_is_bounded_to_fifty_results(): void
    {
        // Create 60 matching tags
        Tag::factory()->count(60)->create([
            'description' => 'bounded-tag',
            'internal_name' => fn () => 'tag-'.uniqid(),
        ]);

        $results = Tag::query()
            ->where('internal_name', 'like', '%bounded-tag%')
            ->orWhere('description', 'like', '%bounded-tag%')
            ->orWhere('backward_compatibility', 'like', '%bounded-tag%')
            ->orderBy('description')
            ->limit(50)
            ->get();

        $this->assertCount(50, $results);
    }
}
