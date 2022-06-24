<?php
//@group paracept_2
$I = new AcceptanceTester($scenario);
$I->am("not authorized user");
$I->wantTo('checkout by card with auth');
$I->openHomePage();
$I->goToMenuCategory(\Pages\Menu::GIRL);
$I->goToProductFromListing();
$I->addToCartFromProductPage();
$I->goToCheckout();
$I->guestCheckout();
$I->fillContacts();
$I->chooseShippingMethod();
$I->fillCardData(BUYER_STRIPES['auth']);
$I->confirmOrder();
$I->complete3DSecure(false);
$I->confirmOrder();
$I->complete3DSecure();
$I->seeThankYouPage();