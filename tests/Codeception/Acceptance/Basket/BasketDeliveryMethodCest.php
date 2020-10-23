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
 * @group oe_graphql_checkout
 * @group basket
 * @group delivery
 */
final class BasketDeliveryMethodCest extends BaseCest
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const BASKET_TITLE = 'basketdelivery';

    public function testBasketDeliveries(AcceptanceTester $I): void
    {
        $I->login(self::USERNAME, self::PASSWORD);

        $basketId = $this->basketCreate($I);

        $I->sendGQLQuery(
            $this->basketDeliveryMethods($basketId)
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
        ], $result['data']['basketDeliveryMethods']);
    }

    public function getNonExistingBasketDeliveryMethods(AcceptanceTester $I): void
    {
        $I->login(self::USERNAME, self::PASSWORD);

        $basketId = 'non-existing-basket';

        $I->sendGQLQuery(
            $this->basketDeliveryMethods($basketId)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
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

    private function basketDeliveryMethods(string $basketId): string
    {
        return '
            query {
              basketDeliveryMethods(basketId: "' . $basketId . '") {
                id
                title
              }
            }
        ';
    }
}
