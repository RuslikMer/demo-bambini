<?php
namespace Pages;

class Login
{
    // include url of current page
    public static $URL = '/account/sign-in/';

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
     * Login.
     *
     * @param string $name
     * @param string $password
     * @throws \Exception
     */
    public function doLogin($name, $password)
    {
        $I = $this->tester;

        $this->openLoginPage();
        $I->submitForm('form.sign-in-form', [
            'email' => $name,
            'password' => $password
        ], 'submit');
    }

    /**
     * Open login page.
     *
     * @throws \Exception
     */
    public function openLoginPage()
    {
        $I = $this->tester;

        $authForm = $I->getNumberOfElements('form.sign-in-form');
        $checkOut = $I->getNumberOfElements('h1.checkout-header-title');
        if (empty($checkOut) && empty($authForm)) {
            $I->waitAndClick(Profile::PROFILE_TOP, 'profile');
        }

        $I->waitForVisible('form.sign-in-form', 'sign in form');
    }

    /**
     * Open forgot password form.
     *
     * @throws \Exception
     */
    public function forgotPassword()
    {
        $I = $this->tester;

        $str = $I->generateString(5);
        $I->waitAndClick('//button[contains(.,"Forgot password?")]', 'forgot password');
        $I->waitOverlayLoader();
        $I->waitAndFill('//div[@class="form-block"]//input[@name="email"]', 'email', $str.'@bambinifashion.com');
        $I->wait(SHORT_WAIT_TIME);
        $I->waitAndClick('//button[contains(.,"Submit")]', 'submit password reset');
        $I->waitForVisible('//button[contains(.,"Forgot password?")]', '"Forgot password?" button');
    }

    /**
     * Sign-in instead from reset password page.
     *
     * @throws \Exception
     */
    public function signInInstead()
    {
        $I = $this->tester;

        $I->waitAndClick('//button[contains(.,"Forgot password?")]', 'forgot password');
        $I->waitOverlayLoader();
        $I->waitAndClick('//a[contains(.,"Sign-in instead")]', 'sign-in instead');
        $I->waitForNotVisible('//a[contains(.,"Sign-in instead")]', '"Sign-in instead" button');
    }

    /**
     * Logout user.
     *
     * @throws \Exception
     */
    public function doLogout()
    {
        $I = $this->tester;

        $I->goToProfile();
        $I->waitAndClick('//button[contains(.,"Logout")]', 'Logout');
        $I->waitForVisible('form.sign-in-form', 'sign in form');
    }

    /**
     * Checking an erroneous login / password entry.
     *
     * @throws \Exception
     */
    public function doFailLogin()
    {
        $I = $this->tester;

        $I->lookForwardTo('do fail login');
        $this->doLogin(BUYER_EMAIL, '123456');
        $I->waitForVisible('//h2[.="Ooops!"]', '"Ooops!" message');
        $I->waitAndClick('//button[contains(.,"Close")]', 'close popup');
    }
}