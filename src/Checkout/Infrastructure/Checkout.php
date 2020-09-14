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

final class Checkout
{

    /**
     * @return DeliverySetDataType[]
     */
    public function parcelDeliveriesForBasket(
        CustomerDataType $customer,
        BasketDataType $basket,
        CountryDataType $country
    ): array
    {
        /** @var EshopUserModel $user */
        $user = $customer->getEshopModel();

        /** @var EshopUserBasketModel $userBasket */
        $userBasket = $basket->getEshopModel();

        //TODO: create EshopBasketModel from EshopUserBasketModel
        $basket = oxNew(EshopBasketModel::class);

        //TODO: we need some matrix for available shipping methods/payments
        $deliverySetList = oxNew(EshopDeliverySetListModel::class);
        list($allSets, $activeShipSet, $paymentList) = $deliverySetList->getDeliverySetData(null, $user, $basket);
        $result = [];

        foreach ($allSets as $set){
            /** @var EshopDeliverySetModel $deliverySet */
            $result[] = new DeliverySetDataType($set);
        }

        return $result;
    }

    /**
     * Sets user id to session.
     *
     * @param string $userId The user id.
     */
    private function setUserIdToSession($userId)
    {
        Registry::getConfig()->setUser(null);
        Registry::getSession()->setVariable('usr', $userId);
    }
}
