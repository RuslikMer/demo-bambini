<?php
namespace Pages;

class Cart
{
    // include url of current page
    public static $URL = '/shopping-bag/';

    //constants
    const CART_TOP = 'a.top-user-cart';
    const CART_ITEM = '//li[@class="cart-item"]';

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
     * Filling in the voucher field.
     *
     * @param string $voucher
     * @param string $message error message
     * @param bool $active active or inactive
     * @param int $fixedVoucher
     * @return array $orderData
     * @throws \Exception
     */
    public function useVoucher($voucher, $message, $active, $fixedVoucher)
    {
        $I = $this->tester;

        $orderData = [];
        $I->waitForVisible('div.cart-voucher', 'voucher block');
        $previousPercentageDiscount = $I->getNumberOfElements('dl.cart-total-item--discount');
        $percentageDiscount = 0;
        if (!empty($previousPercentageDiscount)) {
            $percentageDiscount = $I->getNumberFromLink('dl.cart-total-item--discount', 'percentage discount');
        }

        $previousFixedDiscount = $I->getNumberOfElements('dl.cart-total-item--credit');
        $fixedDiscount = 0;
        if (!empty($previousFixedDiscount)) {
            $fixedDiscount = $I->getNumberFromLink('dl.cart-total-item--credit', 'fixed discount');
        }

        $previousDiscount = array('percent' => $percentageDiscount, 'fixed' => $fixedDiscount);
        $I->waitAndFill(['name' => 'code'], 'voucher', $voucher);
        $I->waitAndClick('button.voucher-form-submit', 'apply voucher');
        $I->wait(MIDDLE_WAIT_TIME);
        if ($active) {
            $I->waitForVisible('div.cart-voucher-entries', 'used voucher');
            $I->waitForVisible('div.voucher-display-header', 'used voucher');
            $I->waitForVisible('dl.cart-total-item--discount, dl.cart-total-item--credit', 'cart discount');
            $orderData = $I->totalAmountCalculation(null, $fixedVoucher, $previousDiscount);
        } elseif (!empty($message)) {
            $I->dismissAlert($message);
        } else {
            $I->waitForVisible('//div[contains(@class,"overlay--popup ")]//h2[.="Ooops!"]', 'message Ooops');
            $I->waitAndClick('//button[contains(.,"Close")]', 'close error message');
            $I->waitForNotVisible('//div[contains(@class,"overlay--popup ")]//h2[.="Ooops!"]', 'message Ooops');
        }

        return $orderData;
    }

