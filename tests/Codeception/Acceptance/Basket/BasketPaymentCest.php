<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Tests\Codeception\Acceptance\Basket;

use Codeception\Example;
use Codeception\Util\HttpCode;
use OxidEsales\GraphQL\Checkout\Tests\Codeception\Acceptance\BaseCest;
use OxidEsales\GraphQL\Checkout\Tests\Codeception\AcceptanceTester;

/**
 * @group oe_graphql_checkout
 * @group basket
 * @group payment
 */
final class BasketPaymentCest extends BaseCest
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const BASKET_TITLE = 'basketpayments';

    private const BASKET_WITH_PAYMENT_ID = 'basket_user_address_payment';

    private const BASKET_WITHOUT_PAYMENT_ID = 'basket_user_3';

    private const PAYMENT_ID = 'oxiddebitnote';

    public function _after(AcceptanceTester $I): void
    {
        $I->logout();
    }

    /**
     * @dataProvider basketPaymentProvider
     */
    public function getBasketPayment(AcceptanceTester $I, Example $data): void
    {
        $basketId  = $data['basketId'];
        $paymentId = $data['paymentId'];

        $I->login(self::USERNAME, self::PASSWORD);

        $I->sendGQLQuery('query {
            basket(id: "' . $basketId . '") {
                id
                payment {
                    id
                }
            }
        }');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();

        $result = $I->grabJsonResponseAsArray();
        $basket = $result['data']['basket'];

        if ($paymentId !== null) {
            $I->assertSame(self::PAYMENT_ID, $basket['payment']['id']);
        } else {
            $I->assertNull($basket['payment']);
        }
    }

    public function testBasketPayments(AcceptanceTester $I): void
    {
        $I->login(self::USERNAME, self::PASSWORD);

        $basketId = $this->basketCreate($I);

        $I->sendGQLQuery(
            'query {
              basketPayments(basketId: "' . $basketId . '") {
                id
                title
              }
            }'
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $result = $I->grabJsonResponseAsArray();

        $I->assertSame([
            [
                'id'    => 'oxidinvoice',
                'title' => 'Rechnung',
            ],
            [
                'id'    => 'oxidpayadvance',
                'title' => 'Vorauskasse',
            ],
            [
                'id'    => 'oxiddebitnote',
                'title' => 'Bankeinzug/Lastschrift',
            ],
            [
                'id'    => 'oxidcashondel',
                'title' => 'Nachnahme',
            ],
            [
                'id'    => 'oxidgraphql',
                'title' => 'GraphQL',
            ],
        ], $result['data']['basketPayments']);
    }

    public function testNonExistingBasketPayments(AcceptanceTester $I): void
    {
        $I->login(self::USERNAME, self::PASSWORD);

        $basketId = 'non-existing-basket';

        $I->sendGQLQuery(
            'query {
              basketPayments(basketId: "' . $basketId . '") {
                id
                title
              }
            }'
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    protected function basketPaymentProvider(): array
    {
        return [
            [
                'basketId'  => self::BASKET_WITH_PAYMENT_ID,
                'paymentId' => self::PAYMENT_ID,
            ],
            [
                'basketId'  => self::BASKET_WITHOUT_PAYMENT_ID,
                'paymentId' => null,
            ],
        ];
    }

    private function basketCreate(AcceptanceTester $I)
    {
        $I->sendGQLQuery(
            'mutation {
                basketCreate(basket: {title: "' . self::BASKET_TITLE . '"}) {
                    id
                }
            }'
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $result = $I->grabJsonResponseAsArray();

        return $result['data']['basketCreate']['id'];
    }
}
