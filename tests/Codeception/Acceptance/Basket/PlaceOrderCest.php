<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Tests\Codeception\Acceptance\Basket;

use Codeception\Scenario;
use Codeception\Util\HttpCode;
use OxidEsales\GraphQL\Checkout\Tests\Codeception\Acceptance\BaseCest;
use OxidEsales\GraphQL\Checkout\Tests\Codeception\AcceptanceTester;
use TheCodingMachine\GraphQLite\Types\ID;

/**
 * @group oe_graphql_checkout
 * @group place_order
 * @group basket
 */
final class PlaceOrderCest extends BaseCest
{
    private const USERNAME = 'user@oxid-esales.com';

    private const CHECKOUT_USERNAME = 'checkoutuser@oxid-esales.com';

    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const PRODUCT_ID = 'dc5ffdf380e15674b56dd562a7cb6aec';

    private const SHIPPING_STANDARD = 'oxidstandard';

    private const TEST_SHIPPING = '_deliveryset';

    private const PAYMENT_STANDARD = 'oxidcashondel';

    private const PAYMENT_TEST = 'oxidgraphql';

    private const EMPTY_BASKET_NAME = 'my_empty_cart';

    private const DEFAULT_SAVEDBASKET = 'savedbasket';

    private const ALTERNATE_COUNTRY = 'a7c40f632a0804ab5.18804076';

    public function _before(AcceptanceTester $I, Scenario $scenario): void
    {
        parent::_before($I, $scenario);

        $I->updateConfigInDatabase('blPerfNoBasketSaving', false, 'bool');
    }

    public function placeOrderUsingInvoiceAddress(AcceptanceTester $I): void
    {
        $I->wantToTest('placing an order successfully with invoice address only');
        $I->login(self::USERNAME, self::PASSWORD);

        //prepare basket
        $basketId = $this->createBasket($I, 'my_cart_one');
        $this->addProductToBasket($I, $basketId, self::PRODUCT_ID, 2);
        $this->setBasketDeliveryMethod($I, $basketId, self::SHIPPING_STANDARD);
        $this->setBasketPaymentMethod($I, $basketId, self::PAYMENT_STANDARD);

        //place the order
        $result  = $this->placeOrder($I, $basketId);
        $orderId = $result['data']['placeOrder']['id'];

        //check order history
        $orders = $this->getOrderFromOrderHistory($I);
        $I->assertEquals($orders['id'], $orderId);
        $I->assertNotEmpty($orders['invoiceAddress']);
        $I->assertNull($orders['deliveryAddress']);

        //remove basket
        $this->removeBasket($I, $basketId, self::USERNAME);
    }

    public function placeOrderUsingInvoiceAddressAndDefaultSavedBasket(AcceptanceTester $I): void
    {
        $I->wantToTest('placing an order for savedbasket and blPerfNoBasketSaving');

        $I->updateConfigInDatabase('blPerfNoBasketSaving', true, 'bool');
        $I->login(self::CHECKOUT_USERNAME, self::PASSWORD);

        //prepare basket
        $basketId = $this->createBasket($I, self::DEFAULT_SAVEDBASKET);
        $this->addProductToBasket($I, $basketId, self::PRODUCT_ID, 2);
        $this->setBasketDeliveryMethod($I, $basketId, self::TEST_SHIPPING);
        $this->setBasketPaymentMethod($I, $basketId, self::PAYMENT_TEST);

        //place the order
        $result  = $this->placeOrder($I, $basketId);
        $orderId = $result['data']['placeOrder']['id'];

        //check order history
        $orders = $this->getOrderFromOrderHistory($I);
        $I->assertEquals($orders['id'], $orderId);
        $I->assertNotEmpty($orders['invoiceAddress']);
        $I->assertNull($orders['deliveryAddress']);

        //remove basket
        $this->removeBasket($I, $basketId, self::CHECKOUT_USERNAME);
    }

