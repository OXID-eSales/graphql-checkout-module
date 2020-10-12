<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Tests\Codeception\Acceptance\Basket;

use Codeception\Util\HttpCode;
use OxidEsales\GraphQL\Checkout\Tests\Codeception\Acceptance\MultishopBaseCest;
use OxidEsales\GraphQL\Checkout\Tests\Codeception\AcceptanceTester;

/**
 * @group address
 * @group basket
 * @group new
 */
final class DeliveryAddressMultiShopCest extends MultishopBaseCest
{
    private const USERNAME = 'user@oxid-esales.com';

    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const BASKET_ID_1 = 'basket_otheruser_2';

    private const DELIVERY_ID_1 = 'address_otheruser';

    private const BASKET_ID_2 = 'basket_user_2';

    private const DELIVERY_ID_2 = 'address_user';

    public function setDeliveryAddressToBasketFromShop1WithUserLoggedInShop2(AcceptanceTester $I): void
    {
        $I->updateConfigInDatabaseForShops('blMallUsers', true, 'bool', [2]);

        $I->login(self::OTHER_USERNAME, self::PASSWORD, 2);

        $I->sendGQLQuery(
            $this->basketSetDeliveryAddress(self::BASKET_ID_1, self::DELIVERY_ID_1),
            null,
            0,
            2
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();

        $result = $I->grabJsonResponseAsArray();
        $basket = $result['data']['basketSetDeliveryAddress'];

        $I->assertSame('Marc', $basket['owner']['firstName']);
        $I->assertSame(self::DELIVERY_ID_1, $basket['deliveryAddress']['id']);
    }

    public function setDeliveryAddressToBasketForShop2(AcceptanceTester $I): void
    {
        $I->login(self::USERNAME, self::PASSWORD, 2);

        $I->sendGQLQuery(
            $this->basketSetDeliveryAddress(self::BASKET_ID_2, self::DELIVERY_ID_2),
            null,
            0,
            2
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    private function basketSetDeliveryAddress(string $basketId, string $deliveryAddressId): string
    {
        return 'mutation {
            basketSetDeliveryAddress(basketId: "' . $basketId . '", deliveryAddressId: "' . $deliveryAddressId . '") {
                owner {
                    firstName
                }
                deliveryAddress {
                    id
                }
            }
        }';
    }
}
