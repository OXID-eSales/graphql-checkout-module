<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\DeliveryMethod\Service;

use OxidEsales\GraphQL\Base\Exception\NotFound;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository;
use OxidEsales\GraphQL\Checkout\DeliveryMethod\DataType\DeliveryMethod as DeliveryMethodDataType;
use OxidEsales\GraphQL\Checkout\DeliveryMethod\Exception\DeliveryMethodNotFound;

final class DeliveryMethod
{
    /** @var Repository */
    private $repository;

    public function __construct(
        Repository $repository
    ) {
        $this->repository = $repository;
    }

    /**
     * @throws DeliveryMethodNotFound
     */
    public function getDeliveryMethod(string $id): DeliveryMethodDataType
    {
        try {
            /** @var DeliveryMethodDataType $deliveryMethod */
            $deliveryMethod = $this->repository->getById(
                $id,
                DeliveryMethodDataType::class,
                false
            );
        } catch (NotFound $e) {
            throw DeliveryMethodNotFound::byId($id);
        }

        return $deliveryMethod;
    }
}
