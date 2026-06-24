<?php

namespace App\Http\Resources;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @template TModel of Model
 * @extends JsonResource<TModel>
 */
abstract class BaseJsonResource extends JsonResource
{
}
