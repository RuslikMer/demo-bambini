<?php

namespace Helper;


class Acceptance extends \Codeception\Module
{
    /**
     * Do before test.
     *
     * @param $test
     * @throws \Exception
     */
    public function _before(\Codeception\TestInterface $test)
    {
        $name = $test->getMetadata()->getName();
        $this->getModule('WebDriver')->_capabilities(function($currentCapabilities) use ($name) {
            $currentCapabilities['name'] = $name;
            codecept_debug($currentCapabilities['name']);
            return $currentCapabilities;
        });
    }

    /**
     * Do after test failed.
     *
     * @param $test
     * @param $fail
     * @throws \Exception
     */
    public function _failed(\Codeception\TestInterface $test, $fail)
    {
        $this->getModule('WebDriver')->executeJS('browserstack_executor: {"action": "setSessionStatus", "arguments": {"status":"failed"}}');
    }
}