<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Basket\Infrastructure;

use OxidEsales\GraphQL\Account\Basket\DataType\Basket as BasketDataType;
use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\Eshop\Application\Model\User as EshopUserModel;
use OxidEsales\Eshop\Application\Model\UserBasket as EshopUserBasketModel;
use OxidEsales\Eshop\Application\Model\Basket as EshopBasketModel;
use OxidEsales\PayPalModule\Core\Config as PayPalConfig;
use OxidEsales\PayPalModule\Controller\StandardDispatcher;
use OxidEsales\PayPalModule\Core\Exception\PayPalException;
use OxidEsales\PayPalModule\Model\PaymentValidator;
use OxidEsales\PayPalModule\Model\PayPalRequest\GetExpressCheckoutDetailsRequestBuilder;
use OxidEsales\PayPalModule\Model\PayPalRequest\SetExpressCheckoutRequestBuilder;
use OxidEsales\PayPalModule\Core\PayPalService;

final class PayPal
{
    public function expressCheckout(BasketDataType $userBasket): string
    {
        $basketModel = $this->createEshopBasket($userBasket);

        $requestBuilder = oxNew(SetExpressCheckoutRequestBuilder::class);
        $requestBuilder->setPayPalConfig(new PayPalConfig);
        $requestBuilder->setBasket($basketModel);
        $requestBuilder->setReturnUrl(EshopRegistry::getConfig()->getShopUrl());
        $requestBuilder->setCancelUrl(EshopRegistry::getConfig()->getShopUrl());
        $requestBuilder->setShowCartInPayPal(true);
        $requestBuilder->setTransactionMode('Sale');

        $request = $requestBuilder->buildExpressCheckoutRequest();
        $payPalService = oxNew(PayPalService::class);
        $response = $payPalService->setExpressCheckout($request);

        return (string) $response->getData()['TOKEN'];
    }

    private function createEshopBasket(BasketDataType $userBasket): EshopBasketModel
    {
        /** @var EshopUserBasketModel $userBasketModel */
        $userBasketModel = $userBasket->getEshopModel();
        $items = $userBasketModel->getItems();

        $basket = oxNew(EshopBasketModel::class);
        foreach ($items as $item) {
            $basket->addToBasket($item->getFieldData('oxartid'), $item->getFieldData('oxamount'));
        }

        $emptyUser = oxNew(EshopUserModel::class);
        $basket->setUser($emptyUser);
        $basket->setShipping($userBasketModel->getFieldData('OEGQL_DELIVERYMETHODID'));
        $basket->calculateBasket();

        return $basket;
    }

    public function getPaypalPayerId(string $paypalToken): string
    {
        $builder = oxNew(GetExpressCheckoutDetailsRequestBuilder::class);
        $request = $builder->getPayPalRequest();
        $request->setParameter('TOKEN', $paypalToken);

        $payPalService = oxNew(PayPalService::class);
        $response = $payPalService->getExpressCheckoutDetails($request);

        return (string) $response->getPayerId();
    }
}
