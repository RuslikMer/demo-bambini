<?php
namespace Pages;

class Listing
{
    // include url of current page
    public static $URL = '';

    //constants
    const PRODUCT_CARD = '//div[@class="category-list-products"]/div[contains(@class,"product-card")]';
    const PROMOTION = '//div[contains(@class, "product-card-label--promotion")]/parent::*/parent::*';
    const PRODUCT_IMAGE = '//div[@class="product-card-thumbnail"]';
    const PRODUCT_TITLE = '//div[@class="product-card-title"]';
    const PRODUCT_BRAND = '//div[@class="product-card-brand"]';
    const PRODUCT_PRICE = '//span[contains(@class,"product-price-regular")]';
    const CATEGORIES_FILTER = '//ul[contains(@class,"category-product-type-filter-category")]//li';
    const ACC_CATEGORY_FILTER = '//div[contains(@class,"category-accumulative-filter-section--desktop ")]';
    const FILTER_SECTION = '//div[@class="category-filter-section"]';


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
     * Go to product.
     *
     * @param int $itemNum Sequential number of the item in the listing section
     * @param string $itemXpath
     * @return array $itemData
     * @throws \Exception
     */
    public function goToProduct($itemXpath, $itemNum)
    {
        $I = $this->tester;

        if (is_null($itemNum)) {
            $itemNum = $this->getRandomItemNumber($itemXpath);
        }

        $I->lookForwardTo('go to product ' . $itemNum);
        $itemData = $this->getItemData($itemNum, '('.$itemXpath.')');
        $I->waitAndClick('('.$itemXpath .')[' . $itemNum . ']', 'go to product ' . $itemNum, true);
        $I->waitForVisible('.product-single-header', 'product name');
        $actual = $I->grabTextFrom('//h1');
        $I->assertTrue($I->compareStrings($actual, $itemData['title']), 'wrong item card');

        return $itemData;
    }

    /**
     * Check product badge.
     *
     * @param string $itemXpath
     * @throws \Exception
     */
    public function checkProductBadge($itemXpath)
    {
        $I = $this->tester;

        $itemNum = $this->getRandomItemNumber($itemXpath);
        $I->lookForwardTo('go to product ' . $itemNum);
        $I->waitForVisible('('.$itemXpath .')[' . $itemNum . ']', 'product card');
        $I->waitForElementClickable('('.$itemXpath .')[' . $itemNum . ']', 30);
    }

    /**
     * Choosing a random filter from a block of categories.
     *
     * @throws \Exception
     */
    public function selectCategoryFilter()
    {
        $I = $this->tester;

        $I->waitForVisible(self::CATEGORIES_FILTER, 'categories filter');
        $categoriesCount = $I->getNumberOfElements(self::CATEGORIES_FILTER);
        $rndCategory = mt_rand(1, $categoriesCount);
        $I->waitAndClick(self::CATEGORIES_FILTER .'[' . $rndCategory . ']//a', 'select category');
        $this->waitOverlayLoader();
        $I->waitForVisible(self::CATEGORIES_FILTER .'[' . $rndCategory . ']//a[contains(@class,"is-active")]', 'selected category');

        $subCategoriesPath = self::CATEGORIES_FILTER .'[' . $rndCategory . ']'.self::CATEGORIES_FILTER;
        $subCategoriesCount = $I->getNumberOfElements($subCategoriesPath);
        if (!empty($subCategoriesCount)) {
            $I->waitForVisible($subCategoriesPath, 'subcategories');
            $rndSubCategory = mt_rand(1, $subCategoriesCount);
            $I->waitAndClick($subCategoriesPath . '[' . $rndSubCategory . ']//a', 'select subcategory');
            $this->waitOverlayLoader();
            $I->waitForVisible($subCategoriesPath . '[' . $rndSubCategory . ']//a[contains(@class,"is-active")]', 'selected subcategory');
        }
    }

