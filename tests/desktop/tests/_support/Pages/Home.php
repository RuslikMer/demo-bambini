<?php
namespace Pages;

use Exception;

class Home
{
    // include url of current page
    public static $URL = '/';

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
     * Opening the main page of the site and checking for the presence of basic elements.
     *
     * @param string $extraUrl
     * @param array $cookie ['param', 'value']
     * @throws Exception
     */
    public function open($cookie, $extraUrl) {
        $I = $this->tester;

        $I->amOnPage($extraUrl);
        if (!is_null($cookie)){
            $I->resetCookie($cookie[0]);
            $I->setCookie($cookie[0], $cookie[1]);
            $I->reloadPage();
        }
    }

    /**
     * Confirmation for the processing of personal data.
     *
     * @throws Exception
     */
    public function confirmProcessingPersonalData()
    {
        $I = $this->tester;

        $I->waitForVisible('div.gdpr-sheet', 'cookie confirmation');
        $I->waitAndClick('//button[.="I agree"]', 'confirm button');
        $I->waitForNotVisible('div.gdpr-sheet', 'cookie confirmation');
    }

    /**
     * Confirmation for the processing of personal data.
     *
     * @throws Exception
     */
    public function closeOverlayBanner()
    {
        $I = $this->tester;

        $I->waitForVisible('#__overlay', 'overlay');
        $I->waitAndClick('//button[contains(@class,"overlay--popup-dismiss")]', 'dismiss banner');
        $I->waitForNotVisible('#__overlay', 'overlay');
    }

    /**
     * Checking required elements on site pages.
     *
     * @throws Exception
     */
    public function checkMandatoryElements()
    {
        $I = $this->tester;

        $I->waitForVisible('i.icon-logo.top-logo-icon', 'logo');
        $I->waitForVisible('footer.footer', 'footer');
    }

    /**
     * Checking footer methods block.
     *
     * @throws Exception
     */
    public function checkFooterMethodsBlock()
    {
        $I = $this->tester;

        $paymentTypes = ['VISA', 'Mastercard', 'American Express', 'Diners Club International', 'JCB International', 'UnionPay International', 'PayPal'];
        $deliveryTypes = ['DHL Express', 'UPS', 'FedEx Express'];
        $socials = ['Facebook', 'Instagram', 'Pinterest', 'WhatsApp'];

        $I->waitForVisible('//div[contains(@class,"footer-container--methods")]', 'footer');
        foreach ($paymentTypes as $str) {
            $I->waitForVisible('//div[contains(@class,"footer-block--payment")]//*[@title="'.$str.'"]', 'payment '.$str);
        }

        foreach ($deliveryTypes as $str) {
            $I->waitForVisible('//div[contains(@class,"footer-block--delivery")]//*[@title="'.$str.'"]', 'delivery '.$str);
        }

        foreach ($socials as $str) {
            $I->waitForVisible('//div[contains(@class,"footer-block--social")]//a[@aria-label="'.$str.'"]', 'social '.$str);
        }
    }

    /**
     * Select category from any section.
     *
     * @param int $sectionNum
     * @param string $sectionsPath
     * @throws Exception
     */
    public function selectCategory($sectionNum, $sectionsPath)
    {
        $I = $this->tester;

        $I->waitForVisible($sectionsPath, 'category');
        if (is_null($sectionNum)) {
            $sectionsCount = $I->getNumberOfElements($sectionsPath);
            $sectionNum = mt_rand(1, $sectionsCount);
            if ($sectionNum == 1) {
                $sectionNum = 2;
            }
        }

        $categoriesCount = $I->getNumberOfElements($sectionsPath.'['.$sectionNum.']//figure');
        $categoryNum = mt_rand(1, $categoriesCount);
        $I->scrollTo('('.$sectionsPath.'['.$sectionNum.']//figure)['.$categoryNum.']');
        $I->waitAndClick('('.$sectionsPath.'['.$sectionNum.']//figure)['.$categoryNum.']', 'select category');
        $I->waitForNotVisible($sectionsPath, 'category');
    }

    /**
     * Go to top user menu.
     *
     * @param string $locator
     * @throws Exception
     */
    public function goToUserMenu($locator)
    {
        $I = $this->tester;

        $I->waitForVisible('ul.top-user', 'link to account in header');
        $I->waitAndClick('a.top-user-'.$locator, 'go to '.$locator);
    }

    /**
     * Set Austria.
     *
     * @throws Exception
     */
    public function setDefaultCountry()
    {
        $I = $this->tester;

        $country = $I->getCurrentCountry();
        if ($country != 'Austria') {
            $this->changeCountry('Austria');
        }
    }

    /**
     * Set current country.
     *
     * @return string $country
     * @throws Exception
     */
    public function getCurrentCountry()
    {
        $I = $this->tester;

        $country = trim($I->grabTextFrom('#localization-dropdown-toggle--country'));

        return $country;
    }

    /**
     * Change country.
     *
     * @param string $country
     * @throws Exception
     */
    public function changeCountry($country)
    {
        $I = $this->tester;

        $countryTopPath = '//button[@id="localization-dropdown-toggle--country"]';
        $I->waitAndClick($countryTopPath, 'open countries list');
        $I->waitForVisible('ul.localization-dropdown-list', 'country list');
        if (!empty(preg_match('/[A-Z]{2}/', $country, $output_array))) {
            $I->waitAndClick('//button[@data-iso-code="'.$country.'"]', 'change country');
            if (!empty($I->getNumberOfElements('//div[@id="__overlay"]'))) {
                $I->changeShippingByAlert();
            }

            $I->waitForVisible($countryTopPath.'[@data-iso-code="'.$country.'"]', 'country '.$country);
        } else {
            $I->waitAndClick('//button[contains(.,"' . $country . '")]', 'change country');
            if (!empty($I->getNumberOfElements('//div[@id="__overlay"]'))) {
                $I->changeShippingByAlert();
            }

            $I->waitForVisible($countryTopPath.'[contains(.,"'.$country.'")]', 'country '.$country);
        }

        $I->waitPage();
        $I->wait(SHORT_WAIT_TIME);
    }

