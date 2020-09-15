<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Checkout\Infrastructure;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\DeliverySetList as EshopDeliverySetListModel;
use OxidEsales\Eshop\Application\Model\DeliverySet as EshopDeliverySetModel;
use OxidEsales\Eshop\Application\Model\Basket as EshopBasketModel;
use OxidEsales\Eshop\Application\Model\User as EshopUserModel;
use OxidEsales\Eshop\Application\Model\UserBasket as EshopUserBasketModel;
use OxidEsales\GraphQL\Account\Basket\DataType\Basket as BasketDataType;
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
        BasketDataType $basket,
        CountryDataType $country
    ): array
    {
        /** @var EshopUserModel $user */
        $userModel = $customer->getEshopModel();

        /** @var EshopUserBasketModel $userBasket */
        $userBasketModel = $basket->getEshopModel();

        //TODO: create EshopBasketModel from EshopUserBasketModel
        $basketModel = oxNew(EshopBasketModel::class);

        //Get available delivery set list for user and country
        $deliverySetList = oxNew(EshopDeliverySetListModel::class);
        $deliverySetListArray = $deliverySetList->getDeliverySetList($userModel, (string) $country->getId());

        //create matrix for available shipping methods/payments
        $return = [];

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
}
