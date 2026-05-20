<?php

return [

    'class_namespace' => 'App\\Http\\Livewire',

    'view_path' => resource_path('views/livewire'),

    'layout' => 'layouts.app',

    'inject_assets_automatically' => true,

    'inject_morph_markers' => true,

    'middleware_group' => 'web',

    'temporary_file_upload' => [
        'disk'        => null,       // uses default (local)
        'rules'       => ['required', 'file', 'max:10240'], // 10 MB max
        'directory'   => null,       // uses livewire-tmp
        'middleware'  => 'throttle:60,1',
        'preview_mimes' => [
            'png', 'gif', 'bmp', 'svg', 'wav', 'mp4',
            'mov', 'avi', 'wmv', 'mp3', 'm4a',
            'jpg', 'jpeg', 'mpga', 'webp', 'wma',
        ],
        'max_upload_time' => 5,
    ],

    'manifest_path' => null,

    'back_button_cache' => false,

    'render_on_redirect' => false,

    'asset_url' => null,

    'app_url' => null,

];
