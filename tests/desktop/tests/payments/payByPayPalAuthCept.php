<?php
//@group paracept_1
$I = new AcceptanceTester($scenario);
$I->am("authorized user");
$I->wantTo('check PayPal checkout');
$I->openHomePage();

