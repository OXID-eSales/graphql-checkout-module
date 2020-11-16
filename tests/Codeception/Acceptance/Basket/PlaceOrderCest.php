<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\GraphQL\Checkout\Tests\Codeception\Acceptance\Basket;

use Codeception\Util\HttpCode;
use OxidEsales\GraphQL\Checkout\DeliveryMethod\Exception\UnavailableDeliveryMethod;
use OxidEsales\GraphQL\Checkout\Tests\Codeception\AcceptanceTester;

/**
 * @group oe_graphql_checkout
 * @group place_order
 * @group basket
 */
final class PlaceOrderCest extends PlaceOrderBaseCest
{
    public function placeOrderUsingInvoiceAddress(AcceptanceTester $I): void
    {
        $I->wantToTest('placing an order successfully with invoice address only');
        $I->login(self::USERNAME, self::PASSWORD, 0);

        //prepare basket
        $basketId = $this->createBasket($I, 'my_cart_one');
        $this->addProductToBasket($I, $basketId, self::PRODUCT_ID, 2);
        $this->setBasketDeliveryMethod($I, $basketId, self::SHIPPING_STANDARD);
        $this->setBasketPaymentMethod($I, $basketId, self::PAYMENT_STANDARD);

        //place the order
        $result  = $this->placeOrder($I, $basketId);
        $orderId = $result['data']['placeOrder']['id'];

        //check the basket costs
        $basketCosts = $this->queryBasketCost($I, $basketId);
        $I->assertEquals(59.8, $basketCosts['productGross']['sum']);
        $I->assertEquals(7.5, $basketCosts['payment']['price']);
        $I->assertEquals(3.9, $basketCosts['delivery']['price']);
        $I->assertEquals(0, $basketCosts['voucher']);
        $I->assertEquals(0, $basketCosts['discount']);
        $I->assertEquals(71.2, $basketCosts['total']);

        //check order history
        $orders = $this->getOrderFromOrderHistory($I);
        $I->assertEquals($orderId, $orders['id']);
        $I->assertEquals($basketCosts['total'], $orders['cost']['total']);
        $I->assertNotEmpty($orders['invoiceAddress']);
        $I->assertNull($orders['deliveryAddress']);

        //remove basket
        $this->removeBasket($I, $basketId, self::USERNAME);
    }

    public function placeOrderUsingInvoiceAddressAndDefaultSavedBasket(AcceptanceTester $I): void
    {
        $I->wantToTest('placing an order for savedbasket and blPerfNoBasketSaving');

        $I->updateConfigInDatabase('blPerfNoBasketSaving', true, 'bool');
        $I->login(self::CHECKOUT_USERNAME, self::PASSWORD);

        //prepare basket
        $basketId = $this->createBasket($I, self::DEFAULT_SAVEDBASKET);
        $this->addProductToBasket($I, $basketId, self::PRODUCT_ID, 2);
        $this->setBasketDeliveryMethod($I, $basketId, self::TEST_SHIPPING);
        $this->setBasketPaymentMethod($I, $basketId, self::PAYMENT_TEST);

        //check the basket costs
        $basketCosts = $this->queryBasketCost($I, $basketId);
        $I->assertEquals(59.8, $basketCosts['productGross']['sum']);
        $I->assertEquals(7.77, $basketCosts['payment']['price']);
        $I->assertEquals(6.66, $basketCosts['delivery']['price']);
        $I->assertEquals(0, $basketCosts['voucher']);
        $I->assertEquals(0, $basketCosts['discount']);
        $I->assertEquals(74.23, $basketCosts['total']);

        //place the order
        $result  = $this->placeOrder($I, $basketId);
        $orderId = $result['data']['placeOrder']['id'];

        //check order history
        $orders = $this->getOrderFromOrderHistory($I);
        $I->assertEquals($orderId, $orders['id']);
        $I->assertEquals($basketCosts['total'], $orders['cost']['total']);
        $I->assertNotEmpty($orders['invoiceAddress']);
        $I->assertNull($orders['deliveryAddress']);

        //remove basket
        $this->removeBasket($I, $basketId, self::CHECKOUT_USERNAME);
    }

