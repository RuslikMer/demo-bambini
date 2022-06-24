<?php
//@group paracept_1
$I = new AcceptanceTester($scenario);
$I->am("authorized user");
$I->wantTo('checkout by card with auth');
$I->openHomePage();
$I->goToMenuCategory(\Pages\Menu::GIRL);
$I->goToProductFromListing();
$I->addToCartFromProductPage();
$I->goToCheckout();
$I->doLogin();
$I->fillContacts();
$I->chooseShippingMethod();
$I->fillCardData(BUYER_STRIPES['auth']);
$I->confirmOrder();
$I->complete3DSecure(false);
$I->confirmOrder();
$I->complete3DSecure();
$I->seeThankYouPage();
$orderNum = $I->getOrderNumber();
$I->viewOrder($orderNum);
$I->viewMyOrders();
