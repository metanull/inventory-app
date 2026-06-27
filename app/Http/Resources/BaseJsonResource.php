<?php

namespace App\Http\Resources;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @template TModel of Model
 *
 * @property TModel $resource
 */
abstract class BaseJsonResource extends JsonResource {}
