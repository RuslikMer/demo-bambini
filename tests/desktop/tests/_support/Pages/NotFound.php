<?php
namespace Pages;

class NotFound
{
    // include url of current page
    public static $URL = '/not-found/';

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
     * Back to home.
     *
     * @throws \Exception
     */
    public function returnHome()
    {
        $I = $this->tester;

        $I->waitForVisible('//h1[.="Page not found"]', '"Page not found" title');
        $I->waitAndClick('//span[.="Return home"]', 'Return home');
    }
}