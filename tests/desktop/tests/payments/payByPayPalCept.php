<?php
//@group paracept_2
$I = new AcceptanceTester($scenario);
$I->am("not authorized user");
$I->wantTo('check PayPal checkout');
$I->openHomePage();
$I->cleanCart();
$I->goToMenuCategory(\Pages\Menu::SHOES);
$I->goToProductFromListing(1);
$I->addToCartFromProductPage();
$I->goToCheckout();
$I->guestCheckout();
$I->fillContacts();
$I->chooseShippingMethod();
$I->choosePayment(\Pages\Checkout::PAYPAL);
$I->confirmOrder();
$I->fillPayPal();
$I->seeThankYouPage();