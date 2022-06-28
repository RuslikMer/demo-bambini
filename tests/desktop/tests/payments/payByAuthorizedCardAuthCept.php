<?php
//@group paracept_1
$I = new AcceptanceTester($scenario);
$I->am("authorized user");
$I->wantTo('checkout by card with auth');
$I->openHomePage();

