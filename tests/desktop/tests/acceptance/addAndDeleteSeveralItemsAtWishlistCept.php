<?php
//@group paracept_2

$I = new AcceptanceTester($scenario);
$I->am("not authorized user");
$I->wantTo('add several products to wishlist and remove one of them');
$I->openHomePage();
for ($i = 1; $i < 4; $i++) {
    $I->goToMenuCategory(\Pages\Menu::NEW_IN);
    $I->goToProductFromListing($i);
    $I->addToWishList();
}

$I->goToWishList();
$I->deleteProductFromWishList();
$I->goToProductFromWishList();
$I->removeFromWishListAtProductPage();