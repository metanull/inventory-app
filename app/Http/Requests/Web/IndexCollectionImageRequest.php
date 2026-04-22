<?php

namespace App\Http\Requests\Web;

use App\Models\Collection;
use App\Support\Web\Lists\CollectionImageListDefinition;
use App\Support\Web\Lists\ListDefinition;

class IndexCollectionImageRequest extends IndexListRequest
{
    protected function createDefinition(): ListDefinition
    {
        return new CollectionImageListDefinition;
    }

    protected function prepareForValidation(): void
    {
        $collection = $this->route('collection');
        if ($collection instanceof Collection) {
            $this->merge(['collection_id' => $collection->id]);
        }
        parent::prepareForValidation();
    }
}
