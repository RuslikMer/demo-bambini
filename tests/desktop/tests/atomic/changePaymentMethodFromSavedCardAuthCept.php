<?php
//@group paracept_1

$I = new AcceptanceTester($scenario);
$I->am("authorized user");
$I->wantTo('change payment method saved card');
$I->openHomePage();
$I->doLogin();
$I->cleanCart();
$I->doSavedCard();
$I->addPaymentMethod();
$I->choosePayment(\Pages\Checkout::PAYPAL);
$I->deleteSavedCard();