<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

/**
 * Metadata version
 */
$sMetadataVersion = '2.0';

/**
 * Module information
 */
$aModule = [
    'id'            => 'oe_graphql_checkout',
    'title'         => [
        'de'        =>  'GraphQL Zur Kasse',
        'en'        =>  'GraphQL Checkout',
    ],
    'description'   =>  [
        'de' => '<span>OXID GraphQL Zur Kasse</span>',
        'en' => '<span>OXID GraphQL Checkout</span>',
    ],
    'thumbnail'   => 'out/pictures/logo.png',
    'version'     => '0.1.0',
    'author'      => 'OXID eSales',
    'url'         => 'www.oxid-esales.com',
    'email'       => 'info@oxid-esales.com',
    'extend'      => [
         \OxidEsales\Eshop\Application\Controller\PaymentController::class => \OxidEsales\GraphQL\Checkout\Shared\Shop\PaymentControllerHack::class
    ],
    'controllers' => [
    ],
    'templates'   => [
    ],
    'blocks'      => [
    ],
    'settings'    => [
    ],
];
