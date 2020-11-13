<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Basket\Infrastructure;

use OxidEsales\Eshop\Application\Model\Address as EshopAddressModel;
use OxidEsales\Eshop\Application\Model\Basket as EshopBasketModel;
use OxidEsales\Eshop\Application\Model\DeliverySet as EshopDeliverySetModel;
use OxidEsales\Eshop\Application\Model\DeliverySetList as EshopDeliverySetListModel;
use OxidEsales\Eshop\Application\Model\Order as OrderModel;
use OxidEsales\Eshop\Application\Model\User as EshopUserModel;
use OxidEsales\Eshop\Application\Model\UserBasket as EshopUserBasketModel;
use OxidEsales\GraphQL\Account\Basket\DataType\Basket as BasketDataType;
use OxidEsales\GraphQL\Account\Country\DataType\Country as CountryDataType;
use OxidEsales\GraphQL\Account\Customer\DataType\Customer as CustomerDataType;
use OxidEsales\GraphQL\Account\Order\DataType\Order as OrderDataType;
use OxidEsales\GraphQL\Account\Shared\Infrastructure\Basket as AccountBasketInfrastructure;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository;
use OxidEsales\GraphQL\Checkout\Basket\Exception\PlaceOrder as PlaceOrderException;
use OxidEsales\GraphQL\Checkout\DeliveryMethod\DataType\DeliveryMethod as DeliveryMethodDataType;
use OxidEsales\GraphQL\Checkout\Payment\DataType\BasketPayment;

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
        $basketModel     = $this->accountBasketInfrastructure->getCalculatedBasket($userBasket);

        //Initialize available delivery set list for user and country
        /** @var EshopDeliverySetListModel $deliverySetList */
        $deliverySetList      = oxNew(EshopDeliverySetListModel::class);
        $deliverySetListArray = $deliverySetList->getDeliverySetList($userModel, (string) $country->getId());

        $result = [];
        /** @var EshopDeliverySetModel $deliverySet */
        foreach ($deliverySetListArray as $setKey => $deliverySet) {
            [$allMethods, $activeShipSet, $paymentList] = $deliverySetList->getDeliverySetData(
                $setKey,
                $userModel,
                /** @phpstan-ignore-next-line */
                $basketModel
            );

            $deliveryMethodPayments = [];

            foreach ($paymentList as $paymentModel) {
                $deliveryMethodPayments[$paymentModel->getId()] = new BasketPayment($paymentModel, $basketModel);
            }

            if (!empty($deliveryMethodPayments)) {
                $result[$setKey] = new DeliveryMethodDataType($deliverySet, $deliveryMethodPayments);
            }
        }

        return $result;
    }

    public function placeOrder(
        CustomerDataType $customer,
        BasketDataType $userBasket
    ): OrderDataType {

        /** @var EshopUserModel $userModel */
        $userModel = $customer->getEshopModel();

        /** @var EshopUserBasketModel $userBasketModel */
        $userBasketModel = $userBasket->getEshopModel();

        if ($userBasketModel->getItemCount() === 0) {
            throw PlaceOrderException::emptyBasket((string) $userBasket->id());
        }

        $_POST['sDeliveryAddressMD5'] = $userModel->getEncodedDeliveryAddress();

        //set delivery address to basket if any is given
        if (!empty($userBasketModel->getFieldData('oegql_deladdressid'))) {
            $userModel->setSelectedAddressId($userBasketModel->getFieldData('oegql_deladdressid'));
            $_POST['deladrid'] = $userModel->getSelectedAddressId();
            /** @var EshopAddressModel $deliveryAdress */
            $deliveryAdress    = oxNew(EshopAddressModel::class);
            $deliveryAdress->load($userModel->getSelectedAddressId());
            $_POST['sDeliveryAddressMD5'] .= $deliveryAdress->getEncodedDeliveryAddress();
        }

        /** @var EshopBasketModel $basketModel */
        $basketModel = $this->accountBasketInfrastructure->getCalculatedBasket($userBasket);

        /** @var OrderModel $orderModel */
        $orderModel = oxNew(OrderModel::class);
        $status     = $orderModel->finalizeOrder($basketModel, $userModel);

        //we need to delete the basket after order to prevent ordering it twice
        if ($status === $orderModel::ORDER_STATE_OK || $status === $orderModel::ORDER_STATE_MAILINGERROR) {
            $basketModel->deleteBasket();
        } else {
            throw PlaceOrderException::byBasketId($userBasketModel->getId(), (int) $status);
        }

        //return order data type
        return new OrderDataType($orderModel);
    }
}
