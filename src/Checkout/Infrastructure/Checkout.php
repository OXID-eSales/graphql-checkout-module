<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Checkout\Infrastructure;

use OxidEsales\Eshop\Application\Model\PaymentList as EshopPaymentListModel;
use OxidEsales\Eshop\Application\Model\DeliveryList as EshopDeliveryListModel;
use OxidEsales\Eshop\Application\Model\UserBasketItem as EshopUserBasketItemModel;
use OxidEsales\Eshop\Application\Model\DeliverySetList as EshopDeliverySetListModel;
use OxidEsales\Eshop\Application\Model\DeliverySet as EshopDeliverySetModel;
use OxidEsales\Eshop\Application\Model\Basket as EshopBasketModel;
use OxidEsales\Eshop\Application\Model\User as EshopUserModel;
use OxidEsales\Eshop\Application\Model\BasketItem as EshopBasketItemModel;
use OxidEsales\Eshop\Application\Model\UserBasket as EshopUserBasketModel;
use OxidEsales\GraphQL\Account\Basket\DataType\Basket as UserBasketDataType;
use OxidEsales\GraphQL\Account\Account\DataType\Customer as CustomerDataType;
use OxidEsales\GraphQL\Account\Country\DataType\Country as CountryDataType;
use OxidEsales\GraphQL\Checkout\Checkout\DataType\DeliverySet as DeliverySetDataType;
use OxidEsales\GraphQL\Checkout\Checkout\DataType\Payment as PaymentDataType;
use OxidEsales\GraphQL\Checkout\Checkout\DataType\Delivery as DeliveryDataType;

final class Checkout
{

    /**
     * @return DeliveryDataType[]
     */
    public function parcelDeliveriesForBasket(
        CustomerDataType $customer,
        UserBasketDataType $userBasket,
        CountryDataType $country
    ): array
    {
        /** @var EshopUserModel $user */
        $userModel = $customer->getEshopModel();

        /** @var EshopBasketModel $basketModel */
        $basketModel = $this->createBasket($userBasket);

        //Get available delivery set list for user and country
        $deliverySetList = oxNew(EshopDeliverySetListModel::class);
        $deliverySetListArray = $deliverySetList->getDeliverySetList($userModel, (string) $country->getId());

        //create matrix for available shipping methods/payments
        $return = [];

        $paymentList = oxNew(EshopPaymentListModel::class);
        $deliveryList = oxNew(EshopDeliveryListModel::class);
        //TODO: continue here

        /** @var EshopDeliverySetModel[] $availableDeliverySets */
        foreach ($deliverySetListArray as $key => $set) {
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

    private function createBasket(UserBasketDataType $userBasket): EshopBasketModel
    {
        /** @var EshopUserBasketModel $userBasket */
        $userBasketModel = $userBasket->getEshopModel();

        /** @var EshopBasketModel $basketModel */
        $basketModel = oxNew(EshopBasketModel::class);

        $savedItems = $userBasketModel->getItems(true);
       /* TODO
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
        }*/

        return $basketModel;
    }
}
