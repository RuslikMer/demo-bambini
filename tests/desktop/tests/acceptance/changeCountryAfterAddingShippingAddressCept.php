<?php
//@group paracept_2
$I = new AcceptanceTester($scenario);
$I->am("not authorized user");
$I->wantTo('change country after adding shipping address');
$I->openHomePage();
$I->selectCategoryHomePage(1);
$I->goToProductFromListing(1);
$I->addToCartFromProductPage();
$I->goToCheckout();
$I->guestCheckout();
$I->fillContacts();
$I->chooseShippingMethod();
$I->amOnPage('');
$I->changeCountry('Vietnam');