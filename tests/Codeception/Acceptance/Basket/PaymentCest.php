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
 * @group payment
 * @group basket
 */
final class PaymentCest extends BaseCest
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const BASKET_ID = 'basket_user_address_payment';

    private const PAYMENT_ID = 'oxiddebitnote';

    public function _after(AcceptanceTester $I): void
    {
        $I->logout();
    }

    public function getBasketPayment(AcceptanceTester $I): void
    {
        $I->login(self::USERNAME, self::PASSWORD);

        $I->sendGQLQuery(
            $this->basketPayment(self::BASKET_ID)
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();

        $result = $I->grabJsonResponseAsArray();
        $basket = $result['data']['basket'];

        $I->assertSame(self::PAYMENT_ID, $basket['payment']['id']);
    }

    private function basketPayment(string $basketId): string
    {
        return 'query {
            basket(id: "' . $basketId . '") {
                id
                payment {
                    id
                }
            }
        }';
    }
}
