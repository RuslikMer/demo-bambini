<?php
//@group paracept_2

$I = new AcceptanceTester($scenario);
$I->am("not authorized user");
$I->wantTo('change payment method saved card');
$I->openHomePage();
$I->doSavedCard(false);
$I->addPaymentMethod();
$I->choosePayment(\Pages\Checkout::PAYPAL);
$I->deleteSavedCard();