<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Basket\Infrastructure;

use OxidEsales\Eshop\Application\Model\Basket as EshopBasketModel;
use OxidEsales\Eshop\Application\Model\User as EshopUserModel;
use OxidEsales\Eshop\Application\Model\UserBasket as EshopUserBasketModel;
use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\GraphQL\Account\Basket\DataType\Basket as BasketDataType;
use OxidEsales\PayPalModule\Controller\ExpressCheckoutDispatcher;
use OxidEsales\PayPalModule\Core\Config as PayPalConfig;
use OxidEsales\PayPalModule\Core\PayPalService;
use OxidEsales\PayPalModule\Model\PayPalRequest\GetExpressCheckoutDetailsRequestBuilder;
use OxidEsales\PayPalModule\Model\PayPalRequest\SetExpressCheckoutRequestBuilder;

final class PayPal
{
    public function expressCheckout(BasketDataType $userBasket): string
    {
        $basketModel = $this->createEshopBasket($userBasket);

        $requestBuilder = oxNew(SetExpressCheckoutRequestBuilder::class);
        $requestBuilder->setPayPalConfig(new PayPalConfig());
        $requestBuilder->setBasket($basketModel);
        $requestBuilder->setReturnUrl(EshopRegistry::getConfig()->getShopUrl());
        $requestBuilder->setCancelUrl(EshopRegistry::getConfig()->getShopUrl());
        $requestBuilder->setShowCartInPayPal(true);
        $requestBuilder->setTransactionMode('Sale');

        $request       = $requestBuilder->buildExpressCheckoutRequest();
        $payPalService = oxNew(PayPalService::class);
        $response      = $payPalService->setExpressCheckout($request);

        return (string) $response->getData()['TOKEN'];
    }

    public function getPayPalCommunicationUrl(string $paypalToken, string $action = 'continue')
    {
        $payalConfig = new PayPalConfig();

        return $payalConfig->getPayPalCommunicationUrl($paypalToken, $action);
    }

    public function getPaypalPayerId(string $paypalToken): string
    {
        $builder = oxNew(GetExpressCheckoutDetailsRequestBuilder::class);
        $request = $builder->getPayPalRequest();
        $request->setParameter('TOKEN', $paypalToken);

        $payPalService = oxNew(PayPalService::class);
        $response      = $payPalService->getExpressCheckoutDetails($request);

        return (string) $response->getPayerId();
    }

    public function prepareCheckoutUser(string $paypalToken): ?EshopUserModel
    {
        EshopRegistry::getSession()->setVariable('oepaypal-token', $paypalToken);

        $dispatcher = oxNew(ExpressCheckoutDispatcher::class);
        $next       = $dispatcher->getExpressCheckoutDetails();

        $user = null;

        if ('order' == $next) {
            $user = EshopRegistry::getSession()->getUser();
        }

        return $user;
    }

    public function setUserInformationToBasket(string $basketId, string $userId, string $deliveryAddressId): BasketDataType
    {
        /** @var EshopUserBasketModel $userBasket */
        $userBasket = oxNew(EshopUserBasketModel::class);
        $userBasket->load($basketId);

        if ($userBasket->load($basketId) &&
            ( empty($userBasket->getFieldData('oxuserid')) ||
              $userId == $userBasket->getFieldData('oxuserid') )
        ) {
            $userBasket->assign(
                [
                    'OXUSERID'           => $userId,
                    'OEGQL_DELADDRESSID' => $deliveryAddressId,
                ]
            );
            $userBasket->save();
        }

        return new BasketDataType($userBasket);
    }

    private function createEshopBasket(BasketDataType $userBasket): EshopBasketModel
    {
        /** @var EshopUserBasketModel $userBasketModel */
        $userBasketModel = $userBasket->getEshopModel();
        $items           = $userBasketModel->getItems();

        /** @var EshopBasketModel $basket */
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
}
