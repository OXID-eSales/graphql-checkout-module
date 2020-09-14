<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Basket\Infrastructure;

use OxidEsales\Eshop\Application\Model\DeliverySet as EshopDeliverySetModel;
use OxidEsales\Eshop\Application\Model\DeliverySetList as EshopDeliverySetListModel;
use OxidEsales\Eshop\Application\Model\User as EshopUserModel;
use OxidEsales\GraphQL\Account\Address\Service\DeliveryAddress as DeliveryAddressService;
use OxidEsales\GraphQL\Account\Basket\DataType\Basket as BasketDataType;
use OxidEsales\GraphQL\Account\Country\DataType\Country as CountryDataType;
use OxidEsales\GraphQL\Account\Customer\DataType\Customer as CustomerDataType;
use OxidEsales\GraphQL\Account\Payment\DataType\Payment as PaymentDataType;
use OxidEsales\GraphQL\Account\Shared\Infrastructure\Basket as AccountBasketInfrastructure;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository;
use OxidEsales\GraphQL\Checkout\DeliverySet\DataType\DeliverySet as DeliverySetDataType;

final class Basket
{
    /** @var Repository */
    private $repository;

    /** @var AccountBasketInfrastructure */
    private $accountBasketInfrastructure;

    /** @var DeliveryAddressService */
    private $deliveryAddressService;

    public function __construct(
        Repository $repository,
        AccountBasketInfrastructure $accountBasketInfrastructure,
        DeliveryAddressService $deliveryAddressService
    ) {
        $this->repository                  = $repository;
        $this->accountBasketInfrastructure = $accountBasketInfrastructure;
        $this->deliveryAddressService      = $deliveryAddressService;
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
     * Update delivery set id for user basket
     * Resets payment id as it may be not available for new delivery set
     */
    public function setDeliverySet(BasketDataType $basket, string $deliverySetId): bool
    {
        $model = $basket->getEshopModel();

        $model->assign([
            'OEGQL_DELIVERYSETID' => $deliverySetId,
            'OEGQL_PAYMENTID'     => '',
        ]);

        return $this->repository->saveModel($model);
    }

    /**
     * @return DeliverySetDataType[]
     */
    public function getBasketAvailableDeliverySets(
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
        /** @var EshopDeliverySetModel $set */
        foreach ($deliverySetListArray as $setKey => $set) {
            /** @phpstan-ignore-next-line */
            [$allSets, $activeShipSet, $paymentList] = $deliverySetList->getDeliverySetData(
                $setKey,
                $userModel,
                $basketModel
            );

            $deliverySetPaymentMethods = [];

            foreach ($paymentList as $paymentModel) {
                $deliverySetPaymentMethods[$paymentModel->getId()] = new PaymentDataType($paymentModel);
            }

            if (!empty($deliverySetPaymentMethods)) {
                $result[$setKey] = new DeliverySetDataType($set, $deliverySetPaymentMethods);
            }
        }

        return $result;
    }

    /**
     * Calculate basket delivery country id
     */
    public function getBasketDeliveryCountryId(BasketDataType $basket): string
    {
        $countryId = null;

        if ($basketDeliveryAddressId = $basket->getEshopModel()->getFieldData('OEGQL_DELADDRESSID')) {
            $basketDeliveryAddress = $this->deliveryAddressService->getDeliveryAddress($basketDeliveryAddressId);
            $countryId             = (string) $basketDeliveryAddress->countryId()->val();
        }

        // if basket don't have delivery set, use basket user active address country id
        if (!$countryId) {
            /** @var EshopUserModel $userModel */
            $userModel = oxNew(EshopUserModel::class);
            $userModel->load((string) $basket->getUserId()->val());
            $countryId = (string) $userModel->getActiveCountry();
        }

        return $countryId;
    }
}