    public function placeOrderUsingDeliveryAddress(AcceptanceTester $I): void
    {
        $I->wantToTest('placing an order successfully with delivery address');
        $I->login(self::USERNAME, self::PASSWORD);

        //prepare basket
        $basketId = $this->createBasket($I, 'my_cart_two');
        $this->addProductToBasket($I, $basketId, self::PRODUCT_ID, 2);
        $this->setBasketDeliveryAddress($I, $basketId);
        $this->setBasketDeliveryMethod($I, $basketId, self::SHIPPING_STANDARD);
        $this->setBasketPaymentMethod($I, $basketId, self::PAYMENT_STANDARD);

        //place the order
        $result  = $this->placeOrder($I, $basketId);
        $orderId = $result['data']['placeOrder']['id'];

        //check order history
        $orders = $this->getOrderFromOrderHistory($I);
        $I->assertEquals($orders['id'], $orderId);
        $I->assertNotEmpty($orders['invoiceAddress']);
        $I->assertNotEmpty($orders['deliveryAddress']);

        //remove basket
        $this->removeBasket($I, $basketId, self::USERNAME);
    }

    public function placeOrderWithoutToken(AcceptanceTester $I): void
    {
        $I->wantToTest('placing an order when logged out');
        $I->login(self::USERNAME, self::PASSWORD);

        //prepare basket
        $basketId = $this->createBasket($I, 'my_cart_three');
        $this->addProductToBasket($I, $basketId, self::PRODUCT_ID, 2);
        $this->setBasketDeliveryMethod($I, $basketId, self::SHIPPING_STANDARD);
        $this->setBasketPaymentMethod($I, $basketId, self::PAYMENT_STANDARD);

        //log out
        $I->logout();

        //place the order
        $this->placeOrder($I, $basketId, HttpCode::BAD_REQUEST);

        //remove basket
        $this->removeBasket($I, $basketId, self::USERNAME);
    }

    public function placeOtherUsersOrder(AcceptanceTester $I): void
    {
        $I->wantToTest('placing another users order');
        $I->login(self::USERNAME, self::PASSWORD);

        //prepare basket
        $basketId = $this->createBasket($I, 'my_cart_four');
        $this->addProductToBasket($I, $basketId, self::PRODUCT_ID, 2);
        $this->setBasketDeliveryMethod($I, $basketId, self::SHIPPING_STANDARD);
        $this->setBasketPaymentMethod($I, $basketId, self::PAYMENT_STANDARD);

        //log out
        $I->logout();

        //log in different user and place the order
        $I->login(self::OTHER_USERNAME, self::PASSWORD);
        $this->placeOrder($I, $basketId, HttpCode::UNAUTHORIZED);

        //remove basket
        $this->removeBasket($I, $basketId, self::USERNAME);
    }

    public function placeOrderWithEmptyBasket(AcceptanceTester $I): void
    {
        $I->wantToTest('that placing an order with empty basket fails');
        $I->login(self::USERNAME, self::PASSWORD);

        //prepare basket
        $basketId = $this->createBasket($I, self::EMPTY_BASKET_NAME);
        $this->setBasketDeliveryMethod($I, $basketId, self::SHIPPING_STANDARD);
        $this->setBasketPaymentMethod($I, $basketId, self::PAYMENT_STANDARD);

        //place the order
        $result = $this->placeOrder($I, $basketId, HttpCode::BAD_REQUEST);

        //remove basket
        $this->removeBasket($I, $basketId, self::USERNAME);
    }

    public function prepareOrderWithNoShippingMethodForCountry(AcceptanceTester $I): void
    {
        $I->wantToTest('that using delivery address with unsupported country fails');
        $I->login(self::USERNAME, self::PASSWORD);

        //prepare basket with invoice address
        $basketId = $this->createBasket($I, 'my_cart_five');
        $this->addProductToBasket($I, $basketId, self::PRODUCT_ID, 3);
        $this->setBasketDeliveryAddress($I, $basketId, self::ALTERNATE_COUNTRY);

        //shipping method not supported
        $this->setBasketDeliveryMethod($I, $basketId, self::SHIPPING_STANDARD, HttpCode::BAD_REQUEST);

        //remove basket
        $this->removeBasket($I, $basketId, self::USERNAME);
    }

    public function placeOrderWithChangedDeliveryAddress(AcceptanceTester $I): void
    {
        $I->wantToTest('that placing an order with changed delivery address fails');
        $I->login(self::USERNAME, self::PASSWORD);

        //prepare basket with german delivery address
        $basketId = $this->createBasket($I, 'my_cart_six');
        $this->addProductToBasket($I, $basketId, self::PRODUCT_ID, 3);
        $this->setBasketDeliveryAddress($I, $basketId); //Germany
        $this->setBasketDeliveryMethod($I, $basketId, self::SHIPPING_STANDARD);
        $this->setBasketPaymentMethod($I, $basketId, self::PAYMENT_STANDARD);

        //this country is not supported for chosen shipping method
        $this->setBasketDeliveryAddress($I, $basketId, self::ALTERNATE_COUNTRY);

        //place the order
        $this->placeOrder($I, $basketId, HttpCode::BAD_REQUEST);

        //remove basket
        $this->removeBasket($I, $basketId, self::USERNAME);
    }