    /**
     * Choosing a random accumulative filter from a block of categories.
     *
     * @throws \Exception
     */
    public function selectAccumulativeCategoryFilter()
    {
        $I = $this->tester;

        $I->waitForVisible('div.category-accumulative-filter', 'accumulative filter');
        $categoriesName = $I->grabMultiple('span.category-accumulative-filter-section--desktop-toggle-text-inner');
        codecept_debug($categoriesName);
        $categoriesCount = $I->getNumberOfElements(self::ACC_CATEGORY_FILTER);
        $rndCategory = mt_rand(1, $categoriesCount);
        $I->waitAndClick(self::ACC_CATEGORY_FILTER.'[' . $rndCategory . ']', 'select category');
        $I->waitForVisible(self::ACC_CATEGORY_FILTER.'[' . $rndCategory . ']//button[@aria-expanded="true"]', 'selected filter');

        $subCategoriesPath = '//li[contains(@class,"category-accumulative-filter-section--desktop-list-item")]';
        $subCategoryPath = self::ACC_CATEGORY_FILTER.'[' . $rndCategory . ']'.$subCategoriesPath;

        $I->waitForVisible($subCategoryPath, 'subcategories');
        $subCategoriesCount = $I->getNumberOfElements($subCategoryPath);
        $rndSubCategory = mt_rand(1, $subCategoriesCount);
        $I->waitAndClick($subCategoryPath.'[' . $rndSubCategory . ']', 'select subcategory');
        $I->waitForVisible($subCategoryPath.'[' . $rndSubCategory . ']' . '//div[@aria-checked="true"]', 'selected subcategory');

        $I->waitAndClick(self::ACC_CATEGORY_FILTER.'[' . $rndCategory . ']//button[contains(@class,"submit")][not(@disabled="disabled")]',
            'submit accumulative filter');
        $this->waitOverlayLoader();
        $I->waitForVisible(self::ACC_CATEGORY_FILTER.'[' . $rndCategory . ']//span[contains(@class, "counter")]', 'filter counter');
    }

    /**
     * Deselecting a filter from a full filter block.
     *
     * @param string $rndFilter filter name
     * @throws \Exception
     */
    public function unselectAccumulativeCategoryFilter($rndFilter)
    {
        $I = $this->tester;

        $I->lookForwardTo('unselect filter from full section');
        $I->waitAndClick(self::ACC_CATEGORY_FILTER.'[' . $rndFilter . ']//span[contains(@class, "counter")]', 'filter');
    }

    /**
     * Getting a random item number.
     *
     * @param string $itemXpath
     * @return int $itemNum random item number
     * @throws \Exception
     */
    public function getRandomItemNumber($itemXpath)
    {
        $I = $this->tester;

        $I->waitForVisible($itemXpath, 'items');
        $items = $I->getNumberOfElements($itemXpath, 'products in grid');
        $I->assertNotEquals($items, 0, 'No available products');
        $itemNum = mt_rand(1, $items);
        if ($itemNum > 5) {
            $itemNum = 5;
        }

        return $itemNum;
    }

    /**
     * Get product data.
     *
     * @param string $itemXpath
     * @param int $itemNum item number
     * @return array $itemData
     * @throws \Exception
     */
    public function getItemData($itemNum, $itemXpath)
    {
        $I = $this->tester;

        if (is_null($itemNum)) {
            $itemNum = $this->getRandomItemNumber($itemXpath);
        }

        $I->waitForVisible($itemXpath, 'items');
        $I->scrollTo($itemXpath.'['.$itemNum.']', 0, -200);
        $itemTitle = $I->grabTextFrom($itemXpath .'[' . $itemNum . ']' . self::PRODUCT_TITLE);
        $itemBrand = $I->grabTextFrom($itemXpath .'[' . $itemNum . ']' . self::PRODUCT_BRAND);
        $itemPrice = $I->grabTextFrom($itemXpath .'[' . $itemNum . ']' . self::PRODUCT_PRICE);

        $itemData = array('brand' => $itemBrand, 'title' => $itemTitle, 'price' => $itemPrice);

        return $itemData;
    }

    /**
     * Waiting for the product list to be updated.
     *
     * @throws \Exception
     */
    public function waitOverlayLoader()
    {
        $I = $this->tester;

        $I->waitForVisible('div.nuxt-progress', 'overlay');
        $I->waitForNotVisible('div.nuxt-progress', 'overlay');
    }

