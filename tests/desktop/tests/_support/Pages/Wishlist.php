<?php
namespace Pages;

class Wishlist
{
    // include url of current page
    public static $URL = '/wishlist/';

    //constants
    const WISHLIST_TOP = 'a.top-user-wishlist';
    const PRODUCT_CARD = '//div[contains(@class,"product-card wishlist-product")]';

    /**
     * Basic route example for your current URL
     * You can append any additional parameter to URL
     * and use it in tests like: Page\Edit::route('/123-post');
     */
    public static function route($param)
    {
        return static::$URL.$param;
    }

    /**
     * @var AcceptanceTester
     */
    protected $tester;

    public function __construct(\AcceptanceTester $I)
    {
        $this->tester = $I;
    }

    /**
     * Remove item.
     *
     * @param int $itemNum
     * @throws \Exception
     */
    public function deleteProduct($itemNum)
    {
        $I = $this->tester;

        if (is_null($itemNum)) {
            $itemNum = $I->getRandomItemNumber(self::PRODUCT_CARD);
        }

        $name = $I->grabTextFrom(self::PRODUCT_CARD.'[' . $itemNum . ']'.Listing::PRODUCT_TITLE);
        $I->waitAndClick(self::PRODUCT_CARD.'[' . $itemNum . ']//i[contains(@class,"icon-plus")]', 'expand product card');
        $I->waitAndClick(self::PRODUCT_CARD.'[' . $itemNum . ']//button[@title="Remove product from Wishlist"]', "remove button");
        $I->waitForNotVisible(self::PRODUCT_CARD.'[contains(.,"'.$name.'")]', 'item name '.$name);
    }

    /**
     * Go to product.
     *
     * @param int $itemNum
     * @throws \Exception
     */
    public function goToProduct($itemNum)
    {
        $I = $this->tester;

        if (is_null($itemNum)) {
            $itemNum = $I->getRandomItemNumber(self::PRODUCT_CARD);
        }

        $name = $I->grabTextFrom(self::PRODUCT_CARD.'[' . $itemNum . ']'.Listing::PRODUCT_TITLE);
        $I->waitAndClick(self::PRODUCT_CARD.'[' . $itemNum . ']//i[contains(@class,"icon-plus")]', 'expand product card');
        $I->waitAndClick(self::PRODUCT_CARD.'[' . $itemNum . ']//a', 'go to product card');
        $I->waitForVisible('//h1[.="'.$name.'"]', 'item name '.$name);
    }
}