    public function placeOrderWithUnavailablePayment(AcceptanceTester $I): void
    {
        $I->wantToTest('that placing an order with unavailable payment fails');
        $I->login(self::USERNAME, self::PASSWORD);

        $I->wantToTest('that placing an order with changed delivery address fails');
        $I->login(self::USERNAME, self::PASSWORD);

        //prepare basket with invoice address
        $basketId = $this->createBasket($I, 'my_cart_seven');
        $this->addProductToBasket($I, $basketId, self::PRODUCT_ID, 3);
        $this->setBasketDeliveryMethod($I, $basketId, self::SHIPPING_STANDARD);
        $this->setBasketPaymentMethod($I, $basketId, self::PAYMENT_STANDARD);

        $I->updateInDatabase('oxuserbaskets', ['oegql_paymentid' => self::PAYMENT_TEST], ['oxid' => $basketId]);

        //place the order
        $result = $this->placeOrder($I, $basketId, HttpCode::BAD_REQUEST);

        //remove basket
        $this->removeBasket($I, $basketId, self::USERNAME);
    }

    public function placeOrderWithVouchers(AcceptanceTester $I): void
    {
        $I->wantToTest('placing an order with vouchers');
        $I->login(self::USERNAME, self::PASSWORD);

        // add voucherSeries and voucher to database
        $this->createVoucher($I);

        //prepare basket
        $basketId = $this->createBasket($I, 'cart_with_voucher');
        $this->addProductToBasket($I, $basketId, self::PRODUCT_ID, 1);
        $this->setBasketDeliveryMethod($I, $basketId, self::SHIPPING_STANDARD);
        $this->setBasketPaymentMethod($I, $basketId, self::PAYMENT_STANDARD);
        $this->addVoucherToBasket($I, $basketId, 'voucher1');

        //place the order
        $result  = $this->placeOrder($I, $basketId);
        $orderId = $result['data']['placeOrder']['id'];

        //check order history
        $orders = $this->getOrderFromOrderHistory($I);
        $I->assertEquals($orders['id'], $orderId);
        $I->assertEquals($orders['vouchers'][0]['id'], 'voucher1id');
        $I->assertNotEmpty($orders['invoiceAddress']);
        $I->assertNull($orders['deliveryAddress']);

        //remove basket
        $this->removeBasket($I, $basketId, self::USERNAME);
    }

    public function placeOrderWithDiscounts(): void
    {
        //TODO
    }

    public function placeOrderAndNoCalculateDelCostIfNotLoggedIn(): void
    {
        //TODO: blCalculateDelCostIfNotLoggedIn
    }

    public function placeOrderWithBasketReservation(): void
    {
        //TODO: blPsBasketReservationEnabled
    }

    public function placeOrderWithConfirmAGB(): void
    {
        //TODO: blConfirmAGB
    }

    public function placeOrderWithDownloadableProduct(): void
    {
        //TODO:
    }

    public function placeOrderWithBelowMinPriceBasket(): void
    {
        //TODO:
    }

    public function placeOrderOnOutOfStockNotBuyableProduct(AcceptanceTester $I): void
    {
        $I->wantToTest('placing an order on a product which is out of stock or not buyable');
        $I->login(self::USERNAME, self::PASSWORD);

        //prepare basket
        $basketId = $this->createBasket($I, 'cart_with_not_buyable_product');
        $this->addProductToBasket($I, $basketId, self::PRODUCT_ID, 5);
        $this->setBasketDeliveryMethod($I, $basketId, self::SHIPPING_STANDARD);
        $this->setBasketPaymentMethod($I, $basketId, self::PAYMENT_STANDARD);

        // making product out of stock now
        $I->updateInDatabase('oxarticles', ['oxstock' => '3', 'oxstockflag' => '3'], ['oxid' => self::PRODUCT_ID]);

        //place the order
        $this->placeOrder($I, $basketId, HttpCode::BAD_REQUEST);

        //remove basket
        $this->removeBasket($I, $basketId, self::USERNAME);
    }

