<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Tests\Codeception\Acceptance\Basket;

use Codeception\Util\HttpCode;
use OxidEsales\GraphQL\Checkout\Tests\Codeception\Acceptance\BaseCest;
use OxidEsales\GraphQL\Checkout\Tests\Codeception\AcceptanceTester;

/**
 * @group basket
 * @group payment
 */
final class BasketPaymentCest extends BaseCest
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const BASKET_TITLE = 'basketpayments';

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
