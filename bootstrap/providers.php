<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\JetstreamServiceProvider;
use App\Providers\LocalImageFakerServiceProvider;
use Intervention\Image\ImageServiceProvider;

return [
    AppServiceProvider::class,
    LocalImageFakerServiceProvider::class,
    FortifyServiceProvider::class,
    JetstreamServiceProvider::class,
    ImageServiceProvider::class,
];