    private function getGQLResponse(
        AcceptanceTester $I,
        string $query,
        array $variables = [],
        int $status = HttpCode::OK
    ): array {
        $I->sendGQLQuery($query, $variables);
        $I->seeResponseCodeIs($status);
        $I->seeResponseIsJson();

        return $I->grabJsonResponseAsArray();
    }

    private function createBasket(AcceptanceTester $I, string $basketTitle): string
    {
        $variables = [
            'title' => $basketTitle,
        ];

        $query = '
            mutation ($title: String!){
                basketCreate(basket: {title: $title}) {
                    id
                }
            }
        ';
        $result = $this->getGQLResponse($I, $query, $variables);

        return $result['data']['basketCreate']['id'];
    }

    private function addProductToBasket(AcceptanceTester $I, string $basketId, string $productId, float $amount): array
    {
        $variables = [
            'basketId'  => $basketId,
            'productId' => $productId,
            'amount'    => $amount,
        ];

        $mutation = '
            mutation ($basketId: String!, $productId: String!, $amount: Float! ) {
                basketAddProduct(
                    basketId: $basketId,
                    productId: $productId,
                    amount: $amount
                ) {
                    id
                    items {
                        product {
                            id
                        }
                        amount
                    }
                }
            }
        ';

        $result = $this->getGQLResponse($I, $mutation, $variables);

        return $result['data']['basketAddProduct']['items'];
    }

    private function queryBasketDeliveryMethods(AcceptanceTester $I, string $basketId): array
    {
        $variables = [
            'basketId'  => new ID($basketId),
        ];

        $query = '
            query ($basketId: ID!){
                basketDeliveryMethods (basketId: $basketId) {
                   id
                }
            }
        ';

        $result = $this->getGQLResponse($I, $query, $variables);

        return $result['data']['basketDeliveryMethods'];
    }

    private function queryBasketPaymentMethods(AcceptanceTester $I, string $basketId): array
    {
        $variables = [
            'basketId'  => new ID($basketId),
        ];

        $query = '
            query ($basketId: ID!){
                basketPayments (basketId: $basketId) {
                   id
                }
            }
        ';

        $result = $this->getGQLResponse($I, $query, $variables);

        return $result['data']['basketPayments'];
    }

    private function setBasketDeliveryMethod(
        AcceptanceTester $I,
        string $basketId,
        string $deliverySetId,
        int $status = HttpCode::OK
    ): string {
        $variables = [
            'basketId'   => new ID($basketId),
            'deliveryId' => new ID($deliverySetId),
        ];

        $mutation = '
            mutation ($basketId: ID!, $deliveryId: ID!) {
                basketSetDeliveryMethod(
                    basketId: $basketId,
                    deliveryMethodId: $deliveryId
                    ) {
                    deliveryMethod {
                        id
                    }
                }
            }
        ';
        $result = $this->getGQLResponse($I, $mutation, $variables, $status);

        return (string) $result['data']['basketSetDeliveryMethod']['deliveryMethod']['id'];
    }

    private function setBasketPaymentMethod(AcceptanceTester $I, string $basketId, string $paymentId): string
    {
        $variables = [
            'basketId'  => new ID($basketId),
            'paymentId' => new ID($paymentId),
        ];

        $mutation = '
            mutation ($basketId: ID!, $paymentId: ID!) {
                basketSetPayment(
                    basketId: $basketId,
                    paymentId: $paymentId
                    ) {
                    id
                }
            }
        ';
        $result = $this->getGQLResponse($I, $mutation, $variables);

        return $result['data']['basketSetPayment']['id'];
    }

    private function getOrderFromOrderHistory(AcceptanceTester $I): array
    {
        $mutation = '
            query {
                customer {
                    id
                    orders(
                        pagination: {limit: 1, offset: 0}
                    ){
                        id
                        orderNumber
                        invoiceNumber
                        invoiced
                        cancelled
                        ordered
                        paid
                        updated
                        vouchers {
                            id
                        }
                        invoiceAddress {
                            firstName
                            lastName
                            street
                        }
                        deliveryAddress {
                            firstName
                            lastName
                            street
                            country {
                                id
                            }
                        }
                    }
                }
            }
        ';

        $result = $this->getGQLResponse($I, $mutation);

        return $result['data']['customer']['orders'][0];
    }

