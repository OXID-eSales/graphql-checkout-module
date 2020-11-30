<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Basket\Infrastructure;

use Exception;
use OxidEsales\Eshop\Application\Controller\PaymentController as EshopPaymentController;
use OxidEsales\Eshop\Application\Model\Address as EshopAddressModel;
use OxidEsales\Eshop\Application\Model\Basket as EshopBasketModel;
use OxidEsales\Eshop\Application\Model\DeliverySet as EshopDeliverySetModel;
use OxidEsales\Eshop\Application\Model\DeliverySetList as EshopDeliverySetListModel;
use OxidEsales\Eshop\Application\Model\Order as OrderModel;
use OxidEsales\Eshop\Application\Model\User as EshopUserModel;
use OxidEsales\Eshop\Application\Model\UserBasket as EshopUserBasketModel;
use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\GraphQL\Account\Basket\DataType\Basket as BasketDataType;
use OxidEsales\GraphQL\Account\Basket\Infrastructure\Basket as AccountBasketUnsharedInfrastructure;
use OxidEsales\GraphQL\Account\Country\DataType\Country as CountryDataType;
use OxidEsales\GraphQL\Account\Customer\DataType\Customer as CustomerDataType;
use OxidEsales\GraphQL\Account\Order\DataType\Order as OrderDataType;
use OxidEsales\GraphQL\Account\Shared\Infrastructure\Basket as AccountBasketInfrastructure;
use OxidEsales\GraphQL\Base\Infrastructure\Legacy as LegacyService;
use OxidEsales\GraphQL\Catalogue\Shared\Infrastructure\Repository;
use OxidEsales\GraphQL\Checkout\Basket\DataType\PayPalBasket as PayPalBasketDataType;
use OxidEsales\GraphQL\Checkout\Basket\Exception\PayPalExpressCheckoutException;
use OxidEsales\GraphQL\Checkout\Basket\Exception\PlaceOrder as PlaceOrderException;
use OxidEsales\GraphQL\Checkout\Basket\Infrastructure\PayPal as PayPalInfrastructure;
use OxidEsales\GraphQL\Checkout\DeliveryMethod\DataType\DeliveryMethod as DeliveryMethodDataType;
use OxidEsales\GraphQL\Checkout\Payment\DataType\BasketPayment;

final class Basket
{
    /** @var Repository */
    private $repository;

    /** @var AccountBasketInfrastructure */
    private $accountBasketInfrastructure;

    /** @var LegacyService */
    private $legacyService;

    /**
     * @var AccountBasketUnsharedInfrastructure
     */
    private $accountBasketForPP;

    public function __construct(
        Repository $repository,
        AccountBasketInfrastructure $accountBasketInfrastructure,
        LegacyService $legacyService,
        AccountBasketUnsharedInfrastructure $accountBasketForPP
    ) {
        $this->repository                  = $repository;
        $this->accountBasketInfrastructure = $accountBasketInfrastructure;
        $this->legacyService               = $legacyService;
        $this->accountBasketForPP          = $accountBasketForPP;
    }

    public function setDeliveryAddress(BasketDataType $basket, string $deliveryAddressId): bool
    {
        $model = $basket->getEshopModel();

        $model->assign([
            'OEGQL_DELADDRESSID' => $deliveryAddressId,
        ]);

        return $this->repository->saveModel($model);
    }

    public function setPayment(BasketDataType $basket, string $paymentId, string $additionalInfo = ''): bool
    {
        $model = $basket->getEshopModel();

        $model->assign([
            'OEGQL_PAYMENTID'      => $paymentId,
            'OEGQL_PAYMENTDYNDATA' => $additionalInfo,
        ]);

        return $this->repository->saveModel($model);
    }

    public function validatePaymentForBasket(BasketDataType $userBasket, string $paymentId, string $additionalInfo): bool
    {
        $result = 'order';

        /** @var EshopBasketModel $basketModel */
        $basketModel = $this->accountBasketInfrastructure->getCalculatedBasket($userBasket);

        $addInfo = @unserialize(base64_decode($additionalInfo));
        $addInfo = is_array($addInfo) ?: [];
        EshopRegistry::getSession()->setVariable('dynvalue', $addInfo);
        EshopRegistry::getSession()->setVariable('paymentid', $paymentId);
        EshopRegistry::getSession()->setVariable('usr', $basketModel->getUser()->getId());
        EshopRegistry::getSession()->setUser($basketModel->getUser());

        $controller = oxNew(EshopPaymentController::class);

        if (method_exists($controller, 'fcpoPaymentActive')) {
            $result = (string) $controller->validatePaymentForGraphql($paymentId);
        }

        return (bool) ('order' == $result);
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
        EshopRegistry::getSession()->setUser($userModel);
        EshopRegistry::getSession()->setVariable('usr', $userModel->getId());
        $basketModel     = $this->accountBasketInfrastructure->getCalculatedBasket($userBasket);

        //Initialize available delivery set list for user and country
        /** @var EshopDeliverySetListModel $deliverySetList */
        $deliverySetList      = oxNew(EshopDeliverySetListModel::class);
        $deliverySetListArray = $deliverySetList->getDeliverySetList($userModel, (string) $country->getId());

        $result = [];

        $this->userBoniCheck($userModel);
        $forbiddenPaymentIds = $this->getForbiddenPaymentsList($userModel);

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
                if (!in_array($paymentModel->getId(), $forbiddenPaymentIds)) {
                    $deliveryMethodPayments[$paymentModel->getId()] = new BasketPayment($paymentModel, $basketModel);
                }
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

        //put dynvalues into session if we have some stored
        $addInfo = @unserialize(base64_decode($userBasketModel->getFieldData('OEGQL_PAYMENTDYNDATA')));
        EshopRegistry::getSession()->setVariable('dynvalue', $addInfo);
        $_POST['dynvalue'] = $addInfo;

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
            throw PlaceOrderException::byBasketId($userBasketModel->getId(), (string) $status);
        }

