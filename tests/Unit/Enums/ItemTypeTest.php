<?php

namespace Tests\Unit\Enums;

use App\Enums\ItemType;
use Tests\TestCase;

class ItemTypeTest extends TestCase
{
    public function test_has_all_required_cases(): void
    {
        $cases = ItemType::cases();

        $this->assertCount(4, $cases);
        $this->assertTrue(in_array(ItemType::OBJECT, $cases, true));
        $this->assertTrue(in_array(ItemType::MONUMENT, $cases, true));
        $this->assertTrue(in_array(ItemType::DETAIL, $cases, true));
        $this->assertTrue(in_array(ItemType::PICTURE, $cases, true));
    }

    public function test_has_correct_values(): void
    {
        $this->assertEquals('object', ItemType::OBJECT->value);
        $this->assertEquals('monument', ItemType::MONUMENT->value);
        $this->assertEquals('detail', ItemType::DETAIL->value);
        $this->assertEquals('picture', ItemType::PICTURE->value);
    }

    public function test_labels_are_human_readable(): void
    {
        $this->assertEquals('Object', ItemType::OBJECT->label());
        $this->assertEquals('Monument', ItemType::MONUMENT->label());
        $this->assertEquals('Detail', ItemType::DETAIL->label());
        $this->assertEquals('Picture', ItemType::PICTURE->label());
    }

    public function test_options_returns_array_for_forms(): void
    {
        $options = ItemType::options();

        $this->assertIsArray($options);
        $this->assertCount(4, $options);

        foreach ($options as $option) {
            $this->assertIsObject($option);
            $this->assertObjectHasProperty('id', $option);
            $this->assertObjectHasProperty('name', $option);
            $this->assertIsString($option->id);
            $this->assertIsString($option->name);
        }
    }

    public function test_options_contain_all_types(): void
    {
        $options = ItemType::options();
        $ids = array_column($options, 'id');

        $this->assertContains('object', $ids);
        $this->assertContains('monument', $ids);
        $this->assertContains('detail', $ids);
        $this->assertContains('picture', $ids);
    }
}
