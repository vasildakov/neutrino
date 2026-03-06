<?php

declare(strict_types=1);

return [
    'view_helper_config' => [
        'asset' => [
            'resource_map' => [
                // External CDN assets
                // phpcs:ignore Generic.Files.LineLength.TooLong
                'css/fonts/ubuntu.css' => 'https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap',
                // phpcs:ignore Generic.Files.LineLength.TooLong
                'css/fonts/roboto.css' => 'https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap',
                // phpcs:ignore Generic.Files.LineLength.TooLong
                'css/bootstrap.min.css' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',

                // Sandbox theme assets - CSS/JS (absolute paths from web root)
                'sandbox/assets/css/plugins.css' => '/sandbox/assets/css/plugins.css',
                'sandbox/assets/css/style.css'   => '/sandbox/assets/css/style.css',
                'sandbox/assets/js/plugins.js'   => '/sandbox/assets/js/plugins.js',
                'sandbox/assets/js/theme.js'     => '/sandbox/assets/js/theme.js',

                // Sandbox theme assets - Images (absolute paths from web root)
                'sandbox/assets/img/logo-dark.png'            => '/sandbox/assets/img/logo-dark.png',
                'sandbox/assets/img/logo-dark@2x.png'         => '/sandbox/assets/img/logo-dark@2x.png',
                'sandbox/assets/img/logo-light.png'           => '/sandbox/assets/img/logo-light.png',
                'sandbox/assets/img/logo-light@2x.png'        => '/sandbox/assets/img/logo-light@2x.png',
                'sandbox/assets/img/logo-purple.png'          => '/sandbox/assets/img/logo-purple.png',
                'sandbox/assets/img/logo-purple@2x.png'       => '/sandbox/assets/img/logo-purple@2x.png',
                'sandbox/assets/img/logo.png'                 => '/sandbox/assets/img/logo.png',
                'sandbox/assets/img/logo@2x.png'              => '/sandbox/assets/img/logo@2x.png',
                'sandbox/assets/img/illustrations/3d9.png'    => '/sandbox/assets/img/illustrations/3d9.png',
                'sandbox/assets/img/illustrations/3d9@2x.png' => '/sandbox/assets/img/illustrations/3d9@2x.png',
                'sandbox/assets/img/photos/clouds.png'        => 'sandbox/assets/img/photos/clouds.png',


                // Add versioned assets here when needed
                // Example with version hash:
                // 'css/style.css' => 'css/style-3a97ff4ee3.css',
                // 'js/vendor.js' => 'js/vendor-a507086eba.js',
            ],
        ],
    ],
];
