<?php
namespace Pages;

class Brands
{
    // include url of current page
    public static $URL = '/designers/';

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
     * Random selection from the alphabet panel,
     * then randomly select a brand from the alphabetical section.
     *
     * @throws \Exception
     */
    public function selectBrand()
    {
        $I = $this->tester;

        $letters = $I->grabMultiple('header.designers-letter');
        $letter = array_rand(array_flip($letters));
        $brands = $I->grabMultiple('//header[.="'.$letter.'"]//following-sibling::ul//a');
        $brand = array_rand(array_flip($brands));
        $I->waitAndClick('//header[.="'.$letter.'"]//following-sibling::ul//a[contains(.,"'.$brand.'")]', $brand.' brand');
        $I->waitForVisible('div.category-list', 'category list');
    }
}