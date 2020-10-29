<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Tests\Codeception\Acceptance\Basket;

use Codeception\Example;
use Codeception\Scenario;
use Codeception\Util\HttpCode;
use OxidEsales\GraphQL\Checkout\Basket\Exception\PlaceOrder;
use OxidEsales\GraphQL\Checkout\Tests\Codeception\AcceptanceTester;

/**
 * @group oe_graphql_checkout
 * @group place_order
 * @group basket
 * @group private_sales
 */
final class PrivateSalesCest extends PlaceOrderBaseCest
{
    private const USER_ID = 'e7af1c3b786fd02906ccd75698f4e6b9';

    public function _before(AcceptanceTester $I, Scenario $scenario): void
    {
        parent::_before($I, $scenario);

        $I->updateConfigInDatabase('blPsBasketReservationEnabled', true, 'bool');
        $I->updateConfigInDatabase('iPsBasketReservationTimeout', 1200, 'int');
    }

    public function _after(AcceptanceTester $I): void
    {
        $I->updateConfigInDatabase('blPsBasketReservationEnabled', false, 'bool');

        parent::_after($I);
    }

    public function placeOrderForTimedOutReservedBasket(AcceptanceTester $I): void
    {
        $I->wantToTest('placing an order with basket reservations enabled and timed out basket');
        $I->login(self::USERNAME, self::PASSWORD);

        //prepare basket
        $basketId = $this->createBasket($I, 'privatesales_1');
        $this->addProductToBasket($I, $basketId, self::PRODUCT_ID, 2);
        $this->setBasketDeliveryMethod($I, $basketId, self::SHIPPING_STANDARD);
        $this->setBasketPaymentMethod($I, $basketId, self::PAYMENT_STANDARD);

        //check basket
        $result         = $this->queryBasketTimeLeft($I, $basketId);
        $timeLeftBefore = $result['basket']['timeLeftInSeconds'];
        $I->assertTrue($timeLeftBefore > 0);

        $I->updateInDatabase(
            'oxuserbaskets',
            [
                'oxupdate' => 10,
            ],
            [
                'oxuserid' => self::USER_ID,
            ]
        );

        $result        = $this->queryBasketTimeLeft($I, $basketId);
        $timeLeftAfter = $result['basket']['timeLeftInSeconds'];
        $I->assertEquals(0, $timeLeftAfter);

        //cannot place the order
        $result               = $this->placeOrder($I, $basketId, HttpCode::BAD_REQUEST);
        $expectedError        = PlaceOrder::timedOutBasket($basketId);
        $expectedErrorMessage = $expectedError->getMessage();
        $I->assertEquals($expectedErrorMessage, $result['errors'][0]['message']);

        //remove basket
        $this->removeBasket($I, $basketId, self::USERNAME);
    }

    /**
     * @dataProvider dataProviderBasketName
     */
    public function placeOrderForNotTimedOutReservedBasket(AcceptanceTester $I, Example $data): void
    {
        $I->wantToTest('placing an order with basket reservations enabled');
        $I->login(self::CHECKOUT_USERNAME, self::PASSWORD);

        //prepare basket
        $basketId = $this->createBasket($I, $data['title']);
        $this->addProductToBasket($I, $basketId, self::PRODUCT_ID, 2);
        $this->setBasketDeliveryMethod($I, $basketId, self::TEST_SHIPPING);
        $this->setBasketPaymentMethod($I, $basketId, self::PAYMENT_TEST);

        //check basket time left
        $result         = $this->queryBasketTimeLeft($I, $basketId);
        $timeLeftBefore = $result['basket']['timeLeftInSeconds'];
        sleep(2);
        $result        = $this->queryBasketTimeLeft($I, $basketId);
        $timeLeftAfter = $result['basket']['timeLeftInSeconds'];
        $I->assertTrue($timeLeftBefore > $timeLeftAfter);

        //place the order
        $result  = $this->placeOrder($I, $basketId);
        $orderId = $result['data']['placeOrder']['id'];

        //check order history
        $orders = $this->getOrderFromOrderHistory($I);
        $I->assertEquals($orders['id'], $orderId);
        $I->assertEquals($orders['cost']['total'], 66.46);
        $I->assertNotEmpty($orders['invoiceAddress']);
        $I->assertNull($orders['deliveryAddress']);

        //remove basket
        $this->removeBasket($I, $basketId, self::CHECKOUT_USERNAME);
    }

    protected function dataProviderBasketName(): array
    {
        return [
            'default' => [
                'title' => self::DEFAULT_SAVEDBASKET,
            ],
            'custom'  => [
                'title' => 'privatesales',
            ],
        ];
    }

    private function queryBasketTimeLeft(AcceptanceTester $I, $basketId): array
    {
        $variables = [
            'basketId'  => $basketId,
        ];

        $query = '
            query ($basketId: String!){
                basket (id: $basketId) {
                   id
                   title
                   timeLeftInSeconds
                }
            }
        ';

        $result = $this->getGQLResponse($I, $query, $variables);

        return $result['data'];
    }
}
