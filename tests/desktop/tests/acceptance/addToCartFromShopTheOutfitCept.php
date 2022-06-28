<?php
//@group paracept_1

$I = new AcceptanceTester($scenario);
$I->am("not authorized user");
$I->wantTo('add to cart from "shop the outfit" block');
$I->openHomePage();
$I->amOnPage('dsquared2/logo-sneakers-in-black-35896.html');
