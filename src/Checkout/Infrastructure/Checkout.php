<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Checkout\Infrastructure;

use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\Eshop\Application\Model\DeliverySetList as EshopDeliverySetListModel;
use OxidEsales\Eshop\Application\Model\DeliverySet as EshopDeliverySetModel;
use OxidEsales\Eshop\Application\Model\Basket as EshopBasketModel;
use OxidEsales\Eshop\Application\Model\User as EshopUserModel;
use OxidEsales\Eshop\Application\Model\BasketItem as EshopBasketItemModel;
use OxidEsales\Eshop\Application\Model\UserBasket as EshopUserBasketModel;
use OxidEsales\GraphQL\Account\Basket\DataType\Basket as UserBasketDataType;
use OxidEsales\GraphQL\Account\Account\DataType\Customer as CustomerDataType;
use OxidEsales\GraphQL\Account\Country\DataType\Country as CountryDataType;
use OxidEsales\GraphQL\Checkout\Checkout\DataType\AvailablePayment as AvailablePaymentDataType;
use OxidEsales\GraphQL\Checkout\Checkout\DataType\DeliverySet as DeliverySetDataType;
use OxidEsales\GraphQL\Checkout\Checkout\DataType\CheckoutPayment as PaymentDataType;
use OxidEsales\GraphQL\Checkout\Checkout\DataType\Delivery as DeliveryDataType;

final class Checkout
{
    /**
     * @return DeliveryDataType[]
     */
    public function parcelDeliveriesForBasket(
        CustomerDataType $customer,
        UserBasketDataType $userBasket,
        CountryDataType $country,
        ?string $actShippingId = null
    ): array
    {
        /** @var EshopUserModel $user */
        $userModel = $customer->getEshopModel();
        $this->setUserIdToSession($userModel->getId()); //do we need this?

        /** @var EshopBasketModel $basketModel */
        $basketModel = $this->createBasketModel($userModel, $userBasket);

        //Initialize available delivery set list for user and country
        $deliverySetList = oxNew(EshopDeliverySetListModel::class);
        $initialList = $deliverySetList->getDeliverySetList($userModel, (string) $country->getId());

        $deliverySetListArray = [];
        if (is_null($actShippingId)) {
            $deliverySetListArray = $initialList;
        } elseif (array_key_exists($actShippingId, $initialList)) {
            $deliverySetListArray[$actShippingId] = $initialList[$actShippingId];
        }

        //create matrix for available shipping methods/payments
        $return = [];

        /** @var EshopDeliverySetModel[] $availableDeliverySets */
        foreach ($deliverySetListArray as $key => $set) {
            $payments = [];
            #$basketModel->setShipping($key); //do we need this?
            list($allSets, $activeShipSet, $paymentList) =
                $deliverySetList->getDeliverySetData($key, $userModel, $basketModel);

            if (empty($paymentList)) {
                continue;
            }

            $deliveryDataType = new DeliverySetDataType($set);

            foreach ($paymentList as $payment){
                /** @var EshopPaymentModel $payment */
                $payments[] = new PaymentDataType($payment);
            }

            $return[] = new DeliveryDataType($deliveryDataType, $payments);
        }

        return $return;
    }

    /**
     * @return DeliverySetDataType[]
     */
    public function parcelDeliveries(
        CustomerDataType $customer,
        CountryDataType $country
    ): array
    {
        /** @var EshopUserModel $user */
        $userModel = $customer->getEshopModel();
        $this->setUserIdToSession($userModel->getId()); //do we need this?

        //Get available delivery set list for user and country
        $deliverySetList = oxNew(EshopDeliverySetListModel::class);
        $deliverySetListArray = $deliverySetList->getDeliverySetList($userModel, (string) $country->getId());

        $return = [];

        foreach ($deliverySetListArray as $key => $set) {
            $return[$key] = new DeliverySetDataType($set);
        }

        return $return;
    }

    /**
     * @return AvailablePaymentDataType[]
     */
    public function paymentMethodsForBasket(
        CustomerDataType $customer,
        UserBasketDataType $userBasket,
        CountryDataType $country
    ): array
    {
        /** @var EshopUserModel $user */
        $userModel = $customer->getEshopModel();
        $this->setUserIdToSession($userModel->getId()); //do we need this?

        /** @var EshopBasketModel $basketModel */
        $basketModel = $this->createBasketModel($userModel, $userBasket);

        //Initialize available delivery set list for user and country
        $deliverySetList = oxNew(EshopDeliverySetListModel::class);
        $deliverySetListArray = $deliverySetList->getDeliverySetList($userModel, (string) $country->getId());

        //create matrix for available payment /shipping methods
        $payments = [];
        $deliveries = [];

        /** @var EshopDeliverySetModel[] $availableDeliverySets */
        foreach ($deliverySetListArray as $key => $set) {
            #$basketModel->setShipping($key); //do we need this?
            list($allSets, $activeShipSet, $paymentList) =
                $deliverySetList->getDeliverySetData($key, $userModel, $basketModel);

            foreach ($paymentList as $paymentModel) {
                $payments[$paymentModel->getId()] = new PaymentDataType($paymentModel);
                $deliveries[$paymentModel->getId()][] = new DeliverySetDataType($set);
            }
        }

        $return = [];
        foreach ($payments as $key => $item) {
            $return[] = new AvailablePaymentDataType($item, $deliveries[$key]);
        }

        return $return;
    }

    private function createBasketModel(EshopUserModel $userModel, UserBasketDataType $userBasket): EshopBasketModel
    {
        /** @var EshopUserBasketModel $userBasketModel */
        $userBasketModel = $userBasket->getEshopModel();
        //$userBasketModel = $userModel->getBasket($userBasket->title());

        /** @var EshopBasketModel $basketModel */
        $basketModel = oxNew(EshopBasketModel::class);

        $savedItems = $userBasketModel->getItems();
        foreach ($savedItems as $item) {
            try {
                $basketModel->addToBasket(
                      $item->getFieldData('oxartid'),
                      $item->getFieldData('oxamount'),
                      $item->getSelList(),
                      $item->getPersParams(),
                      true
                );
            } catch (\OxidEsales\Eshop\Core\Exception\ArticleException $exception) {
                // caught and ignored as does the shop (TODO: we need feedback for the customer)
            }
        }

        //do we need this?
        $basketModel->setBasketUser($userModel);
        EshopRegistry::getSession()->setBasket($basketModel);
        $basketModel->onUpdate();
        $basketModel->calculateBasket();

        return $basketModel;
    }

    /**
     * Sets user id to session.
     *
     * @param string $userId The user id.
     */
    private function setUserIdToSession($userId)
    {
        EshopRegistry::getConfig()->setUser(null);
        EshopRegistry::getSession()->setVariable('usr', $userId);
    }
}