    /**
     * Check pagination.
     *
     * @throws \Exception
     */
    public function checkPagination()
    {
        $I = $this->tester;

        $currentXpath = '//li[contains(@class, "pagination-item--current")]';

        $pagination = $I->getNumberOfElements('.pagination-items');
        if (!empty($pagination)) {
            $I->lookForwardTo('check that the first page is selected');
            $I->waitForVisible($currentXpath.'[.=1]', '1 page in pagination');
            $arrayFirstPage = $I->grabMultiple(self::PRODUCT_CARD);
            $I->lookForwardTo('check that the second page is selected');
            $I->waitAndClick('//li[contains(@class, "pagination-item--desktop")][2]', "second page");
            $I->waitOverlayLoader();
            $I->waitForVisible($currentXpath.'[.=2]', '2 page in pagination');
            $arraySecondPage = $I->grabMultiple(self::PRODUCT_CARD);
            $I->assertNotEquals($arrayFirstPage, $arraySecondPage, 'The product list has not changed');
        }

        $dotsCount = $I->getNumberOfElements('.pagination-item--truncation');
        if (!empty($dotsCount)) {
            $I->waitForVisible('//li[contains(@class, "pagination-item--truncation")]/following-sibling::li[contains(@class, "pagination-item--desktop")]', 'pagination truncation');
        }

        $forward = $I->getNumberOfElements('.pagination-item--next');
        if (!empty($forward)) {
            $currentPage = $I->grabTextFrom($currentXpath);
            $I->waitAndClick('.pagination-item--next', "next page");
            $I->waitOverlayLoader();
            $forwardPage = $I->grabTextFrom($currentXpath);
            $I->assertGreaterThan($currentPage, $forwardPage, 'moving forward doesnt work');
            $I->waitAndClick('.pagination-item--prev', "previous page");
            $I->waitOverlayLoader();
            $currentPage = $I->grabTextFrom($currentXpath);
            $I->assertGreaterThan($currentPage, $forwardPage, 'moving backward doesnt work');
        }
    }

    /**
     * Sorting.
     *
     * @param string $filterName
     * @throws \Exception
     */
    public function sortBy($filterName)
    {
        $I = $this->tester;

        $sizeFilters = ['Age', 'Shoe Size'];
        $staticFilters = ['Age', 'Shoe Size', 'Gender', 'Color'];

        $sectionPath = '//div[@class="category-filter-section"]';
        $totalItemsCount = $I->getNumberFromLink('.category-list-title', 'total items count');

        $I->waitForVisible('.category-filter-sections', 'category filter');
        $open = $I->getNumberOfElements($sectionPath . '[contains(.,"' . $filterName . '")]//button[contains(@class, "has-expanded")]');
        if (empty($open)) {
            $I->waitAndClick($sectionPath . '[contains(.,"' . $filterName . '")]//button', 'expand filter');
            $I->waitForVisible($sectionPath . '[contains(.,"' . $filterName . '")]//button[contains(@class, "has-expanded")]', 'expanded filter');
        }

        $filters = array_diff($I->grabMultiple($sectionPath . '[contains(.,"' . $filterName . '")]//li'), array(''));
        if (!empty($filters)) {
            $filter = array_rand(array_flip($filters));
            $I->waitAndClick('(' . $sectionPath . '//a[contains(.,"' . $filter . '")])[1]', 'select filter');
            $I->waitOverlayLoader();
        }

        $sortTotalItemsCount = $I->getNumberFromLink('.category-list-title', 'sort total items count');
        $I->assertGreaterThan($sortTotalItemsCount, $totalItemsCount, 'wrong total items count after sorting');
        if ($filterName == 'Product Type') {
            $subCategories = $I->grabMultiple($sectionPath . '//li[contains(.,"' . $filter . '")]//li');
            if (!empty($subCategories)) {
                $subCategory = array_rand(array_flip($subCategories));
                $I->waitAndClick('(' . $sectionPath . '//li[contains(.,"' . $filter . '")]//li/a[contains(.,"' . $subCategory . '")])[1]', 'select sub category');
                $I->waitOverlayLoader();
            }
        } elseif (in_array($filterName, $staticFilters)) {
            $sortFilters =  array_diff($I->grabMultiple($sectionPath . '[contains(.,"' . $filterName . '")]//li'), array(''));
            $I->assertEquals($filters, $sortFilters, 'number of filters in section changed');
        }

        $productsCount = $I->getNumberOfElements(self::PRODUCT_CARD);
        if (in_array($filterName, $sizeFilters)) {
            for ($i = 1; $i <= $productsCount; $i++) {
                $I->moveMouseOver(self::PRODUCT_CARD . '[' . $i . ']');
                $I->wait(SHORT_WAIT_TIME);
                $I->waitForVisible(self::PRODUCT_CARD . '[' . $i . ']//li[contains(.,"' . $filter . '")]', 'item size '. $filter);
            }
        }

        if ($filterName == 'Designers') {
            $sortProductsCount = $I->getNumberOfElements(self::PRODUCT_CARD.'[contains(.,"'.$filter.'")]');
            $I->assertEquals($productsCount, $sortProductsCount, 'sorting not working');
        }
    }

