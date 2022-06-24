<?php
namespace Pages;

class Profile
{
    // include url of current page
    public static $URL = '/account/';

    //constants
    const PROFILE_TOP = 'a.top-user-account';

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
     * Select order.
     *
     * @throws \Exception
     */
    public function selectOrder()
    {
        $I = $this->tester;

        $I->waitForVisible('span.order-reference', 'order reference');
        $orders = $I->grabMultiple('span.order-reference');
        codecept_debug($orders);
        $order = $orders[mt_rand(0, count($orders)-1)];
        $I->waitAndClick('//span[.="'.$order.'"]', 'select order '.$order);
        $I->waitForVisible('//div[@class="order-summary"]//span[.="'.$order.'"]', 'order summary '.$order);
    }

    /**
     * Go to product card from order.
     *
     * @throws \Exception
     */
    public function goToProductFromOrder()
    {
        $I = $this->tester;

        $I->lookForwardTo('go to product from order');
        $I->waitAndClick('div.cart-item-image-wrapper', 'go to order');
        $I->waitForVisible('div.product-container', 'product card');
    }

    /**
     * Add new delivery address.
     *
     * @throws \Exception
     */
    public function addDeliveryAddress()
    {
        $I = $this->tester;

        $this->goToTabFromNavigationPanel('Addresses');
        $I->waitAndClick('//button[contains(.,"Add New Address")]', 'Add New Address button');
        $I->fillContacts();
    }

    /**
     * View my orders.
     *
     * @throws \Exception
     */
    public function viewMyOrders()
    {
        $I = $this->tester;

        $I->waitAndClick('//a[contains(.,"View my Orders")]', 'View my Orders');
        $I->waitForVisible('//h1[.="My Orders"]', '"My Orders" title');
    }

    /**
     * Remove last shipping address.
     *
     * @throws \Exception
     */
    public function removeDeliveryAddress()
    {
        $I = $this->tester;

        $this->goToTabFromNavigationPanel('Addresses');
        $I->waitAndClick('//section[contains(@class, "address-display")][last()]//button[.="Remove"]', 'remove button');
    }

    /**
     * Change password.
     *
     * @param string $newPass
     * @throws \Exception
     */
    public function changePassword($newPass)
    {
        $I = $this->tester;

        $I->waitAndClick('//button[.="Change my password"]', 'change password button');
        $I->waitAndFill(['name' => 'password'], 'new password', $newPass);
        $I->waitAndFill(['name' => 'password_confirmation'], 'password confirmation', $newPass);
    }

    /**
     * Change user data.
     *
     * @param string $name
     * @param string $lastName
     * @param string $email
     * @param string $newPass
     * @throws \Exception
     */
    public function changeUserData($name, $lastName, $email, $newPass)
    {
        $I = $this->tester;

        $this->goToTabFromNavigationPanel('Personal information');
        $I->waitAndFill(['name' => 'fname'], 'name', $name);
        $I->waitAndFill(['name' => 'lname'], 'lastName', $lastName);
        $I->waitAndFill(['name' => 'email'], 'email', $email);
        $this->changePassword($newPass);
        $I->waitAndClick('//button[contains(.,"Update")]', 'button update data');
    }

    /**
     * Go to a tab from the navigation bar.
     *
     * @param string $tabName
     * @throws \Exception
     */
    public function goToTabFromNavigationPanel($tabName)
    {
        $I = $this->tester;

        $I->waitAndClick('//nav[contains(@class, "aside-nav-list--account")]//a[.="'.$tabName.'"]', 'navigation panel button');
        $I->waitForVisible('//nav[contains(@class, "aside-nav-list--account")]//a[.="'.$tabName.'"][contains(@class, "is-active")]', 'selected tab '.$tabName);
    }
}