    public function placeOrderUsingDeliveryAddress(AcceptanceTester $I): void
    {
        $I->wantToTest('placing an order successfully with delivery address');
        $I->login(self::USERNAME, self::PASSWORD);

        //prepare basket
        $basketId = $this->createBasket($I, 'my_cart_two');
        $this->addProductToBasket($I, $basketId, self::PRODUCT_ID, 2);
        $this->setBasketDeliveryAddress($I, $basketId);
        $this->setBasketDeliveryMethod($I, $basketId, self::SHIPPING_STANDARD);
        $this->setBasketPaymentMethod($I, $basketId, self::PAYMENT_STANDARD);

        //place the order
        $result  = $this->placeOrder($I, $basketId);
        $orderId = $result['data']['placeOrder']['id'];

        //check order history
        $orders = $this->getOrderFromOrderHistory($I);
        $I->assertEquals($orderId, $orders['id']);
        $I->assertNotEmpty($orders['invoiceAddress']);
        $I->assertNotEmpty($orders['deliveryAddress']);

        //remove basket
        $this->removeBasket($I, $basketId, self::USERNAME);
    }

    public function placeOrderWithoutToken(AcceptanceTester $I): void
    {
        $I->wantToTest('placing an order when logged out');
        $I->login(self::USERNAME, self::PASSWORD);

        //prepare basket
        $basketId = $this->createBasket($I, 'my_cart_three');
        $this->addProductToBasket($I, $basketId, self::PRODUCT_ID, 2);
        $this->setBasketDeliveryMethod($I, $basketId, self::SHIPPING_STANDARD);
        $this->setBasketPaymentMethod($I, $basketId, self::PAYMENT_STANDARD);

        //log out
        $I->logout();

        //place the order
        $this->placeOrder($I, $basketId, HttpCode::BAD_REQUEST);

        //remove basket
        $this->removeBasket($I, $basketId, self::USERNAME);
    }

    public function placeOtherUsersOrder(AcceptanceTester $I): void
    {
        $I->wantToTest('placing another users order');
        $I->login(self::USERNAME, self::PASSWORD);

        //prepare basket
        $basketId = $this->createBasket($I, 'my_cart_four');
        $this->addProductToBasket($I, $basketId, self::PRODUCT_ID, 2);
        $this->setBasketDeliveryMethod($I, $basketId, self::SHIPPING_STANDARD);
        $this->setBasketPaymentMethod($I, $basketId, self::PAYMENT_STANDARD);

        //log out
        $I->logout();

        //log in different user and place the order
        $I->login(self::OTHER_USERNAME, self::PASSWORD);
        $this->placeOrder($I, $basketId, HttpCode::UNAUTHORIZED);

        //remove basket
        $this->removeBasket($I, $basketId, self::USERNAME);
    }

    public function placeOrderWithEmptyBasket(AcceptanceTester $I): void
    {
        $I->wantToTest('that placing an order with empty basket fails');
        $I->login(self::USERNAME, self::PASSWORD);

        //prepare basket
        $basketId = $this->createBasket($I, self::EMPTY_BASKET_NAME);
        $this->setBasketDeliveryMethod($I, $basketId, self::SHIPPING_STANDARD);
        $this->setBasketPaymentMethod($I, $basketId, self::PAYMENT_STANDARD);

        //place the order
        $this->placeOrder($I, $basketId, HttpCode::BAD_REQUEST);

        //remove basket
        $this->removeBasket($I, $basketId, self::USERNAME);
    }

