<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Klarna\Controller;

use OxidEsales\Eshop\Application\Model\Basket as EshopBasketModel;
use OxidEsales\Eshop\Application\Model\User as EshopUserModel;
use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\GraphQL\Account\Basket\DataType\Basket as BasketDataType;
use OxidEsales\GraphQL\Account\Basket\Service\Basket as AccountBasketService;
use OxidEsales\GraphQL\Account\Country\DataType\Country;
use OxidEsales\GraphQL\Account\Customer\Service\Customer;
use OxidEsales\GraphQL\Account\Shared\Infrastructure\Basket as SharedBasketInfrastructure;
use OxidEsales\GraphQL\Checkout\Basket\Service\Basket as CheckoutBasketService;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TopConcepts\Klarna\Core\Exception\KlarnaClientException;
use TopConcepts\Klarna\Core\Exception\KlarnaWrongCredentialsException;
use TopConcepts\Klarna\Core\KlarnaPayment;
use TopConcepts\Klarna\Core\KlarnaPaymentsClient;
use TopConcepts\Klarna\Core\KlarnaUtils;

class KlarnaPayments
{
    /**
     * This should be executed on loading of payment/3rd step of checkout.
     *
     * @Query()
     * @Logged()
     *
     * @return string[]
     */
    public function klarnaPaymentsToken(string $basketId): array
    {
        if (!KlarnaUtils::isKlarnaPaymentsEnabled()) {
            throw new \Exception('Klarna Payments are not enabled!');
        }

        /** @var BasketDataType $basketDataType */
        $basketDataType = ContainerFactory::getInstance()
            ->getContainer()
            ->get(AccountBasketService::class)
            ->getAuthenticatedCustomerBasket($basketId);

        /** @var EshopBasketModel $basketModel */
        $basketModel = ContainerFactory::getInstance()
            ->getContainer()
            ->get(SharedBasketInfrastructure::class)
            ->getCalculatedBasket($basketDataType);

        /** @var \OxidEsales\GraphQL\Account\Customer\DataType\Customer $customerDataType */
        $customerDataType = ContainerFactory::getInstance()
            ->getContainer()
            ->get(Customer::class)
            ->customer($basketDataType->getUserId()->val());

        /** @var EshopUserModel $userModel */
        $userModel = $customerDataType->getEshopModel();

        if (KlarnaPayment::countryWasChanged($userModel)) {
            KlarnaPayment::cleanUpSession();
        }

        /** @var KlarnaPayment $klarnaPayment */
        $klarnaPayment = oxNew(KlarnaPayment::class, $basketModel, $userModel);
        if (!$klarnaPayment->isSessionValid()) {
            KlarnaPayment::cleanUpSession();
        }

        $errors = $klarnaPayment->getError();
        if ($errors) {
            throw new \Exception('KLARNA ERRORS: ' . $errors);
        }

        /** @var Country $countryDataType */
        $countryDataType = ContainerFactory::getInstance()
            ->getContainer()
            ->get(CheckoutBasketService::class)
            ->getBasketDeliveryCountry($basketDataType);

        try {
            $client = KlarnaPaymentsClient::getInstance($countryDataType->getIsoAlpha2());
            $klarnaResponse = $client->initOrder($klarnaPayment)->createOrUpdateSession();
        } catch (KlarnaWrongCredentialsException $oEx) {
            KlarnaUtils::fullyResetKlarnaSession();
            throw $oEx;
        } catch (KlarnaClientException $e) {
            throw $e;
        }

        $previousRequestedData = EshopRegistry::getSession()->getVariable('klarna_session_data');

        return [
            'sessionId' => $klarnaResponse['session_id'] ?? $previousRequestedData['session_id'],
            'token' => $klarnaResponse['client_token'] ?? $previousRequestedData['client_token'],
            'klarnaPayments' => serialize($klarnaResponse['payment_method_categories'] ?? $previousRequestedData['payment_method_categories']),
        ];
    }

