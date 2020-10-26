<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Basket\Infrastructure;

use OxidEsales\Eshop\Application\Model\Basket as EshopBasketModel;
use OxidEsales\Eshop\Application\Model\UserBasket as EshopUserBasketModel;
use OxidEsales\Eshop\Application\Model\DeliverySet as EshopDeliverySetModel;
use OxidEsales\Eshop\Application\Model\DeliverySetList as EshopDeliverySetListModel;
use OxidEsales\Eshop\Application\Model\Order as OrderModel;
use OxidEsales\Eshop\Application\Model\User as EshopUserModel;
use OxidEsales\GraphQL\Account\Address\Service\DeliveryAddress as DeliveryAddressService;
use OxidEsales\GraphQL\Account\Basket\DataType\Basket as BasketDataType;
use OxidEsales\GraphQL\Account\Country\DataType\Country as CountryDataType;
use OxidEsales\GraphQL\Account\Customer\DataType\Customer as CustomerDataType;
use OxidEsales\GraphQL\Account\Order\DataType\Order as OrderDataType;
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

    public function placeOrder(
        CustomerDataType $customer,
        BasketDataType $userBasket
    ): OrderDataType {

        /** @var EshopUserModel $user */
        $userModel = $customer->getEshopModel();

        /** @var EshopUserBasketModel $userBasketModel */
        $userBasketModel = $userBasket->getEshopModel();

        //set delivery address to basket if any is given
        if(!empty($userBasketModel->getFieldData('oegql_deladdressid'))) {
            $userModel->setSelectedAddressId($userBasketModel->getFieldData('oegql_deladdressid'));
        }
        $_POST['sDeliveryAddressMD5'] = $userModel->getEncodedDeliveryAddress();

        /** @var EshopBasketModel $basketModel */
        $basketModel = $this->accountBasketInfrastructure->getBasket($userBasketModel, $userModel);
        $basketModel->setPayment($userBasketModel->getFieldData('oegql_paymentid'));
        $basketModel->setShipping($userBasketModel->getFieldData('oegql_shippingid'));

        /** @var OrderModel $orderModel */
        $orderModel = oxNew(OrderModel::class);
        $state = $orderModel->finalizeOrder($basketModel, $userModel);

        //we need to delete the basket after order to prevent ordering it twice
        if ($state === $orderModel::ORDER_STATE_OK || $state === $orderModel::ORDER_STATE_MAILINGERROR) {
            $basketModel->deleteBasket();
        }

        //return order data type
        return new OrderDataType($orderModel);
    }
}