    /**
     * Calculation of the total amount.
     *
     * @param int $delivery
     * @param int $fixedVoucher
     * @param int $duties
     * @param array $previousDiscount
     * @return array $orderData
     * @throws \Exception
     */
    public function totalAmountCalculation($delivery, $fixedVoucher, $previousDiscount, $duties)
    {
        $I = $this->tester;

        $I->waitForVisible(['class' => 'cart-total'], 'cart total amount');
        $itemsTotal = $I->getNumberFromLink(['class' => 'cart-total-item'], 'items total');

        if (is_null($delivery)) {
            $delivery = 0;
        }

        $deliveryActive = $I->getNumberOfElements('dl.cart-total-item--delivery', 'delivery');
        if (!empty($deliveryActive)) {
            $delivery = $I->getNumberFromLink('dl.cart-total-item--delivery', 'delivery');
        }

        if (empty($previousDiscount)) {
            $previousDiscount = array('percent' => 0, 'fixed' => 0);
        }

        //$subTotal = $I->getNumberFromLink(['class' => 'cart-total-item--subtotal'], 'sub-total');
        $percentVoucherActive = $I->getNumberOfElements('//div[@class="voucher-display-header"][contains(.,"Extra") or contains(.,"EXTRA") or contains(.,"percent")]', 'percent voucher');
        $percentVoucherDiscount = 0;
        if (!empty($percentVoucherActive) && empty($fixedVoucher)) {
            $text = preg_replace("/[E2E]/", "", $I->grabTextFrom('//div[@class="voucher-display-info-description"][contains(.,"Extra") or contains(.,"EXTRA") or contains(.,"percent")]'));
            $percent = preg_replace("/[^0-9]/", "", $text);
            $percentVoucherDiscount = $I->getNumberFromLink('dl.cart-total-item--discount', 'percent cart discount');
            $I->assertTrue((round(($itemsTotal * $percent)/100, 0) - ($percentVoucherDiscount - $previousDiscount['percent'])) <= 1, 'incorrect discount percent');
        }

        if (empty($percentVoucherDiscount)) {
            $percentVoucherDiscount = $previousDiscount['percent'];
        }

        $fixedVoucherActive = $I->getNumberOfElements('//div[@class="voucher-display-header"][contains(.,"fixed")]', 'fixed voucher');
        $fixedVoucherAmount = 0;
        if (!empty($fixedVoucherActive)) {
            $fixedVoucher = preg_replace("/[^0-9]/", "", str_replace('E2E', '', $I->grabTextFrom('(//div[@class="voucher-display-info-title"][contains(.,"fixed")])[last()]')));
            $fixedVoucherAmount = $I->getNumberFromLink('dl.cart-total-item--credit', 'total fixed voucher discount');
            //$I->assertEquals($fixedVoucher, $fixedVoucherAmount - $previousDiscount['fixed'], 'incorrect fixed discount');
        }

        $I->wait(SHORT_WAIT_TIME);
        $totalSum = $I->getNumberFromLink('dl.cart-total-item--total', 'total amount');
        $I->assertEquals($totalSum, $itemsTotal + $delivery - $percentVoucherDiscount - $fixedVoucherAmount + $duties, 'wrong total amount');

        $orderData = array('totalSum' => $totalSum, 'itemsTotal' => $itemsTotal, 'delivery' => $delivery, 'fixedVoucher' => $fixedVoucher, 'previousDiscount' => array_sum($previousDiscount), 'duties' => $duties);

        return $orderData;
    }

    /**
     * Calculation of the sub-total amount.
     *
     * @throws \Exception
     */
    public function subTotalAmountCalculation()
    {
        $I = $this->tester;

        $I->waitForVisible(self::CART_ITEM, 'items');
        $productsCount = $I->getNumberOfElements(self::CART_ITEM);
        $totalProductsPrice = 0;
        for ($i = 1; $i <= $productsCount; $i++) {
            $price = $I->getNumberFromLink(self::CART_ITEM.'['.$i.']//span[contains(@class,"product-price-regular") and not(contains(@class,"is-reduced")) or contains(@class,"product-price-reduced")]', 'product price');
            $totalProductsPrice += $price;
        }

        //$subTotal = $I->getNumberFromLink(['class' => 'cart-total-item--subtotal'], 'sub-total');
        $itemsTotal = $I->getNumberFromLink(['class' => 'cart-total-item'], 'items total');
        $I->assertEquals($itemsTotal, $totalProductsPrice, 'wrong sub-total amount');
    }

    /**
     * Dismiss voucher.
     *
     * @throws \Exception
     */
    public function dismissVoucher()
    {
        $I = $this->tester;

        $I->waitAndClick('button.alert-dismiss', 'dismiss voucher');
        $I->waitForNotVisible('div.cart-voucher-entries', 'used voucher');
        $I->waitForNotVisible('dl.cart-total-item--discount', 'discount');
        $I->totalAmountCalculation();
    }

    /**
     * Clean cart.
     *
     * @throws \Exception
     */
    public function cleanCart()
    {
        $I = $this->tester;

        $I->waitForVisible(self::CART_TOP, 'cart in header');
        $itemsCount = $I->getNumberFromLink(self::CART_TOP, 'item count in cart');
        if (!empty($itemsCount)) {
            $I->goToCart();
            for ($i = 1; $i <= $itemsCount; $i++) {
                $I->waitAndClick('button.cart-item-remove', 'remove from bag');
            }

            $I->waitForVisible('button.cart-empty-continue', 'continue button');
            $I->amOnPage('/');
        }
    }

    /**
     * Delete item.
     *
     * @throws \Exception
     */
    public function deleteItem()
    {
        $I = $this->tester;

        $items = $I->grabMultiple(self::CART_ITEM.'//h3');
        $item = $items[mt_rand(0, count($items)-1)];
        $I->waitAndClick(self::CART_ITEM.'[contains(.,"'.$item.'")]//button[contains(.,"Remove")]', 'remove from bag');
        $I->waitForNotVisible(self::CART_ITEM.'[contains(.,"'.$item.'")]//button[contains(.,"Remove")][@disabled="disabled"]', 'disabled Remove button');
    }