    /**
     * Expand filter section.
     *
     * @param string $filterName
     * @throws \Exception
     */
    public function expandFilter($filterName)
    {
        $I = $this->tester;

        $I->waitAndClick(self::FILTER_SECTION . '[contains(.,"' . $filterName . '")]//button', 'expand filter');
    }

    /**
     * Use multi filter.
     *
     * @throws \Exception
     */
    public function multiFilter()
    {
        $I = $this->tester;

        $sectionNum = 1;

        do {
            $totalItemsCount = $I->getNumberFromLink('.category-list-title', 'total items count');
            $open = $I->getNumberOfElements(self::FILTER_SECTION . '[' . $sectionNum . ']//button[contains(@class, "has-expanded")]');
            if (empty($open)) {
                $I->waitAndClick(self::FILTER_SECTION . '[' . $sectionNum . ']//button', 'expand filter');
                $I->waitForVisible(self::FILTER_SECTION . '[' . $sectionNum . ']//button[contains(@class, "has-expanded")]', 'expanded filter');
            }

            $filters = $I->grabMultiple(self::FILTER_SECTION . '[' . $sectionNum . ']//li');
            $filter = array_rand(array_flip($filters));
            $I->waitAndClick('(' . self::FILTER_SECTION . '//a[contains(.,"' . $filter . '")])[1]', 'select filter');
            $I->waitOverlayLoader();
            $I->wait(SHORT_WAIT_TIME);
            $sortTotalItemsCount = $I->getNumberFromLink('.category-list-title', 'sort total items count');
            $I->assertGreaterThanOrEqual($sortTotalItemsCount, $totalItemsCount, 'wrong total items count after sorting');
            $sectionsCount = $I->getNumberOfElements(self::FILTER_SECTION);
            $sectionNum++;

        } while ($sectionNum <= $sectionsCount);
    }

    /**
     * Clear filter section.
     *
     * @param string $filterName
     * @throws \Exception
     */
    public function clearFilterBy($filterName)
    {
        $I = $this->tester;

        $I->waitForVisible('.category-filter-sections', 'category filter');
        $I->waitAndClick(self::FILTER_SECTION . '[contains(.,"' . $filterName . '")]//a[contains(.,"Clear")]', 'clear filter');
        $I->waitForNotVisible(self::FILTER_SECTION . '[contains(.,"' . $filterName . '")]//a[contains(.,"Clear")]', 'clear button for '. $filterName);
    }

    /**
     * Check season sale page.
     *
     * @throws \Exception
     */
    public function checkSeasonSale()
    {
        $I = $this->tester;

        $I->waitForVisible(['class' => 'footer-top'], 'footer');
        $I->scrollTo(['class' => 'footer-top']);
        $productsCount = $I->getNumberOfElements(self::PRODUCT_CARD);
        $sortProductsCount = $I->getNumberOfElements(self::PRODUCT_CARD.self::PROMOTION);
        $I->assertEquals($productsCount, $sortProductsCount, 'not all items are on sale');
    }

    /**
     * Check swiping images by arrow.
     *
     * @throws \Exception
     */
    public function checkSwipingImagesByArrow()
    {
        $I = $this->tester;

        $itemNum = $this->getRandomItemNumber(self::PRODUCT_CARD);
        $itemXpath = '('.self::PRODUCT_CARD.')[' . $itemNum . ']';
        $imagesCount = $I->getNumberOfElements($itemXpath.'//img');
        $i = 1;
        for (; $i < $imagesCount; ) {
            $I->moveMouseOver($itemXpath.'//button[@title="Next image"]');
            $I->waitAndClick($itemXpath.'//button[@title="Next image"]', "swipe forward", true);
            $I->unFocus();
            $I->scrollTo($itemXpath.'//button[@title="Next image"]', null, -200);
            $I->wait(0.5);
            $i++;
        }

        for (; $i > 1; $i--) {
            $I->moveMouseOver($itemXpath.'//button[@title="Previous image"]');
            $I->waitAndClick($itemXpath.'//button[@title="Previous image"]', "swipe back", true);
            $I->unFocus();
            $I->scrollTo($itemXpath.'//button[@title="Previous image"]', null, -200);
            $I->wait(0.5);
        }
    }
}