<?php
//@group paracept_2
$I = new AcceptanceTester($scenario);
$I->am("not authorized user");
$I->wantTo('change country after adding shipping address');
$I->openHomePage();
