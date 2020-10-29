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

abstract class PlaceOrderBaseCest extends BaseCest
{
    protected const USERNAME = 'user@oxid-esales.com';

    protected const CHECKOUT_USERNAME = 'checkoutuser@oxid-esales.com';

    protected const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    protected const PASSWORD = 'useruser';

    protected const PRODUCT_ID = 'dc5ffdf380e15674b56dd562a7cb6aec';

    protected const SHIPPING_STANDARD = 'oxidstandard';

    protected const TEST_SHIPPING = '_deliveryset';

    protected const PAYMENT_STANDARD = 'oxidcashondel';

    protected const PAYMENT_TEST = 'oxidgraphql';

    protected const EMPTY_BASKET_NAME = 'my_empty_cart';

    protected const DEFAULT_SAVEDBASKET = 'savedbasket';

    protected const ALTERNATE_COUNTRY = 'a7c40f632a0804ab5.18804076';

    protected const DOWNLOADABLE_FILE = 'oiaa81b5e002fc2f73b9398c361c0b97';

    public function _before(AcceptanceTester $I, Scenario $scenario): void
    {
        parent::_before($I, $scenario);

        $I->updateConfigInDatabase('blPerfNoBasketSaving', false, 'bool');
        $I->updateConfigInDatabase('blCalculateDelCostIfNotLoggedIn', false, 'bool');
    }

    protected function getGQLResponse(
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

    protected function createBasket(AcceptanceTester $I, string $basketTitle): string
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

    protected function addProductToBasket(AcceptanceTester $I, string $basketId, string $productId, float $amount): array
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

    protected function queryBasketDeliveryMethods(AcceptanceTester $I, string $basketId): array
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

    protected function queryBasketPaymentMethods(AcceptanceTester $I, string $basketId): array
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

    protected function setBasketDeliveryMethod(
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

        if ($status === HttpCode::BAD_REQUEST) {
            return (string) $result['errors'][0]['message'];
        }

        return (string) $result['data']['basketSetDeliveryMethod']['deliveryMethod']['id'];
    }

    protected function setBasketPaymentMethod(AcceptanceTester $I, string $basketId, string $paymentId): string
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

    protected function getOrderFromOrderHistory(AcceptanceTester $I): array
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
                        cost {
                            total
                        }
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

    protected function placeOrder(AcceptanceTester $I, string $basketId, int $status = HttpCode::OK): array
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

    protected function removeBasket(AcceptanceTester $I, string $basketId, string $username): void
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

    protected function createDeliveryAddress(AcceptanceTester $I, string $countryId = 'a7c40f631fc920687.20179984'): string
    {
        $variables = [
            'countryId' => new ID($countryId),
        ];

        $mutation = 'mutation ($countryId: ID!) {
                customerDeliveryAddressAdd(deliveryAddress: {
                    salutation: "MRS",
                    firstName: "Marlene",
                    lastName: "Musterlich",
                    additionalInfo: "protected delivery",
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

    protected function setBasketDeliveryAddress(
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

    protected function addVoucherToBasket(AcceptanceTester $I, string $basketId, string $voucher): void
    {
        $variables = [
            'basketId' => $basketId,
            'voucher'  => $voucher,
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

    protected function createVoucher(AcceptanceTester $I): void
    {
        $I->haveInDatabase(
            'oxvoucherseries',
            [
                'OXID'               => 'voucherserie1',
                'OXSERIENR'          => 'voucherserie1',
                'OXDISCOUNT'         => 5,
                'OXDISCOUNTTYPE'     => 'absolute',
                'OXBEGINDATE'        => '2000-01-01',
                'OXENDDATE'          => '2050-12-31',
                'OXSERIEDESCRIPTION' => '',
                'OXALLOWOTHERSERIES' => 1,
            ]
        );
        $I->haveInDatabase(
            'oxvouchers',
            [
                'OXDATEUSED'       => null,
                'OXORDERID'        => '',
                'OXUSERID'         => '',
                'OXRESERVED'       => 0,
                'OXVOUCHERNR'      => 'voucher1',
                'OXVOUCHERSERIEID' => 'voucherserie1',
                'OXID'             => 'voucher1id',
                'OXDISCOUNT'       => 5,
                'OXTIMESTAMP'      => date('Y-m-d'),
                'OEGQL_BASKETID'   => 'null',
            ]
        );
    }
}
