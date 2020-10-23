<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Tests\Codeception\Acceptance\Basket;

use Codeception\Util\HttpCode;
use TheCodingMachine\GraphQLite\Types\ID;
use OxidEsales\GraphQL\Checkout\Tests\Codeception\Acceptance\BaseCest;
use OxidEsales\GraphQL\Checkout\Tests\Codeception\AcceptanceTester;

/**
 * @group oe_graphql_checkout
 * @group place_order
 * @group basket
 */
final class PlaceOrderCest extends BaseCest
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const PRODUCT_ID = 'dc5ffdf380e15674b56dd562a7cb6aec';

    private const SHIPPING_STANDARD = 'oxidstandard';

    private const TEST_SHIPPING = '_deliveryset';

    private const PAYMENT_STANDARD = 'oxidcashondel';

    private const PAYMENT_TEST = 'oxidgraphql';

    public function placeOrderUsingInvoiceAddress(AcceptanceTester $I): void
    {
        $I->login(self::USERNAME, self::PASSWORD);

        //prepare basket
        $basketId = $this->createBasket($I, 'mycart');
        $this->addProductToBasket($I, $basketId, self::PRODUCT_ID, 2);
        $this->setBasketDeliveryMethod($I, $basketId, self::SHIPPING_STANDARD);
        $this->setBasketPaymentMethod($I, $basketId, self::PAYMENT_STANDARD);

        //now actually place the order
        $variables = [
            'basketId' => new ID($basketId)
        ];
        $mutation = '
            mutation ($basketId: ID!) {
                placeOrder(
                    basketId: $basketId
                    ) {
                    id
                }
            }
        ';
        $result = $this->getGQLResponse($I, $mutation, $variables);
        $orderId = $result['data']['placeOrder']['id'];

        $orders = $this->getOrderFromOrderHistory($I);
        $I->assertEquals($orders['id'], $orderId);
        $I->assertNotEmpty($orders['invoiceAddress']);
        $I->assertNull($orders['deliveryAddress']);
    }

    private function getGQLResponse(
        AcceptanceTester $I,
        string $query,
        array $variables = [],
        int $status = HttpCode::OK
    ): array
    {
        $I->sendGQLQuery($query, $variables);
        $I->seeResponseCodeIs($status);
        $I->seeResponseIsJson();

        return $I->grabJsonResponseAsArray();
    }

    private function createBasket(AcceptanceTester $I, string $basketTitle): string
    {
        $variables = [
            'title' => $basketTitle
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
            'amount'    => $amount
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
            'basketId'  => new ID($basketId)
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
            'basketId'  => new ID($basketId)
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

    private function setBasketDeliveryMethod(AcceptanceTester $I, string $basketId, string $deliverySetId): string
    {
        $variables = [
            'basketId'   => new ID($basketId),
            'deliveryId' => new ID($deliverySetId)
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
        $result = $this->getGQLResponse($I, $mutation, $variables);

        return $result['data']['basketSetDeliveryMethod']['deliveryMethod']['id'];
    }

    private function setBasketPaymentMethod(AcceptanceTester $I, string $basketId, string $paymentId): string
    {
        $variables = [
            'basketId'  => new ID($basketId),
            'paymentId' => new ID($paymentId)
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
                        }
                    }
                }
            }
        ';

        $result = $this->getGQLResponse($I, $mutation);

        return $result['data']['customer']['orders'][0];
    }
}
