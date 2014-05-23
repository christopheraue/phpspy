<?php

use Behat\Behat\Context\BehatContext;

/**
 * Spy function features context.
 */
class SpyFunctionFeatureContext extends BehatContext
{
    public function __construct(array $parameters)
    {

    }

    /**
     * @Given /^There is a function called "([^"]*)" defined$/
     */
    public function thereIsAFunctionCalledDefined($funcname)
    {
        require_once dirname(__FILE__)."/".$funcname.".php";
    }

    /**
     * @Given /^There is a spy "([^"]*)" spying on function "([^"]*)"$/
     */
    public function thereIsASpySpyingOnFunction($spy, $funcName)
    {
        $this->getMainContext()->objects[$spy] = new \christopheraue\phpspy\Spy($funcName);
    }

    /**
     * @When /^"([^"]*)" is called(?: with: (.+))?$/
     */
    public function isCalled($func, $args)
    {
        $args = explode(",", preg_replace('/\s*,\s*/', ',', $args));
        $this->getMainContext()->lastResult = call_user_func_array($func, $args);
    }

    /**
     * @When /^"([^"]*)" is called (\d+) times(?: with: (.+))?$/
     */
    public function isCalledMultipleTimes($func, $counter, $args)
    {
        $results = array();
        for ($idx=0; $idx<$counter; $idx++) {
            $callArgs = str_replace(",", $idx.",", $args);
            $results[] = $this->isCalled($func, $callArgs);
        }
        $this->getMainContext()->lastResult = implode("\n", $results);
    }
}