<?php
//@group paracept_2

$I = new AcceptanceTester($scenario);
$I->am("not authorized user");
$I->wantTo('add several products to wishlist and remove one of them');
$I->openHomePage();
