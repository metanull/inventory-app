<?php

return [
    'uploads' => [
        'images' => [
            /*
            |--------------------------------------------------------------------------
            | The Maximum Size for Image Uploads
            |--------------------------------------------------------------------------
            |
            | This value defines the maximum size for image uploads in bytes. The
            | default is set to 20MB (20480 kilobytes). You can change this value
            | in your .env file using the `LOCAL_STORAGE_IMAGE_UPLOAD_MAX_SIZE` key.
            |
            | Note that this value is used for validation, so it should be set
            | according to your application's requirements.
            | */
            'max_size' => env('LOCAL_STORAGE_IMAGE_UPLOAD_MAX_SIZE', 20480), // in kb

            /*
            |--------------------------------------------------------------------------
            | The Allowed MIME Types for Image Uploads
            |--------------------------------------------------------------------------
            |
            | This value defines the allowed MIME types for image uploads. The
            | default is set to `jpeg,jpg,png`. You can change this value in
            | your .env file using the `IMAGE_MIME_TYPES` key.
            |
            | Note that this value is used for validation, so it should be set
            | according to your application's requirements.
            |
            */
            'mime' => env('IMAGE_MIME_TYPES', 'jpeg,jpg,png'),

            /*
            |--------------------------------------------------------------------------
            | The Disk for Image Uploads
            |--------------------------------------------------------------------------
            |
            | This disk is used to store uploaded images. It is defined in the
            | filesystem configuration. The default disk is set to **local**, but you
            | can change it to any other disk defined in your filesystem configuration.
            |
            */
            'disk' => env('LOCAL_STORAGE_IMAGE_UPLOAD_DISK', 'local'),

            /*
            |--------------------------------------------------------------------------
            | The Directory for Image Uploads
            |--------------------------------------------------------------------------
            |
            | This directory is used to store uploaded images. It is relative to the
            | _disk_ disk.
            |
            */
            'directory' => env('LOCAL_STORAGE_IMAGE_UPLOAD_DIRECTORY', 'image_uploads'),
        ],
    ],

    'public' => [
        'images' => [
            /*
            |--------------------------------------------------------------------------
            | The Maximum Width and Height for Public Images
            |--------------------------------------------------------------------------
            |
            | These values define the maximum width and height for public images.
            | The default is set to 3840x2160 (4K resolution). You can change
            | these values in your .env file using the `IMAGE_MAX_WIDTH` and
            | `IMAGE_MAX_HEIGHT` keys.
            |
            */
            'max_width' => env('IMAGE_MAX_WIDTH', 3840),
            'max_height' => env('IMAGE_MAX_HEIGHT', 2160),

            /*
            |--------------------------------------------------------------------------
            | The Disk for Public Images
            |--------------------------------------------------------------------------
            |
            | This disk is used to store images that are publicly accessible. It is
            | defined in the filesystem configuration. The default disk is set to
            | **public**, but you can change it to any other disk defined in your
            | filesystem configuration.
            |
            */
            'disk' => env('LOCAL_STORAGE_IMAGE_DISK', 'public'),

            /*
            |--------------------------------------------------------------------------
            | The Directory for Public Images
            |--------------------------------------------------------------------------
            |
            | This directory is used to store images that are publicly accessible.
            | It is relative to the _disk_ disk.
            |
            */
            'directory' => env('LOCAL_STORAGE_IMAGE_DIRECTORY', 'images'),
        ],
    ],

    'pictures' => [
        /*
        |--------------------------------------------------------------------------
        | The Disk for Attached Pictures
        |--------------------------------------------------------------------------
        |
        | This disk is used to store pictures that are attached to Items, Details,
        | or Partners. It is defined in the filesystem configuration. The default
        | disk is set to **public**, but you can change it to any other disk
        | defined in your filesystem configuration.
        |
        */
        'disk' => env('LOCAL_STORAGE_PICTURES_DISK', 'public'),

        /*
        |--------------------------------------------------------------------------
        | The Directory for Attached Pictures
        |--------------------------------------------------------------------------
        |
        | This directory is used to store pictures that are attached to Items,
        | Details, or Partners. It is relative to the _disk_ disk.
        |
        */
        'directory' => env('LOCAL_STORAGE_PICTURES_DIRECTORY', 'pictures'),
    ],
];
