<?php
//@group paracept_1
$I = new AcceptanceTester($scenario);
$I->am("not authorized user");
$I->wantTo('add to cart from wishlist');
$I->openHomePage();