    public function prepareOrderWithNoShippingMethodForCountry(AcceptanceTester $I): void
    {
        $I->wantToTest('that using delivery address with unsupported country fails');
        $I->login(self::USERNAME, self::PASSWORD);

        //prepare basket with invoice address
        $basketId = $this->createBasket($I, 'my_cart_five');
        $this->addProductToBasket($I, $basketId, self::PRODUCT_ID, 3);
        $this->setBasketDeliveryAddress($I, $basketId, self::ALTERNATE_COUNTRY);

        //shipping method not supported
        $errorMessage         = $this->setBasketDeliveryMethod($I, $basketId, self::SHIPPING_STANDARD, HttpCode::BAD_REQUEST);
        $expectedError        = UnavailableDeliveryMethod::byId(self::SHIPPING_STANDARD);
        $expectedErrorMessage = $expectedError->getMessage();
        $I->assertEquals($expectedErrorMessage, $errorMessage);

        //remove basket
        $this->removeBasket($I, $basketId, self::USERNAME);
    }

    public function placeOrderWithChangedDeliveryAddress(AcceptanceTester $I): void
    {
        $I->wantToTest('that placing an order with changed delivery address fails');
        $I->login(self::USERNAME, self::PASSWORD);

        //prepare basket with german delivery address
        $basketId = $this->createBasket($I, 'my_cart_six');
        $this->addProductToBasket($I, $basketId, self::PRODUCT_ID, 3);
        $this->setBasketDeliveryAddress($I, $basketId); //Germany
        $this->setBasketDeliveryMethod($I, $basketId, self::SHIPPING_STANDARD);
        $this->setBasketPaymentMethod($I, $basketId, self::PAYMENT_STANDARD);

        //this country is not supported for chosen shipping method
        $this->setBasketDeliveryAddress($I, $basketId, self::ALTERNATE_COUNTRY);

        //place the order
        $this->placeOrder($I, $basketId, HttpCode::BAD_REQUEST);

        //remove basket
        $this->removeBasket($I, $basketId, self::USERNAME);
    }

    public function placeOrderWithUnavailablePayment(AcceptanceTester $I): void
    {
        $I->wantToTest('that placing an order with unavailable payment fails');
        $I->login(self::USERNAME, self::PASSWORD);

        //prepare basket with invoice address
        $basketId = $this->createBasket($I, 'my_cart_seven');
        $this->addProductToBasket($I, $basketId, self::PRODUCT_ID, 3);
        $this->setBasketDeliveryMethod($I, $basketId, self::SHIPPING_STANDARD);
        $this->setBasketPaymentMethod($I, $basketId, self::PAYMENT_STANDARD);

        $I->updateInDatabase('oxuserbaskets', ['oegql_paymentid' => self::PAYMENT_TEST], ['oxid' => $basketId]);

        //place the order
        $this->placeOrder($I, $basketId, HttpCode::BAD_REQUEST);

        //remove basket
        $this->removeBasket($I, $basketId, self::USERNAME);
    }