    /**
     * Change currency.
     *
     * @param string $currency Iso-code
     * @return string $symbol
     * @throws Exception
     */
    public function changeCurrency($currency)
    {
        $I = $this->tester;

        $I->waitAndClick('#localization-dropdown-toggle--currency', 'open currencies list');
        $I->waitForVisible('ul.localization-dropdown-list', 'currency list');
        if (is_null($currency)) {
            $currencyCount = $I->getNumberOfElements('//ul[@class="localization-dropdown-list"]//button');
            $rndNum = mt_rand(1, $currencyCount);
            $currency = $I->grabAttributeFrom('(//ul[@class="localization-dropdown-list"]//button)['.$rndNum.']', 'data-iso-code');
        }

        $I->waitAndClick('//button[@data-iso-code="'.$currency.'"]', 'change currency');
        $I->waitForVisible('//button[@id="localization-dropdown-toggle--currency"][@data-iso-code="'.$currency.'"]', 'currency '.$currency);
        $symbol = strtok($I->grabTextFrom('#localization-dropdown-toggle--currency'), ' ');
        $I->wait(SHORT_WAIT_TIME);

        return $symbol;
    }

    /**
     * Change language.
     *
     * @throws Exception
     */
    public function changeLanguage()
    {
        $I = $this->tester;

        $I->wait(SHORT_WAIT_TIME);
        $I->waitAndClick('#top-localization-dropdown-toggle--language', 'open languages list');
        $I->waitForVisible('div.top-localization-dropdown-inner', 'languages list');
        $I->waitAndClick('button.top-localization-dropdown-button', 'change language', true);
        $I->waitForNotVisible('div.top-localization-dropdown-inner', 'languages list');
    }

    /**
     * Go to link from information.
     *
     * @param string $linkName
     * @param string $xpath path to block with links
     * @throws Exception
     */
    public function goToLinkFromInformation($linkName, $xpath)
    {
        $I = $this->tester;

        $I->waitAndClick($xpath.'//li[contains(.,"'.$linkName.'")]//a', 'go to '.$linkName);
        $I->waitForVisible($xpath.'//a[contains(@class,"is-active")][contains(.,"'.$linkName.'")]', 'link '.$linkName);
    }

    /**
     * Subscribe news letter.
     *
     * @param string $email
     * @throws Exception
     */
    public function subscribe($email)
    {
        $I = $this->tester;

        if (is_null($email)) {
            $email = ($I->generateString(5).'@bambinifashion.com');
        }

        $I->waitAndFill(['name' => 'email'], 'email', $email);
        $I->waitAndClick('//button[contains(.,"Subscribe")]', 'Subscribe');
        $I->dismissAlert('Thank you!');
    }

    /**
     * Check visibility of contacts links.
     *
     * @throws Exception
     */
    public function checkContacts()
    {
        $I = $this->tester;

        $I->waitForVisible('//a[@href="mailto:hello@bambinifashion.com"]', 'contact email');
        $I->waitForVisible('//a[@href="tel:+431512896218"]', 'contact phone');
        $I->waitForVisible('//a[@href="https://wa.me/431512896230"]', 'contact whatsApp');
    }

    /**
     * Unsubscribe any subscription.
     *
     * @param string $email
     * @param string $subscriptionType wishlist, abandoned_cart
     * @throws Exception
     */
    public function unsubscribe($email, $subscriptionType)
    {
        $I = $this->tester;

        $link = $I->getUnsubscribeLink($email, $subscriptionType);
        $I->amOnUrl($link);
        $I->dismissAlert('You have been unsubscribed from our abandoned-cart');
    }

    /**
     * Check 'change shipping' button in pop up.
     *
     * @throws Exception
     */
    public function changeShippingByAlert()
    {
        $I = $this->tester;

        $I->waitForVisible('//div[@id="__overlay"][contains(.,"Your shipping destination is currently set")]', 'message "Your shipping destination is currently set"');
        $I->waitAndClick('//button[contains(.,"Change Shipping")]', 'go to shipping');
        $I->waitForVisible('//h2[.="Shipping method"]', 'Shipping method');
    }

    /**
     * Get restriction zone data.
     *
     * @param string $restriction tags\manufacturers\products
     * @return array $zoneData
     * @throws Exception
     */
    public function getRestrictionZoneData($restriction)
    {
        $I = $this->tester;

        $data = $I->getRestrictionZones();
        codecept_debug($data);
        $sortedIds = array();
        $sortedNames = array();
        foreach ($data as $zone) {
            if (!empty($zone['restrictions'][$restriction])) {
                $countries = $zone['countries'];
                $country = array_rand(array_flip($countries));
                $ids = $zone['restrictions'][$restriction];
                $id = $ids[array_rand($ids)]['id'];
                $names = $zone['restrictions'][$restriction];
                $name = $names[array_rand($names)]['name'];
                $sortedIds[$country] = $id;
                $sortedNames[$country] = $name;
            }
        }

        $country = array_rand($sortedIds);
        return $zoneData = array('country' => $country, 'id' => $sortedIds[$country], 'name' => $sortedNames[$country]);
    }
}