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

/**
 * @group oe_graphql_checkout
 * @group delivery-set
 * @group basket
 */
final class BasketSetDeliveryMutationCest extends BaseCest
{
    private const USERNAME = 'user@oxid-esales.com';

    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const BASKET_TITLE = 'deliverysetbasket';

    private const AVAILABLE_DELIVERY_SET_ID = '_deliveryset';

    private const UNAVAILABLE_DELIVERY_SET_ID = '_unavailabledeliveryset';

    private const NON_EXISTING_DELIVERY_SET_ID = 'non-existing-delivery-set-id';

    private const NON_EXISTING_BASKET_ID = 'non-existing-basket-id';

    private $basketId;

    public function _before(AcceptanceTester $I, Scenario $scenario): void
    {
        parent::_before($I, $scenario);

        $this->basketCreate($I);
    }

    public function _after(AcceptanceTester $I): void
    {
        $this->basketRemove($I);

        parent::_after($I);
    }

    public function setAvailableDeliverySetToBasket(AcceptanceTester $I): void
    {
        $I->sendGQLQuery(
            $this->basketSetDelivery(self::AVAILABLE_DELIVERY_SET_ID)
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();

        $result = $I->grabJsonResponseAsArray();
        $basket = $result['data']['basketSetDelivery'];

        $I->assertSame($this->basketId, $basket['id']);
        $I->assertSame(self::AVAILABLE_DELIVERY_SET_ID, $basket['deliverySetId']);
    }

    public function setUnavailableDeliverySetToBasket(AcceptanceTester $I): void
    {
        $I->sendGQLQuery(
            $this->basketSetDelivery(self::UNAVAILABLE_DELIVERY_SET_ID)
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    public function setNonExistingDeliverySetToBasket(AcceptanceTester $I): void
    {
        $I->sendGQLQuery(
            $this->basketSetDelivery(self::NON_EXISTING_DELIVERY_SET_ID)
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    public function setDeliverySetToWrongBasket(AcceptanceTester $I): void
    {
        $I->login(self::OTHER_USERNAME, self::PASSWORD);

        $I->sendGQLQuery(
            $this->basketSetDelivery(self::AVAILABLE_DELIVERY_SET_ID)
        );

        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);

        // Login as the basket owner, because on _after the basket will be deleted
        $I->login(self::USERNAME, self::PASSWORD);
    }

    public function setDeliverySetToNonExistingBasket(AcceptanceTester $I): void
    {
        $I->sendGQLQuery(
            $this->basketSetDelivery(self::AVAILABLE_DELIVERY_SET_ID, self::NON_EXISTING_BASKET_ID)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    private function basketCreate(AcceptanceTester $I): void
    {
        $I->login(self::USERNAME, self::PASSWORD);

        $I->sendGQLQuery('
            mutation {
                basketCreate(basket: {title: "' . self::BASKET_TITLE . '"}) {
                    id
                }
            }
        ');

        $I->seeResponseCodeIs(HttpCode::OK);
        $result = $I->grabJsonResponseAsArray();

        $this->basketId = $result['data']['basketCreate']['id'];
    }

    private function basketRemove($I): void
    {
        $I->sendGQLQuery('
            mutation {
                basketRemove (id: "' . $this->basketId . '")
            }
        ');

        $I->seeResponseCodeIs(HttpCode::OK);
    }

    private function basketSetDelivery(string $deliverySetId, ?string $basketId = null): string
    {
        $basketId = $basketId ?: $this->basketId;

        return 'mutation {
            basketSetDelivery(basketId: "' . $basketId . '", deliverySetId: "' . $deliverySetId . '") {
                id
                deliverySetId
            }
        }';
    }
}
