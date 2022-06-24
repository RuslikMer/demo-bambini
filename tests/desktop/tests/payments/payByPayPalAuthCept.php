<?php
//@group paracept_1
$I = new AcceptanceTester($scenario);
$I->am("authorized user");
$I->wantTo('check PayPal checkout');
$I->openHomePage();
$I->doLogin();
$I->cleanCart();
$I->goToMenuCategory(\Pages\Menu::SHOES);
$I->goToProductFromListing(1);
$I->addToCartFromProductPage();
$I->goToCheckout();
$I->fillContacts();
$I->chooseShippingMethod();
$I->choosePayment(\Pages\Checkout::PAYPAL);
$I->confirmOrder();
$I->fillPayPal();
$I->seeThankYouPage();