    /**
     * This should be executed after selecting a payment.
     *
     * @Query()
     * @Logged()
     *
     * @return string[]
     */
    public function klarnaPaymentsSetPaymentId(string $basketId, string $token): array
    {
        if (!KlarnaUtils::isKlarnaPaymentsEnabled()) {
            throw new \Exception('Klarna Payments are not enabled!');
        }

        /** @var BasketDataType $basketDataType */
        $basketDataType = ContainerFactory::getInstance()
            ->getContainer()
            ->get(AccountBasketService::class)
            ->getAuthenticatedCustomerBasket($basketId);

        /** @var EshopBasketModel $basketModel */
        $basketModel = ContainerFactory::getInstance()
            ->getContainer()
            ->get(SharedBasketInfrastructure::class)
            ->getCalculatedBasket($basketDataType);

        /** @var \OxidEsales\GraphQL\Account\Customer\DataType\Customer $customerDataType */
        $customerDataType = ContainerFactory::getInstance()
            ->getContainer()
            ->get(Customer::class)
            ->customer($basketDataType->getUserId()->val());

        /** @var EshopUserModel $userModel */
        $userModel = $customerDataType->getEshopModel();

        // Recreate token
        if (KlarnaPayment::countryWasChanged($userModel)) {
            KlarnaPayment::cleanUpSession();
            $this->klarnaPaymentsToken($basketId);
        }

        $payload = [
            'action' => 'addUserData',
            'paymentId' => $basketDataType->getEshopModel()->getFieldData('oegql_paymentid'),
        ];

        /** @var  $klarnaPayment KlarnaPayment */
        $klarnaPayment = oxNew(KlarnaPayment::class, $basketModel, $userModel, $payload);

        // Recreate token
        if (!$klarnaPayment->isSessionValid() || !$klarnaPayment->validateClientToken($token)) {
            KlarnaPayment::cleanUpSession();
            $this->klarnaPaymentsToken($basketId);
        }

        $responseData = [];
        $responseData['update'] = $klarnaPayment->getChangedData();
        $savedCheckSums = $klarnaPayment->fetchCheckSums();
        if ($savedCheckSums['_aUserData'] === false) {
            $klarnaPayment->setCheckSum('_aUserData', true);
        }

        /** @var Country $countryDataType */
        $countryDataType = ContainerFactory::getInstance()
            ->getContainer()
            ->get(CheckoutBasketService::class)
            ->getBasketDeliveryCountry($basketDataType);

        $client = KlarnaPaymentsClient::getInstance($countryDataType->getIsoAlpha2());
        $client->initOrder($klarnaPayment)->createOrUpdateSession();

        return [
            'action' => __METHOD__,
            'status' => 'updateUser',
            'data' => serialize($responseData),
        ];
    }

    /**
     * This should be executed on clicking on next step btn.
     *
     * @Query()
     * @Logged()
     *
     * @return string[]
     */
    public function klarnaPaymentsCheckOrder(string $basketId, string $token): array
    {
        if (!KlarnaUtils::isKlarnaPaymentsEnabled()) {
            throw new \Exception('Klarna Payments are not enabled!');
        }

        /** @var BasketDataType $basketDataType */
        $basketDataType = ContainerFactory::getInstance()
            ->getContainer()
            ->get(AccountBasketService::class)
            ->getAuthenticatedCustomerBasket($basketId);

        /** @var EshopBasketModel $basketModel */
        $basketModel = ContainerFactory::getInstance()
            ->getContainer()
            ->get(SharedBasketInfrastructure::class)
            ->getCalculatedBasket($basketDataType);

        /** @var \OxidEsales\GraphQL\Account\Customer\DataType\Customer $customerDataType */
        $customerDataType = ContainerFactory::getInstance()
            ->getContainer()
            ->get(Customer::class)
            ->customer($basketDataType->getUserId()->val());

        /** @var EshopUserModel $userModel */
        $userModel = $customerDataType->getEshopModel();

        // Recreate token
        if (KlarnaPayment::countryWasChanged($userModel)) {
            KlarnaPayment::cleanUpSession();
            $this->klarnaPaymentsToken($basketId);
        }

        $payload = [
            'action' => 'checkOrderStatus',
            'paymentId' => $basketDataType->getEshopModel()->getFieldData('oegql_paymentid'),
        ];

        /** @var KlarnaPayment $klarnaPayment */
        $klarnaPayment = oxNew(KlarnaPayment::class, $basketModel, $userModel, $payload);

        // Recreate token
        if (!$klarnaPayment->isSessionValid() || !$klarnaPayment->validateClientToken($token)) {
            KlarnaPayment::cleanUpSession();
            $this->klarnaPaymentsToken($basketId);
        }

        $klarnaPayment->setStatus('submit');

        if ($klarnaPayment->isAuthorized()) {
            $this->handleAuthorizedPayment($klarnaPayment);
        } else {
            $klarnaPayment->setStatus('authorize');
        }

        if ($klarnaPayment->paymentChanged) {
            $klarnaPayment->setStatus('authorize');
            EshopRegistry::getSession()->deleteVariable('sAuthToken');
            EshopRegistry::getSession()->deleteVariable('finalizeRequired');
        }

        /** @var Country $countryDataType */
        $countryDataType = ContainerFactory::getInstance()
            ->getContainer()
            ->get(CheckoutBasketService::class)
            ->getBasketDeliveryCountry($basketDataType);

        $client = KlarnaPaymentsClient::getInstance($countryDataType->getIsoAlpha2());
        $client->initOrder($klarnaPayment)->createOrUpdateSession();

        return [
            'action' => __METHOD__,
            'status' => $klarnaPayment->getStatus(),
            'data' => serialize([
                'update' => $payload,
                'paymentMethod' => $klarnaPayment->getPaymentMethodCategory(),
                'refreshUrl' => $klarnaPayment->refreshUrl,
            ]),
        ];
    }

