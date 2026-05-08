<?php

namespace Tests\Filament\Resources;

use App\Filament\Resources\CollectionResource\RelationManagers\ImagesRelationManager as CollectionImagesRelationManager;
use App\Filament\Resources\ItemResource\RelationManagers\ImagesRelationManager as ItemImagesRelationManager;
use App\Filament\Resources\PartnerResource\RelationManagers\ImagesRelationManager as PartnerImagesRelationManager;
use App\Filament\Resources\PartnerTranslationResource\RelationManagers\ImagesRelationManager as PartnerTranslationImagesRelationManager;
use App\Filament\Resources\RelationManagers\BaseImagesRelationManager;
use App\Filament\Resources\TimelineEventResource\RelationManagers\ImagesRelationManager as TimelineEventImagesRelationManager;
use App\Filament\Support\TranslationFormSchema;
use ReflectionMethod;
use Tests\TestCase;

class FilamentDuplicationRefactorTest extends TestCase
{
    public function test_all_images_relation_managers_extend_shared_base_class(): void
    {
        $this->assertTrue(is_subclass_of(ItemImagesRelationManager::class, BaseImagesRelationManager::class));
        $this->assertTrue(is_subclass_of(CollectionImagesRelationManager::class, BaseImagesRelationManager::class));
        $this->assertTrue(is_subclass_of(PartnerImagesRelationManager::class, BaseImagesRelationManager::class));
        $this->assertTrue(is_subclass_of(PartnerTranslationImagesRelationManager::class, BaseImagesRelationManager::class));
        $this->assertTrue(is_subclass_of(TimelineEventImagesRelationManager::class, BaseImagesRelationManager::class));
    }

    public function test_translation_form_schema_exposes_shared_entity_select_factories(): void
    {
        $itemMethod = new ReflectionMethod(TranslationFormSchema::class, 'itemSelectField');
        $collectionMethod = new ReflectionMethod(TranslationFormSchema::class, 'collectionSelectField');
        $partnerMethod = new ReflectionMethod(TranslationFormSchema::class, 'partnerSelectField');
        $contextMethod = new ReflectionMethod(TranslationFormSchema::class, 'contextSelectField');

        $this->assertSame('Filament\\Forms\\Components\\Select', $itemMethod->getReturnType()?->getName());
        $this->assertSame('Filament\\Forms\\Components\\Select', $collectionMethod->getReturnType()?->getName());
        $this->assertSame('Filament\\Forms\\Components\\Select', $partnerMethod->getReturnType()?->getName());
        $this->assertSame('Filament\\Forms\\Components\\Select', $contextMethod->getReturnType()?->getName());
    }
}
