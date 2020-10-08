<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Basket\Infrastructure;

use OxidEsales\GraphQL\Account\Basket\DataType\Basket as BasketDataType;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository;

final class Basket
{
    /** @var Repository */
    private $repository;

    public function __construct(
        Repository $repository
    ) {
        $this->repository = $repository;
    }

    public function setDeliveryAddress(BasketDataType $basket, string $deliveryAddressId): bool
    {
        $model = $basket->getEshopModel();
        $model->assign([
            'oxuserbaskets__oegql_deladdressid' => $deliveryAddressId,
        ]);

        return $this->repository->saveModel($model);
    }
}