    /**
     * @param KlarnaPayment $klarnaPayment
     */
    protected function handleAuthorizedPayment(KlarnaPayment &$klarnaPayment)
    {
        $reauthorizeRequired = EshopRegistry::getSession()->getVariable('reauthorizeRequired');

        if ($reauthorizeRequired || $klarnaPayment->isOrderStateChanged() || !$klarnaPayment->isTokenValid()) {
            $klarnaPayment->setStatus('reauthorize');
            EshopRegistry::getSession()->deleteVariable('reauthorizeRequired');

        } else if ($klarnaPayment->requiresFinalization()) {
            $klarnaPayment->setStatus('finalize');
        }
    }

    /**
     * This should be executed on clicking on order btn.
     *
     * @Query()
     * @Logged()
     *
     * @return string[]
     */
    public function klarnaPaymentsCheckOrderBeforeOrder(string $basketId, string $token): array
    {
        if (!KlarnaUtils::isKlarnaPaymentsEnabled()) {
            throw new \Exception('Klarna Payments are not enabled!');
        }

        /** @var BasketDataType $basketDataType */
        $basketDataType = ContainerFactory::getInstance()
            ->getContainer()
            ->get(AccountBasketService::class)
            ->getAuthenticatedCustomerBasket($basketId);

        EshopRegistry::getSession()->setVariable('paymentid', $basketDataType->getEshopModel()->getFieldData('oegql_paymentid'));

        /** @var EshopBasketModel $basketModel */
        $basketModel = ContainerFactory::getInstance()
            ->getContainer()
            ->get(SharedBasketInfrastructure::class)
            ->getCalculatedBasket($basketDataType);

        /** @var \OxidEsales\GraphQL\Account\Customer\DataType\Customer $customerDataType */
        $customerDataType = ContainerFactory::getInstance()
            ->getContainer()
            ->get(Customer::class)
            ->customer($basketDataType->getUserId()->val());

        /** @var EshopUserModel $userModel */
        $userModel = $customerDataType->getEshopModel();

        // Recreate token
        if (KlarnaPayment::countryWasChanged($userModel)) {
            KlarnaPayment::cleanUpSession();
            $this->klarnaPaymentsToken($basketId);
        }

        $payload = [
            'action' => 'checkOrderStatus',
        ];

        /** @var KlarnaPayment $klarnaPayment */
        $klarnaPayment = oxNew(KlarnaPayment::class, $basketModel, $userModel, $payload);

        // Recreate token
        if (!$klarnaPayment->isSessionValid() || !$klarnaPayment->validateClientToken($token)) {
            KlarnaPayment::cleanUpSession();
            $this->klarnaPaymentsToken($basketId);
        }

        $klarnaPayment->setStatus('submit');

        if ($klarnaPayment->isAuthorized()) {
            $this->handleAuthorizedPayment($klarnaPayment);
        } else {
            $klarnaPayment->setStatus('authorize');
        }

        if ($klarnaPayment->paymentChanged) {
            $klarnaPayment->setStatus('authorize');
            EshopRegistry::getSession()->deleteVariable('sAuthToken');
            EshopRegistry::getSession()->deleteVariable('finalizeRequired');
        }

        /** @var Country $countryDataType */
        $countryDataType = ContainerFactory::getInstance()
            ->getContainer()
            ->get(CheckoutBasketService::class)
            ->getBasketDeliveryCountry($basketDataType);

        $client = KlarnaPaymentsClient::getInstance($countryDataType->getIsoAlpha2());
        $client->initOrder($klarnaPayment)->createOrUpdateSession();

        return [
            'action' => __METHOD__,
            'status' => $klarnaPayment->getStatus(),
            'data' => serialize([
                'update' => $payload,
                'paymentMethod' => $klarnaPayment->getPaymentMethodCategory(),
                'refreshUrl' => $klarnaPayment->refreshUrl,
            ]),
        ];
    }
}
