<?php
//@group paracept_1

$I = new AcceptanceTester($scenario);
$I->am("authorized user");
$I->wantTo('add and delete saved card');
$I->openHomePage();
