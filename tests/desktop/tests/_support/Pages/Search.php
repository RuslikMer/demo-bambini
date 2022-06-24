<?php
namespace Pages;

use Facebook\WebDriver\WebDriverKeys;

class Search
{
    // include url of current page
    public static $URL = '/search/';

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
     * Filling in the search string.
     *
     * @param string $text
     * @param bool $submit pressing Enter
     * @throws \Exception
     */
    public function searchString($text, $submit)
    {
        $I = $this->tester;

        $I->waitAndClick('//li[contains(@class,"search")]', 'open search');
        $I->waitAndFill('#search-input', 'search field', $text);
        if ($submit) {
            $I->pressKey('#search-input', WebDriverKeys::ENTER);
            $I->waitOverlayLoader();
            $searchResult = $I->grabTextFrom('//h1');
            if(!preg_match('/'.strtoupper($text).'/', $searchResult)) {
                Throw new \Exception("wrong request result");
            };
            //$I->assertContains(strtoupper($text), $searchResult, 'wrong request result');
        }
    }

    /**
     * Go to result from quick search.
     *
     * @throws \Exception
     */
    public function goToResultFromFastSearch()
    {
        $I = $this->tester;

        $resultsCount = $I->getNumberOfElements('//div[@class="search-list-item"]', 'products in fast search');
        $rndNum = mt_rand(1, $resultsCount);
        $text = $I->grabTextFrom('//div[@class="search-list-item"][' . $rndNum . ']');
        $I->waitAndClick('//div[@class="search-list-item"][' . $rndNum . ']', 'select result by num');
        $I->waitOverlayLoader();
        $searchResult = $I->grabTextFrom('//h1');
        if(!preg_match('/'.strtoupper($text).'/', $searchResult)) {
            Throw new \Exception("wrong request result");
        };
        //$I->assertContains(strtoupper($text), [$searchResult], 'wrong request result');
    }
}