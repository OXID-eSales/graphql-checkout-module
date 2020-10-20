<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Tests\Codeception\Acceptance\Basket;

use Codeception\Scenario;
use Codeception\Util\HttpCode;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\GraphQL\Checkout\Tests\Codeception\Acceptance\BaseCest;
use OxidEsales\GraphQL\Checkout\Tests\Codeception\AcceptanceTester;

/**
 * @group oe_graphql_checkout
 * @group payment
 * @group basket
 */
final class BasketSetPaymentMutationCest extends BaseCest
{
    private const USERNAME = 'user@oxid-esales.com';

    private const OTHER_USERNAME = 'otheruser@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const BASKET_TITLE = 'paymentsetbasket';

    private const DELIVERY_SET_ID = 'oxidstandard';

    private const AVAILABLE_PAYMENT_ID = 'oxidinvoice';

    private const UNAVAILABLE_PAYMENT_ID = 'oxempty';

    private const NON_EXISTING_PAYMENT_ID = 'non-existing-payment-id';

    private const NON_EXISTING_BASKET_ID = 'non-existing-basket-id';

    private const COUNTRY_UK = 'a7c40f632a0804ab5.18804076';

    private const COUNTRY_DE = 'a7c40f631fc920687.20179984';

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

    public function setAvailablePaymentToBasket(AcceptanceTester $I): void
    {
        $this->basketSetDelivery($I, self::DELIVERY_SET_ID);

        $I->sendGQLQuery(
            $this->basketSetPayment(self::AVAILABLE_PAYMENT_ID)
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();

        $result = $I->grabJsonResponseAsArray();
        $basket = $result['data']['basketSetPayment'];

        $I->assertSame($this->basketId, $basket['id']);
        $I->assertSame(self::AVAILABLE_PAYMENT_ID, $basket['paymentId']);
    }

    public function setPaymentToBasketWithoutSetDeliverySet(AcceptanceTester $I): void
    {
        $I->sendGQLQuery(
            $this->basketSetPayment(self::AVAILABLE_PAYMENT_ID)
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    public function setUnavailablePaymentToBasket(AcceptanceTester $I): void
    {
        $this->basketSetDelivery($I, self::DELIVERY_SET_ID);

        $I->sendGQLQuery(
            $this->basketSetPayment(self::UNAVAILABLE_PAYMENT_ID)
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    public function setNonExistingPaymentToBasket(AcceptanceTester $I): void
    {
        $this->basketSetDelivery($I, self::DELIVERY_SET_ID);

        $I->sendGQLQuery(
            $this->basketSetPayment(self::NON_EXISTING_PAYMENT_ID)
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    public function setPaymentToWrongBasket(AcceptanceTester $I): void
    {
        $I->login(self::OTHER_USERNAME, self::PASSWORD);

        $I->sendGQLQuery(
            $this->basketSetPayment(self::AVAILABLE_PAYMENT_ID)
        );

        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);

        // Login as the basket owner, because on _after the basket will be deleted
        $I->login(self::USERNAME, self::PASSWORD);
    }

    public function setDeliverySetToNonExistingBasket(AcceptanceTester $I): void
    {
        $I->sendGQLQuery(
            $this->basketSetPayment(self::AVAILABLE_PAYMENT_ID, self::NON_EXISTING_BASKET_ID)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function setPaymentToBasketWithWrongCountry(AcceptanceTester $I): void
    {
        $this->basketSetDelivery($I, self::DELIVERY_SET_ID);

        $this->setCountry(self::COUNTRY_UK);

        $I->sendGQLQuery(
            $this->basketSetPayment(self::AVAILABLE_PAYMENT_ID)
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);

        $this->setCountry(self::COUNTRY_DE);
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

    private function basketSetDelivery(AcceptanceTester $I, string $deliverySetId): void
    {
        $I->sendGQLQuery(
            'mutation {
                basketSetDelivery(basketId: "' . $this->basketId . '", deliverySetId: "' . $deliverySetId . '") {
                    id
                    deliverySetId
                }
            }'
        );

        $I->seeResponseCodeIs(HttpCode::OK);
    }

    private function basketRemove(AcceptanceTester $I): void
    {
        $I->sendGQLQuery('
            mutation {
                basketRemove (id: "' . $this->basketId . '")
            }
        ');

        $I->seeResponseCodeIs(HttpCode::OK);
    }

    private function basketSetPayment(string $paymentId, ?string $basketId = null): string
    {
        $basketId = $basketId ?: $this->basketId;

        return 'mutation {
            basketSetPayment(basketId: "' . $basketId . '", paymentId: "' . $paymentId . '") {
                id
                paymentId
            }
        }';
    }

    private function setCountry(string $countryId): void
    {
        $queryBuilder = ContainerFactory::getInstance()
            ->getContainer()
            ->get(QueryBuilderFactoryInterface::class)
            ->create();

        $queryBuilder->update('oxuser')
            ->set('oxcountryid', ':countryId')
            ->where('oxusername = :username')
            ->setParameter(':countryId', $countryId)
            ->setParameter(':username', self::USERNAME)
            ->execute();
    }
}