    private function placeOrder(AcceptanceTester $I, string $basketId, int $status = HttpCode::OK): array
    {
        //now actually place the order
        $variables = [
            'basketId' => new ID($basketId),
        ];
        $mutation = '
            mutation ($basketId: ID!) {
                placeOrder(
                    basketId: $basketId
                    ) {
                    id
                    orderNumber
                }
            }
        ';

        return $this->getGQLResponse($I, $mutation, $variables, $status);
    }

    private function removeBasket(AcceptanceTester $I, string $basketId, string $username): void
    {
        $I->login($username, self::PASSWORD);

        $variables = [
            'basketId' => new ID($basketId),
        ];

        $I->sendGQLQuery(
            'mutation ($basketId: String!) {
                basketRemove(id: $basketId)
            }',
            $variables
        );
    }

    private function createDeliveryAddress(AcceptanceTester $I, string $countryId = 'a7c40f631fc920687.20179984'): string
    {
        $variables = [
            'countryId' => new ID($countryId),
        ];

        $mutation = 'mutation ($countryId: ID!) {
                customerDeliveryAddressAdd(deliveryAddress: {
                    salutation: "MRS",
                    firstName: "Marlene",
                    lastName: "Musterlich",
                    additionalInfo: "private delivery",
                    street: "Bertoldstrasse",
                    streetNumber: "48",
                    zipCode: "79098",
                    city: "Freiburg",
                    countryId: $countryId}
                    ){
                       id
                    }
                }
            ';

        $result = $this->getGQLResponse($I, $mutation, $variables);

        return $result['data']['customerDeliveryAddressAdd']['id'];
    }

    private function setBasketDeliveryAddress(
        AcceptanceTester $I,
        string $basketId,
        string $countryId = 'a7c40f631fc920687.20179984'
    ): void {
        $deliveryAddressId = $this->createDeliveryAddress($I, $countryId);

        $variables = [
            'basketId'          => $basketId,
            'deliveryAddressId' => $deliveryAddressId,
        ];

        $mutation = '
            mutation ($basketId: String!, $deliveryAddressId: String!) {
                basketSetDeliveryAddress(basketId: $basketId, deliveryAddressId: $deliveryAddressId) {
                    deliveryAddress {
                        id
                    }
                }
            }';

        $result = $this->getGQLResponse($I, $mutation, $variables);

        $I->assertSame($deliveryAddressId, $result['data']['basketSetDeliveryAddress']['deliveryAddress']['id']);
    }

    private function addVoucherToBasket(AcceptanceTester $I, string $basketId, string $voucher): void
    {
        $variables = [
            'basketId' => $basketId,
            'voucher' => $voucher,
        ];

        $mutation = '
            mutation ($basketId: String!, $voucher: String!){
                basketAddVoucher(basketId: $basketId, voucher: $voucher){
                    vouchers {
                        number
                    }
                }
            }
        ';
        $result = $this->getGQLResponse($I, $mutation, $variables);

        $I->assertSame($voucher, $result['data']['basketAddVoucher']['vouchers'][0]['number']);
    }

    private function createVoucher(AcceptanceTester $I): void
    {
        $I->haveInDatabase(
            'oxvoucherseries',
            [
                'OXID' => 'voucherserie1',
                'OXSERIENR' => 'voucherserie1',
                'OXDISCOUNT' => 5,
                'OXDISCOUNTTYPE' => 'absolute',
                'OXBEGINDATE' => '2000-01-01',
                'OXENDDATE' => '2050-12-31',
                'OXSERIEDESCRIPTION' => '',
                'OXALLOWOTHERSERIES' => 1,
            ]
        );
        $I->haveInDatabase(
            'oxvouchers',
            [
                'OXDATEUSED' => null,
                'OXORDERID' => '',
                'OXUSERID' => '',
                'OXRESERVED' => 0,
                'OXVOUCHERNR' => 'voucher1',
                'OXVOUCHERSERIEID' => 'voucherserie1',
                'OXID' => 'voucher1id',
                'OXDISCOUNT' => 5,
                'OXTIMESTAMP' => date("Y-m-d"),
                'OEGQL_BASKETID' => 'null',
            ]
        );
    }
}
