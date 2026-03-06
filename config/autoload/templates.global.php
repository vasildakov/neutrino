<?php

declare(strict_types=1);

// config/autoload/templates.global.php

return [
    'templates' => [
        'layout' => 'layout::default', // Default layout template
        'map'    => [
            'layout::default'        => './src/Neutrino/templates/layout/default.phtml', //default
            'layout::sandbox'        => './src/Neutrino/templates/sandbox/layout/sandbox.phtml',
            'layout::authentication' => './src/Neutrino/templates/sandbox/layout/authentication.phtml',
//            'page::home'          => __DIR__ . '/templates/static/home.phtml',
//            'page::other'         => __DIR__ . '/templates/static/other.phtml',
        ],
    ],
];
