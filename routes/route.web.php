<?php

use DevNoKage\Enums\KeyRoute;
use DevNoKage\ErrorController;

$routes = [
    '/' => [
        KeyRoute::CONTROLLER->value => ErrorController::class,
        KeyRoute::METHOD->value => '_404',
        KeyRoute::MIDDLEWARE->value => []
    ],
    '/404' => [
        KeyRoute::CONTROLLER->value => ErrorController::class,
        KeyRoute::METHOD->value => '_404',
        KeyRoute::MIDDLEWARE->value => []
    ]
];
