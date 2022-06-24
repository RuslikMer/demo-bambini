<?php

namespace Helper;

use Yandex\Allure\Adapter\Support\AttachmentSupport;
use Facebook\WebDriver\WebDriverBy;

class Shot extends \Codeception\Module
{
    use AttachmentSupport;

    /**
     * To do before test.
     *
     * @param $test
     * @throws \Exception
     */
    public function _before(\Codeception\TestInterface $test)
    {

    }

    /**
     * To do after test.
     *
     * @param $test
     * @throws \Exception
     */
    public function _after(\Codeception\TestInterface $test)
    {
        $sessionId = $this->getModule('\Helper\System')->getSessionID();
        $json = file_get_contents('tests/users.json');
        $jsonArray = json_decode($json, true);
        foreach ($jsonArray as &$userData) {
            if ($userData['isBusy'] == $sessionId) {
                $userData['isBusy'] = false;
                file_put_contents('tests/users.json', json_encode($jsonArray));

                break;
            }
        }
    }

    /**
     * To do if failed.
     *
     * @param $test
     * @param $fail
     * @throws \Exception
     */
    public function _failed(\Codeception\TestInterface $test, $fail)
    {
        // Attach screenshot to report.
        $testName = $test->getMetadata()->getName();
        $env = $test->getMetadata()->getCurrent('env');
        if (!is_null($env)) {
            $envName = str_replace('-', '.', $env) . '.';
            codecept_debug($envName);
            try {
                $this->makeFullScreen($testName . 'Cept' . $envName);
                $shot = codecept_output_dir() . 'debug' . DIRECTORY_SEPARATOR . $testName . 'Cept' . $envName . 'fail.png';
            } catch (\Exception $e) {
                $shot = codecept_output_dir() . $testName . 'Cept.' . $envName . 'fail.png';
            }
        } else {
            try {
                $this->makeFullScreen($testName . 'Cept.');
                $shot = codecept_output_dir() . 'debug' . DIRECTORY_SEPARATOR . $testName . 'Cept.fail.png';
            } catch (\Exception $e) {
                $shot = codecept_output_dir() . $testName . 'Cept.fail.png';
            }
        }

        codecept_debug($shot);
        if (!file_exists($shot)) {
            print("###### Screenshot file does not exist! ######");
        }

        $this->addAttachment($shot, 'Screenshot', 'image/png');
    }

    /**
     * Get a screenshot of the entire page.
     *
     * @param mixed $testName
     * @throws \Exception
     */
    public function makeFullScreen($testName)
    {
        $windowHeight = $this->getModule('WebDriver')->executeJS('return window.innerHeight');
        $scrollHeight = $this->getModule('WebDriver')->executeJS('return document.body.scrollHeight');
        $windowWidth = $this->getModule('WebDriver')->executeJS('return window.innerWidth');

        $fullImg = imagecreatetruecolor(intval($windowWidth), intval($scrollHeight));

        //get the size of the header on the page and recalculate the number of repetitions and the remainder
        $topSize = $this->getModule('WebDriver')->executeInSelenium(function (\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) use (&$size) {
            return $size = $webdriver->findElement(WebDriverBy::xpath('//div[@class="top"]'))->getSize();
        });

        $navSize = $this->getModule('WebDriver')->executeInSelenium(function (\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) use (&$size) {
            return $size = $webdriver->findElement(WebDriverBy::xpath('//nav[@class="nav nav--desktop"]'))->getSize();
        });

        $prolineSize = $this->getModule('WebDriver')->executeInSelenium(function (\Facebook\WebDriver\Remote\RemoteWebDriver $webdriver) use (&$size) {
            return $size = $webdriver->findElement(WebDriverBy::xpath('//div[@class="proline-highlight"]'))->getSize();
        });

        $topSize = array_values((array)$topSize);
        $navSize = array_values((array)$navSize);
        $prolineSize = array_values((array)$prolineSize);
        $headerHeight = $topSize[0] + $navSize[0] + $prolineSize[0];

        $repeat = ceil(number_format(($scrollHeight - $windowHeight) / ($windowHeight - $headerHeight), 2) + 1);
        $residue = ($scrollHeight - $windowHeight) % ($windowHeight - $headerHeight);

        $removeHeight = $headerHeight;
        codecept_debug($removeHeight);
        for ($k = 0; $k < $repeat; $k++) {
            $offsetY = ($windowHeight * $k);
            //If there is a fixed header when scrolling the page, recalculate the coordinates minus the size of the header
            if ($k != 0) {
                $offsetY = ($windowHeight * $k) - ($removeHeight * $k);
            }

            $this->getModule('WebDriver')->executeJS('scroll(0, "' . $offsetY . '")');
            $this->getModule('WebDriver')->makeScreenshot($testName . $k);
        }

        $fullScreen = codecept_log_dir() . 'debug' . DIRECTORY_SEPARATOR . $testName . 'fail.png';
        $screenHeight = 0;
        for ($k = 0; $k < $repeat; $k++) {
            $screen = codecept_log_dir() . 'debug' . DIRECTORY_SEPARATOR . $testName . $k . '.png';
            $screenSize = getimagesize($screen);
            //if there is a fixed cap, then it is cut off
            if ($k != 0) {
                $height = $screenSize[1] - $removeHeight;
                $img = imagecreatetruecolor(intval($screenSize[0]), intval($height));
                $img2 = imagecreatefrompng($screen);
                imagecopyresampled($img, $img2, 0, 0, 0, intval($removeHeight), intval($screenSize[0]), intval($screenSize[1]), intval($screenSize[0]), intval($screenSize[1]));
                imagepng($img, $screen);
            }

            //crop the last screenshot with the rest of the page
            if ($residue != 0 && $k == $repeat - 1) {
                $img = imagecreatetruecolor(intval($screenSize[0]), intval($residue));
                $img2 = imagecreatefrompng($screen);
                imagecopyresampled($img, $img2, 0, 0, 0, intval($screenSize[1] - $removeHeight - $residue), intval($screenSize[0]), intval($residue), intval($screenSize[0]), intval($residue));
                imagepng($img, $screen);
            }

            //combine all screens into one
            $img2 = imagecreatefrompng($screen);
            $screenSize = getimagesize($screen);
            imagecopymerge($fullImg, $img2, 0, intval($screenHeight), 0, 0, intval($screenSize[0]), intval($screenSize[1]), 100);
            imagepng($fullImg, $fullScreen);
            imagedestroy($img2);
            unlink($screen);
            $screenHeight += $screenSize[1];
        }
    }
}