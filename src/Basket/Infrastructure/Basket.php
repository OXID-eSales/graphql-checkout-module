<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Basket\Infrastructure;

use OxidEsales\Eshop\Application\Model\DeliverySet as EshopDeliverySetModel;
use OxidEsales\Eshop\Application\Model\DeliverySetList as EshopDeliverySetListModel;
use OxidEsales\GraphQL\Account\Basket\DataType\Basket as BasketDataType;
use OxidEsales\GraphQL\Account\Country\DataType\Country as CountryDataType;
use OxidEsales\GraphQL\Account\Customer\DataType\Customer as CustomerDataType;
use OxidEsales\GraphQL\Account\Payment\DataType\Payment as PaymentDataType;
use OxidEsales\GraphQL\Account\Shared\Infrastructure\Basket as AccountBasketInfrastructure;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository;
use OxidEsales\GraphQL\Checkout\DeliveryMethod\DataType\DeliveryMethod as DeliveryMethodDataType;

final class Basket
{
    /** @var Repository */
    private $repository;

    /** @var AccountBasketInfrastructure */
    private $accountBasketInfrastructure;

    public function __construct(
        Repository $repository,
        AccountBasketInfrastructure $accountBasketInfrastructure
    ) {
        $this->repository                  = $repository;
        $this->accountBasketInfrastructure = $accountBasketInfrastructure;
    }

    public function setDeliveryAddress(BasketDataType $basket, string $deliveryAddressId): bool
    {
        $model = $basket->getEshopModel();

        $model->assign([
            'OEGQL_DELADDRESSID' => $deliveryAddressId,
        ]);

        return $this->repository->saveModel($model);
    }

    public function setPayment(BasketDataType $basket, string $paymentId): bool
    {
        $model = $basket->getEshopModel();

        $model->assign([
            'OEGQL_PAYMENTID' => $paymentId,
        ]);

        return $this->repository->saveModel($model);
    }

    /**
     * Update delivery method id for user basket
     * Resets payment id as it may be not available for new delivery method
     */
    public function setDeliveryMethod(BasketDataType $basket, string $deliveryMethodId): bool
    {
        $model = $basket->getEshopModel();

        $model->assign([
            'OEGQL_DELIVERYMETHODID' => $deliveryMethodId,
            'OEGQL_PAYMENTID'        => '',
        ]);

        return $this->repository->saveModel($model);
    }

    /**
     * @return DeliveryMethodDataType[]
     */
    public function getBasketAvailableDeliveryMethods(
        CustomerDataType $customer,
        BasketDataType $userBasket,
        CountryDataType $country
    ): array {
        $userModel       = $customer->getEshopModel();
        $userBasketModel = $userBasket->getEshopModel();
        $basketModel     = $this->accountBasketInfrastructure->getBasket($userBasketModel, $userModel);

        //Initialize available delivery set list for user and country
        /** @var EshopDeliverySetListModel $deliverySetList */
        $deliverySetList      = oxNew(EshopDeliverySetListModel::class);
        $deliverySetListArray = $deliverySetList->getDeliverySetList($userModel, (string) $country->getId());

        $result = [];
        /** @var EshopDeliverySetModel $deliverySet */
        foreach ($deliverySetListArray as $setKey => $deliverySet) {
            /** @phpstan-ignore-next-line */
            [$allMethods, $activeShipSet, $paymentList] = $deliverySetList->getDeliverySetData(
                $setKey,
                $userModel,
                $basketModel
            );

            $deliveryMethodPayments = [];

            foreach ($paymentList as $paymentModel) {
                $deliveryMethodPayments[$paymentModel->getId()] = new PaymentDataType($paymentModel);
            }

            if (!empty($deliveryMethodPayments)) {
                $result[$setKey] = new DeliveryMethodDataType($deliverySet, $deliveryMethodPayments);
            }
        }

        return $result;
    }
}