    /**
     * Go to checkout.
     *
     * @throws \Exception
     */
    public function goToCheckout()
    {
        $I = $this->tester;

        $I->waitAndClick('//div[@class="cart-summary-inner"]//button[contains(.,"Proceed to purchase")]', "go to checkout");
        $I->waitOverlayLoader();
    }

    /**
     * Go to product.
     *
     * @throws \Exception
     */
    public function goToProduct()
    {
        $I = $this->tester;

        $I->waitForVisible(self::CART_ITEM, 'items');
        $itemsCount = $I->getNumberOfElements(self::CART_ITEM, 'items count');
        $rndNum = mt_rand(1, $itemsCount);
        $itemName = $I->grabTextFrom(self::CART_ITEM.'['.$rndNum.']//h3');
        $I->waitAndClick(self::CART_ITEM.'['.$rndNum.']//a', 'go to product');
        $I->waitForVisible('//h1[.="'.$itemName.'"]', 'item '.$itemName);
    }

    /**
     * Check delivery & return.
     *
     * @throws \Exception
     */
    public function checkDeliveryAndReturn()
    {
        $I = $this->tester;

        $I->waitAndClick('//button[contains(.,"Delivery & Return")]', 'open delivery and return');
        $this->checkDeliveryCalculator();
    }

    /**
     * Check delivery calculator.
     *
     * @throws \Exception
     */
    public function checkDeliveryCalculator()
    {
        $I = $this->tester;

        $I->waitForVisible('//select[@id="delivery-calculator-country-input-select"]', 'delivery calculator');
        $country = $I->grabTextFrom('//select[@id="delivery-calculator-country-input-select"]');
        $I->waitAndClick('//select[@id="delivery-calculator-country-input-select"]', 'open country list');
        $countries = $I->grabMultiple('//select[@id="delivery-calculator-country-input-select"]//option[not(@disabled)]');
        unset($countries[array_search($country, $countries)]);
        $rndCountry = $countries[mt_rand(0, count($countries)-1)];
        $I->waitAndClick('//select[@id="delivery-calculator-country-input-select"]//option[.="'.$rndCountry.'"]', 'select country');
    }

    /**
     * Continue shopping.
     *
     * @throws \Exception
     */
    public function continueShopping()
    {
        $I = $this->tester;

        $I->waitAndClick('//button[contains(.,"Continue shopping")]', 'continue shopping');
        $I->waitForVisible(Listing::PRODUCT_CARD, 'product card');
    }

    /**
     * Check countdown appear.
     *
     * @throws \Exception
     */
    public function checkCountdownAppear()
    {
        $I = $this->tester;

        $I->waitForVisible('//span[contains(.,"Your Bag will be reserved for")]', 'countdown');
    }

    /**
     * Check countdown disappear.
     *
     * @throws \Exception
     */
    public function checkCountdownDisappear()
    {
        $I = $this->tester;

        $I->waitForNotVisible('//span[contains(.,"Your Bag will be reserved for")]', 'countdown');
        $I->waitForVisible('div.countdown-finished', 'countdown finish');
    }

    /**
     * Change item quantity.
     *
     * @throws \Exception
     */
    public function changeItemQuantity()
    {
        $I = $this->tester;

        $itemsCount = $I->getNumberOfElements(self::CART_ITEM);
        $rndItem = mt_rand(1, $itemsCount);
        $quantityXpath = self::CART_ITEM.'['.$rndItem.']//select';
        $I->waitAndClick($quantityXpath, 'expand quantity list');
        $optionsCount = $I->getNumberOfElements($quantityXpath.'//option');
        $I->assertTrue($optionsCount <= 10, 'available quantity more than 10');
        $rndNum = mt_rand(1, $optionsCount);
        $I->waitAndClick($quantityXpath.'//option['.$rndNum.']', 'select quantity');
        $I->waitForNotVisible('//select[@id="cart-item-quantity-input-select"][@disabled="disabled"]', 'disabled quantity input');
    }
}