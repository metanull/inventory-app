<?php

namespace App\Http\Requests\Web;

use App\Models\Item;
use App\Support\Web\Lists\ItemImageListDefinition;
use App\Support\Web\Lists\ListDefinition;

class IndexItemImageRequest extends IndexListRequest
{
    protected function createDefinition(): ListDefinition
    {
        return new ItemImageListDefinition;
    }

    protected function prepareForValidation(): void
    {
        $item = $this->route('item');
        if ($item instanceof Item) {
            $this->merge(['item_id' => $item->id]);
        }
        parent::prepareForValidation();
    }
}