        //return order data type
        return new OrderDataType($orderModel);
    }

    public function paypalExpress(string $productId, int $amount): PayPalBasketDataType
    {
        //we create an oxuserbasket entry without name and userid
        /** @var EshopUserBasketModel $userBasketModel */
        $userBasketModel = oxNew(EshopUserBasketModel::class);
        $userBasketModel->assign(
            [
                'OEGQL_DELIVERYMETHODID' => 'oxidstandard',
                'OEGQL_PAYMENTID'        => 'oxidpaypal',
            ]
        );
        $userBasketModel->save();
        $userBasket = new BasketDataType($userBasketModel);
        $this->accountBasketForPP->addProduct($userBasket, $productId, $amount);

        $paypal      = new PayPalInfrastructure();
        $paypalToken = $paypal->expressCheckout($userBasket);

        $userBasketModel->assign(
            [
                'OEGQL_PAYPALTOKEN' => $paypalToken,
            ]
        );
        $userBasketModel->save();

        $url = $paypal->getPayPalCommunicationUrl($paypalToken, 'continue');

        return new PayPalBasketDataType($userBasket, $url, $paypalToken);
    }

    public function ensurePayPalExpressUser(BasketDataType $userBasket, string $paypalToken, string $payerId): CustomerDataType
    {
        $paypal            = new PayPalInfrastructure();
        $payerIdFromPayPal = $paypal->getPaypalPayerId($paypalToken);

        if ($payerId !== $payerIdFromPayPal) {
            throw PayPalExpressCheckoutException::byToken($paypalToken);
        }

        $savedUserId = $userBasket->getEshopModel()->getFieldData('oxuserid');

        if ($savedUserId) {
            /** @var EshopUserModel $sessionUser */
            $sessionUser = oxNew(EshopUserModel::class);
            $sessionUser->load($savedUserId);
            EshopRegistry::getSession()->setUser($sessionUser);
            EshopRegistry::getSession()->setVariable('usr', $savedUserId);
        }

        //at this point we need that session basket, otherwise the check for basket costs will not pass
        /** @var EshopBasketModel $basketModel */
        $basketModel = $this->accountBasketInfrastructure->getBasket($userBasket);
        EshopRegistry::getSession()->setBasket($basketModel);
        EshopRegistry::getSession()->setVariable('paymentid', 'oxidpaypal'); //needed for payment gateway

        /** @var EshopUserModel $user */
        $user = $paypal->prepareCheckoutUser($paypalToken);

        if (!$user) {
            throw PayPalExpressCheckoutException::byUser($paypalToken);
        }

        return new CustomerDataType($user);
    }

    public function setUserInformationToBasket(string $basketId, string $userId): BasketDataType
    {
        $deliveryAddressId = (string) EshopRegistry::getSession()->getVariable('deladrid');

        $paypal = new PayPalInfrastructure();

        return $paypal->setUserInformationToBasket($basketId, $userId, $deliveryAddressId);
    }

    /**
     * @throws Exception
     */
    private function userBoniCheck(EshopUserModel $userModel): void
    {
        $canContinue = true;

        if (method_exists($userModel, 'checkAddressAndScore')) {
            if ('after' != $this->legacyService->getConfigParam('sFCPOBonicheckMoment')) {
                $canContinue = $userModel->checkAddressAndScore();
            } else {
                $canContinue = $userModel->checkAddressAndScore(true, false);
            }
        }

        if (!$canContinue) {
            throw new Exception('Cannot continue, stopped by bonicheck.');
        }
    }

    private function getForbiddenPaymentsList(EshopUserModel $userModel): array
    {
        $forbiddenPaymentIds = [];

        //finetuning as to when the boni score is checked can be implemented later
        if (method_exists($userModel, 'fcpoGetForbiddenPaymentIds')) {
            $forbiddenPaymentIds = $userModel->fcpoGetForbiddenPaymentIds();
        }

        return $forbiddenPaymentIds;
    }
}
