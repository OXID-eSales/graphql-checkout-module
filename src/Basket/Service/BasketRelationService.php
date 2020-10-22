<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Basket\Service;

use OxidEsales\GraphQL\Account\Address\DataType\DeliveryAddress;
use OxidEsales\GraphQL\Account\Address\Exception\DeliveryAddressNotFound;
use OxidEsales\GraphQL\Account\Address\Service\DeliveryAddress as DeliveryAddressService;
use OxidEsales\GraphQL\Account\Basket\DataType\Basket;
use OxidEsales\GraphQL\Account\Payment\DataType\Payment;
use OxidEsales\GraphQL\Account\Payment\Exception\PaymentNotFound;
use OxidEsales\GraphQL\Account\Payment\Service\Payment as PaymentService;
use OxidEsales\GraphQL\Checkout\DeliveryMethod\DataType\DeliveryMethod;
use OxidEsales\GraphQL\Checkout\DeliveryMethod\Exception\DeliveryMethodNotFound;
use OxidEsales\GraphQL\Checkout\DeliveryMethod\Service\DeliveryMethod as DeliveryMethodService;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;

/**
 * @ExtendType(class=Basket::class)
 */
final class BasketRelationService
{
    /** @var DeliveryAddressService */
    private $deliveryAddressService;

    /** @var PaymentService */
    private $paymentService;

    /** @var DeliveryMethodService */
    private $deliveryMethodService;

    public function __construct(
        DeliveryAddressService $deliveryAddressService,
        PaymentService $paymentService,
        DeliveryMethodService $deliveryMethodService
    ) {
        $this->deliveryAddressService = $deliveryAddressService;
        $this->paymentService         = $paymentService;
        $this->deliveryMethodService  = $deliveryMethodService;
    }

    /**
     * @Field()
     */
    public function deliveryAddress(Basket $basket): ?DeliveryAddress
    {
        $addressId = (string) $basket->getEshopModel()->getFieldData('oegql_deladdressid');

        if (empty($addressId)) {
            return null;
        }

        try {
            $deliveryAddress = $this->deliveryAddressService->getDeliveryAddress($addressId);
        } catch (DeliveryAddressNotFound $e) {
            $deliveryAddress = null;
        }

        return $deliveryAddress;
    }

    /**
     * Returns selected payment for current basket.
     *
     * @Field()
     */
    public function payment(Basket $basket): ?Payment
    {
        $paymentId = (string) $basket->getEshopModel()->getFieldData('oegql_paymentid');

        if (empty($paymentId)) {
            return null;
        }

        try {
            $payment = $this->paymentService->payment($paymentId);
        } catch (PaymentNotFound $e) {
            $payment = null;
        }

        return $payment;
    }

    /**
     * Returns selected delivery method for current basket.
     *
     * @Field()
     */
    public function deliveryMethod(Basket $basket): ?DeliveryMethod
    {
        $deliveryMethodId = (string) $basket->getEshopModel()->getFieldData('oegql_deliverymethodid');

        if (empty($deliveryMethodId)) {
            return null;
        }

        try {
            $deliveryMethod = $this->deliveryMethodService->getDeliveryMethod($deliveryMethodId);
        } catch (DeliveryMethodNotFound $e) {
            $deliveryMethod = null;
        }

        return $deliveryMethod;
    }
}
