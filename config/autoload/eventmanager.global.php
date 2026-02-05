<?php

use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerInterface;

return [
    'dependencies' => [
        'aliases' => [
            EventManagerInterface::class => 'event_manager',
        ],
        'factories' => [
            'event_manager' => function () {
                $em = new EventManager();
                // Optionally set identifiers (for filtering listeners)
                $em->setIdentifiers(['app']);
                return $em;
            },
        ],
    ],
];
