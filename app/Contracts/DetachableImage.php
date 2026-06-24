<?php

namespace App\Contracts;

use App\Models\AvailableImage;

interface DetachableImage
{
    public function detachToAvailableImage(): AvailableImage;
}