    public function placeOrderWithDiscountedProduct(AcceptanceTester $I): void
    {
        $I->wantToTest('placing an order with a discounted product');
        $I->login(self::USERNAME, self::PASSWORD);

        // get 10% off from 200 EUR product value on
        $I->updateInDatabase('oxdiscount', ['oxactive' => 0]);
        $I->updateInDatabase('oxdiscount', ['oxactive' => 1], ['oxid' => self::DISCOUNT_ID]);

        //prepare basket
        $basketId = $this->createBasket($I, 'cart_with_discount');
        $this->addProductToBasket($I, $basketId, self::DISCOUNT_PRODUCT, 1);
        $this->setBasketDeliveryMethod($I, $basketId, self::SHIPPING_STANDARD);
        $this->setBasketPaymentMethod($I, $basketId, self::PAYMENT_STANDARD);

        //check the basket costs
        $basketCosts = $this->queryBasketCost($I, $basketId);
        $I->assertEquals(479.0, $basketCosts['productGross']['sum']);
        $I->assertEquals(7.5, $basketCosts['payment']['price']);
        $I->assertEquals(0.0, $basketCosts['delivery']['price']);
        $I->assertEquals(0.0, $basketCosts['voucher']);
        $I->assertEquals(47.9, $basketCosts['discount']); //this is sum of all discounts, including vouchers
        $I->assertEquals(438.6, $basketCosts['total']);

        //place the order
        $result  = $this->placeOrder($I, $basketId);
        $orderId = $result['data']['placeOrder']['id'];

        //check order history
        $orders = $this->getOrderFromOrderHistory($I);
        $I->assertEquals($orderId, $orders['id']);
        $I->assertEquals($basketCosts['total'], $orders['cost']['total']);
        $I->assertEquals($basketCosts['discount'], $orders['cost']['discount']);
        $I->assertEquals($basketCosts['voucher'], $orders['cost']['voucher']);

        //remove basket
        $this->removeBasket($I, $basketId, self::USERNAME);

        $I->updateInDatabase('oxdiscount', ['oxactive' => 0]);
        $I->updateInDatabase('oxdiscount', ['oxactive' => 1], ['oxid' => self::DEFAULT_DISCOUNT_ID]);
    }

    public function placeOrderAndNoCalculateDelCostIfNotLoggedIn(AcceptanceTester $I): void
    {
        $I->wantToTest('that blCalculateDelCostIfNotLoggedIn has no effect on placeOrder');
        $I->updateConfigInDatabase('blCalculateDelCostIfNotLoggedIn', true, 'bool');

        $I->login(self::USERNAME, self::PASSWORD);

        //prepare basket with invoice address
        $basketId = $this->createBasket($I, 'my_cart_del_cost_flag');
        $this->addProductToBasket($I, $basketId, self::PRODUCT_ID, 2);
        $this->setBasketDeliveryMethod($I, $basketId, self::SHIPPING_STANDARD);
        $this->setBasketPaymentMethod($I, $basketId, self::PAYMENT_STANDARD);

        //check the basket costs
        $basketCosts = $this->queryBasketCost($I, $basketId);
        $I->assertEquals(59.8, $basketCosts['productGross']['sum']);
        $I->assertEquals(7.5, $basketCosts['payment']['price']);
        $I->assertEquals(3.9, $basketCosts['delivery']['price']);
        $I->assertEquals(0, $basketCosts['voucher']);
        $I->assertEquals(0, $basketCosts['discount']);
        $I->assertEquals(71.2, $basketCosts['total']);

        //place the order
        $result  = $this->placeOrder($I, $basketId);
        $orderId = $result['data']['placeOrder']['id'];

        //check order history
        $orders = $this->getOrderFromOrderHistory($I);
        $I->assertEquals($orderId, $orders['id']);
        $I->assertEquals($basketCosts['total'], $orders['cost']['total']);

        //remove basket
        $this->removeBasket($I, $basketId, self::USERNAME);
    }

    public function placeOrderWithConfirmAGB(): void
    {
        //TODO: blConfirmAGB
    }

    public function placeOrderWithDownloadableProduct(AcceptanceTester $I): void
    {
        $I->wantToTest('placing an order on downloadable product');
        $I->login(self::USERNAME, self::PASSWORD);

        //prepare basket
        $basketId = $this->createBasket($I, 'cart_with_files');
        $this->addProductToBasket($I, $basketId, self::DOWNLOADABLE_FILE, 1);
        $this->setBasketDeliveryMethod($I, $basketId, self::SHIPPING_STANDARD);
        $this->setBasketPaymentMethod($I, $basketId, self::PAYMENT_STANDARD);

        //check the basket costs
        //NOTE: usually you won't use cash on delivery with downloads but shop allows it also in standard checkout
        $basketCosts = $this->queryBasketCost($I, $basketId);
        $I->assertEquals(0, $basketCosts['productGross']['sum']);
        $I->assertEquals(7.5, $basketCosts['payment']['price']);
        $I->assertEquals(0, $basketCosts['delivery']['price']);
        $I->assertEquals(0, $basketCosts['voucher']);
        $I->assertEquals(0, $basketCosts['discount']);
        $I->assertEquals(7.5, $basketCosts['total']);

        //place the order
        $result  = $this->placeOrder($I, $basketId);
        $orderId = $result['data']['placeOrder']['id'];

        //check order history
        $orders = $this->getOrderFromOrderHistory($I);
        $I->assertEquals($orders['id'], $orderId);
        $I->assertEquals($orders['cost']['total'], $basketCosts['total']);

        //remove basket
        $this->removeBasket($I, $basketId, self::USERNAME);
    }

