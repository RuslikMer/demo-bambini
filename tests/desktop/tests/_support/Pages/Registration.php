<?php
namespace Pages;

class Registration
{
    // include url of current page
    public static $URL = '/account/register/';

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
     * Open registration form.
     *
     * @throws \Exception
     */
    public function openRegistrationForm()
    {
        $I = $this->tester;

        $I->openLoginPage();
        $I->waitAndClick('//a[.="Register"]', 'registration link');
        $I->waitForVisible('//h1[.="New Account"]', '"New Account" title');
    }

    /**
     * Filling in contact information.
     *
     * @param string $email
     * @param string $name
     * @param string $lastname
     * @param string $password
     * @param bool $fail
     * @return string $email
     * @throws \Exception
     */
    public function fillRegistrationContactForm($email, $name, $lastname, $password, $fail)
    {
        $I = $this->tester;

        if (is_null($email)) {
            $str = $I->generateString(5);
            $email = $str.'@bambinifashion.com';
        }

        $I->waitAndFill(['name' => 'fname'], 'registration name', $name, true);
        $I->waitAndFill(['name' => 'lname'], 'registration $lastname', $lastname, true);
        $I->waitAndFill(['name' => 'email'], 'registration email', $email, true);
        $I->waitAndFill(['name' => 'password'], 'registration password', $password, true);
        $I->waitAndFill(['name' => 'password_confirmation'], 'repeat password', $password, true);
        $I->waitAndClick('//button[contains(.,"Create Account")] | //button[contains(.,"Upgrade Account")]', 'submit');
        if ($fail) {
            $I->dismissAlert('We already have this e-mail address registered for an account.');
        } else {
            $I->waitForVisible('//h1[contains(.,"Welcome")]', '"Welcome" title');
        }

        return $email;
    }
}