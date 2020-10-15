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
 * @group delivery
 */
final class BasketDeliveryCest extends BaseCest
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const BASKET_TITLE = 'basketdelivery';

    public function testBasketDeliveries(AcceptanceTester $I): void
    {
        $I->login(self::USERNAME, self::PASSWORD);

        $basketId = $this->basketCreate($I);

        $I->sendGQLQuery(
            'query {
              basketDeliveries(basketId: "' . $basketId . '") {
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
                'id'    => 'oxidstandard',
                'title' => 'Standard',
            ], [
                'id'    => '_deliveryset',
                'title' => 'graphql set',
            ],
        ], $result['data']['basketDeliveries']);
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
