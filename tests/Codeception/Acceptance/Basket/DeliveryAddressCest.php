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
 * @group address
 * @group basket
 */
final class DeliveryAddressCest extends BaseCest
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const BASKET_ID = 'basket_user';

    private const WRONG_BASKET_ID = 'basket_otheruser';

    private const DELIVERY_ADDRESS_ID = 'address_user';

    private const WRONG_DELIVERY_ADDRESS_ID = 'address_otheruser';

    private const BASKET_WITH_ADDRESS_ID = 'basket_user_address_payment';

    public function _after(AcceptanceTester $I): void
    {
        $I->logout();
    }

    public function setDeliveryAddressToBasket(AcceptanceTester $I): void
    {
        $I->login(self::USERNAME, self::PASSWORD);

        $I->sendGQLQuery(
            $this->basketSetDeliveryAddress(self::BASKET_ID, self::DELIVERY_ADDRESS_ID)
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();

        $result = $I->grabJsonResponseAsArray();
        $basket = $result['data']['basketSetDeliveryAddress'];

        $I->assertSame('User', $basket['owner']['firstName']);
        $I->assertSame(self::DELIVERY_ADDRESS_ID, $basket['deliveryAddress']['id']);
    }

    public function setDeliveryAddressToBasketWithoutToken(AcceptanceTester $I): void
    {
        $I->sendGQLQuery(
            $this->basketSetDeliveryAddress(self::BASKET_ID, self::DELIVERY_ADDRESS_ID)
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    public function setDeliveryAddressToWrongBasket(AcceptanceTester $I): void
    {
        $I->login(self::USERNAME, self::PASSWORD);

        $I->sendGQLQuery(
            $this->basketSetDeliveryAddress(self::WRONG_BASKET_ID, self::DELIVERY_ADDRESS_ID)
        );

        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
    }

    public function setDeliveryAddressToNonExistingBasket(AcceptanceTester $I): void
    {
        $I->login(self::USERNAME, self::PASSWORD);

        $I->sendGQLQuery(
            $this->basketSetDeliveryAddress('non-existing-basket-id', self::DELIVERY_ADDRESS_ID)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function setWrongDeliveryAddressToBasket(AcceptanceTester $I): void
    {
        $I->login(self::USERNAME, self::PASSWORD);

        $I->sendGQLQuery(
            $this->basketSetDeliveryAddress(self::BASKET_ID, self::WRONG_DELIVERY_ADDRESS_ID)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function setNonExistingDeliveryAddressToBasket(AcceptanceTester $I): void
    {
        $I->login(self::USERNAME, self::PASSWORD);

        $I->sendGQLQuery(
            $this->basketSetDeliveryAddress(self::BASKET_ID, 'non-existing-delivery-id')
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function getBasketDeliveryAddress(AcceptanceTester $I): void
    {
        $I->login(self::USERNAME, self::PASSWORD);

        $I->sendGQLQuery(
            $this->basketDeliveryAddress(self::BASKET_WITH_ADDRESS_ID)
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();

        $result = $I->grabJsonResponseAsArray();
        $basket = $result['data']['basket'];

        $I->assertSame(self::DELIVERY_ADDRESS_ID, $basket['deliveryAddress']['id']);
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

    private function basketDeliveryAddress(string $basketId): string
    {
        return 'query {
            basket(id: "' . $basketId . '") {
                id
                deliveryAddress {
                    id
                }
            }
        }';
    }
}