    public function placeOrderWithBelowMinPriceBasket(AcceptanceTester $I): void
    {
        $I->wantToTest('placing an order when basket total is below minimum price');
        $I->login(self::USERNAME, self::PASSWORD);

        //prepare basket
        $basketId = $this->createBasket($I, 'cart_below_min_price');
        $this->addProductToBasket($I, $basketId, self::PRODUCT_ID, 1);
        $this->setBasketDeliveryMethod($I, $basketId, self::SHIPPING_STANDARD);
        $this->setBasketPaymentMethod($I, $basketId, self::PAYMENT_STANDARD);

        // change minimum price to place an order
        $I->updateConfigInDatabase('iMinOrderPrice', '100', 'str');

        //place the order
        $this->placeOrder($I, $basketId, HttpCode::BAD_REQUEST);

        //remove basket
        $this->removeBasket($I, $basketId, self::USERNAME);
        $I->updateConfigInDatabase('iMinOrderPrice', '0', 'str');
    }

    public function placeOrderOnOutOfStockNotBuyableProduct(AcceptanceTester $I): void
    {
        $I->wantToTest('placing an order on a product which is out of stock or not buyable');
        $I->login(self::USERNAME, self::PASSWORD);

        //prepare basket
        $basketId = $this->createBasket($I, 'cart_with_not_buyable_product');
        $this->addProductToBasket($I, $basketId, self::PRODUCT_ID, 5);
        $this->setBasketDeliveryMethod($I, $basketId, self::SHIPPING_STANDARD);
        $this->setBasketPaymentMethod($I, $basketId, self::PAYMENT_STANDARD);

        // making product out of stock now
        $I->updateInDatabase('oxarticles', ['oxstock' => '3', 'oxstockflag' => '3'], ['oxid' => self::PRODUCT_ID]);

        //place the order
        $this->placeOrder($I, $basketId, HttpCode::BAD_REQUEST);

        //remove basket
        $this->removeBasket($I, $basketId, self::USERNAME);
        $I->updateInDatabase('oxarticles', ['oxstock' => '15', 'oxstockflag' => '1'], ['oxid' => self::PRODUCT_ID]);
    }

    public function placeOrderWithoutDeliveryMethod(AcceptanceTester $I): void
    {
        $I->wantToTest('placing an order without selected delivery method');

        $I->login(self::USERNAME, self::PASSWORD);

        //prepare basket
        $basketId = $this->createBasket($I, 'no_delivery_method');
        $this->addProductToBasket($I, $basketId, self::PRODUCT_ID, 1);

        //place the order
        $this->placeOrder($I, $basketId, HttpCode::BAD_REQUEST);
    }

    public function placeOrderWithoutPaymentMethod(AcceptanceTester $I): void
    {
        $I->wantToTest('placing an order without selected payment method');

        $I->login(self::USERNAME, self::PASSWORD);

        //prepare basket
        $basketId = $this->createBasket($I, 'no_payment_method');
        $this->addProductToBasket($I, $basketId, self::PRODUCT_ID, 1);
        $this->setBasketDeliveryMethod($I, $basketId, self::SHIPPING_STANDARD);

        //place the order
        $this->placeOrder($I, $basketId, HttpCode::BAD_REQUEST);
    }
}
