<?php

use Bitrix\Main\DI\ServiceLocator;
use Dreamteam\Test\Repository\NewsRepository;
use Dreamteam\Test\Service\NewsService;

return [
    'controllers' => [
        'value' => [
            'defaultNamespace' => '\\Dreamteam\\Test\\Controller',
        ],
        'readonly' => true,
    ],
    'services' => [
        'value' => [
            NewsRepository::class => ['className' => NewsRepository::class],
            NewsService::class => [
                'className' => NewsService::class,
                'constructorParams' => static fn (): array => [
                    ServiceLocator::getInstance()->get(NewsRepository::class),
                ],
            ],
        ],
        'readonly' => true,
    ],
];
