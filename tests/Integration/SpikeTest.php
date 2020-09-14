<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */


/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Tests\Integration;

use OxidEsales\GraphQL\Base\Tests\Integration\TokenTestCase;

final class SpikeTest extends TokenTestCase
{
    private const USERNAME = 'user@oxid-esales.com';

    private const PASSWORD = 'useruser';

    private const PRODUCT_ID = 'dc5ffdf380e15674b56dd562a7cb6aec';

    private const BASKET_SAVED_BASKET = 'savedbasket';

    private const BASKET_SAVED_BASKET_ID = '_test_savedbasket';

    private const DEFAULT_DELIVERY_ADDRESS_ID = 'test_delivery_address';

    private const COUNTRY_ID_DE = 'a7c40f631fc920687.20179984';

    public function testSpike(): void
    {
        $this->prepareToken(self::USERNAME, self::PASSWORD);

        // remove possible saved basket
        $this->basketRemoveMutation(self::BASKET_SAVED_BASKET_ID);

        // create savedBasket
        $result = $this->basketCreateMutation(self::BASKET_SAVED_BASKET);
        $this->assertResponseStatus(200, $result);
        $savedBasketId = $result['body']['data']['basketCreate']['id'];

        //fill basket with items
        $result = $this->basketAddProductMutation($savedBasketId, self::PRODUCT_ID, 2);
        $this->assertResponseStatus(200, $result);

        //query parcelDeliveriesForBasket (arguments: basket id and country id)
        //Shop offers a list with delivery options and shows the payment options
        //available per delivery option.
        //see PaymentController::getAllSets() and PaymentController::getPaymentList()
        //
        // TODO


    }

    private function queryParcelDeliveriesForBasket(): array
    {
        return $this->query('query {
            parcelDeliveriesForBasket {
                id
                payments {
                   TODO
                }
            }
        }');
    }

    private function basketCreateMutation(string $title): array
    {
        return $this->query(
            'mutation {
            basketCreate(basket: {title: "' . $title . '"}) {
                owner {
                    firstName
                }
                items(pagination: {limit: 10, offset: 0}) {
                    product {
                        title
                    }
                }
                id
                public
            }
        }'
        );
    }

    private function basketRemoveMutation(string $basketId): array
    {
        return $this->query(
            'mutation {
            basketRemove(id: "' . $basketId . '")
        }'
        );
    }

    private function queryDeliveryAddress(): array
    {
        return $this->query('query {
            customerDeliveryAddresses {
                id
                firstName
                lastName
                street
                streetNumber
            }
        }');
    }

    private function basketAddProductMutation(string $basketId, string $productId, int $amount = 1): array
    {
        return $this->query('
            mutation {
                basketAddProduct(
                    basketId: "' . $basketId . '",
                    productId: "' . $productId . '",
                    amount: ' . $amount . '
                ) {
                    id
                    items {
                        product {
                            id
                        }
                        amount
                    }
                    lastUpdateDate
                }
            }
        ');
    }
}