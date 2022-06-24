<?php
namespace Pages;

class Menu
{
    // include url of current page
    public static $URL = '';

    //constants
    const NEW_IN = 'new-in';
    const DESIGNERS = 'designers';
    const BABY = 'baby';
    const BOY = 'boy';
    const GIRL = 'girl';
    const SHOES = 'shoes';
    const LIFESTYLE = 'lifestyle';
    const OUTLET = 'season-sale';

    const HOVER = '//li[contains(@class,"has-hover")]';

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
     * Go to category.
     *
     * @param string $category name of category
     * @throws \Exception
     */
    public function goToMenuCategory($category)
    {
        $I = $this->tester;

        $I->waitForVisible('ul.nav--desktop-list', 'menu navigation');
        $I->waitAndClick('//li[contains(@class,"'.$category.'")]', 'go to '.$category);
        $this->categoryCheck($category);
    }

    /**
     * Open menu hover.
     *
     * @param string $category name of category
     * @throws \Exception
     */
    public function openMenuHover($category)
    {
        $I = $this->tester;

        $I->waitForVisible('ul.nav--desktop-list', 'menu navigation');
        $I->moveMouseOver('//li[contains(@class,"'.$category.'")]');
        $I->waitForVisible(self::HOVER, 'menu hover');
    }

    /**
     * Go to subcategory.
     *
     * @param string $category
     * @return string $subcategory
     * @throws \Exception
     */
    public function goToSubcategory($category)
    {
        $I = $this->tester;

        $this->openMenuHover($category);
        $type = $this->getSubCategoriesType();
        $subcategories = $I->grabMultiple(self::HOVER.'//div[contains(@class,"section")][contains(.,"'.ucfirst(strtolower($type)).'")]//a[@class=""]');
        codecept_debug($subcategories);
        $subcategory = $subcategories[mt_rand(0, count($subcategories)-1)];
        $I->waitAndClick(self::HOVER.'//a[@class=""][contains(.,"'.$subcategory.'")]', 'go to '.$subcategory, true);
        if (!empty(preg_match("/[\d]+/", $subcategory, $match))) {
            $I->waitForVisible('//span[contains(@class, "desktop-toggle-text-inner-counter")] | //a[@data-checked="true"]', 'selected subcategory');
        } else {
            $I->waitForVisible('//h1[contains(.,"' . $subcategory . '")]', 'subcategory '. $subcategory);
        }

        return $subcategory;
    }

    /**
     * Get subcategories type.
     *
     * @return string $type
     * @throws \Exception
     */
    public function getSubCategoriesType()
    {
        $I = $this->tester;

        $types = $I->grabMultiple(self::HOVER.'//div[contains(@class,"dropmenu-title")]');
        codecept_debug($types);
        $type = $types[mt_rand(0, count($types)-1)];

        return $type;
    }

    /**
     * Go to category by "viewAll".
     *
     * @param string $category name of category
     * @throws \Exception
     */
    public function viewAll($category)
    {
        $I = $this->tester;

        $this->openMenuHover($category);
        $type = $this->getSubCategoriesType();
        $I->waitAndClick(self::HOVER.'//div[contains(@class,"section")][contains(.,"'.ucfirst(strtolower($type)).'")]//a[contains(@class,"list-all")]', 'view all');
        $this->categoryCheck($category);
    }

    /**
     * Go to category by banner.
     *
     * @param string $category name of category
     * @throws \Exception
     */
    public function goToMenuCategoryByBanner($category)
    {
        $I = $this->tester;

        $I->waitForVisible('ul.nav--desktop-list', 'menu navigation');
        $I->moveMouseOver('//li[contains(@class,"'.$category.'")]');
        $I->waitForElementClickable(self::HOVER);
        $I->waitAndClick(self::HOVER.'//figcaption', 'go to '.$category, true);
        $this->categoryCheck($category);
    }

    /**
     * Check transition to category.
     *
     * @param string $category name of category
     * @throws \Exception
     */
    public function categoryCheck($category)
    {
        $I = $this->tester;

        $I->wait(SHORT_WAIT_TIME);
        $I->waitForNotVisible('div.nuxt-progress', 'overlay');
        $I->waitForVisible('//div[@class="category-list"] | //div[@class="designers-container"]', 'listing or designers');
        $url = $I->getCurrentUrlJS();
        if(!preg_match('/'.$category.'/', $url)) {
            Throw new \Exception("Wrong category url!");
        };
        //$I->assertContains($category, $url, "Wrong category url!");
    }

    /**
     * See proline.
     *
     * @throws \Exception
     */
    public function seeProline()
    {
        $I = $this->tester;

        $I->waitForVisible('div.proline-container', 'proline');
    }
}