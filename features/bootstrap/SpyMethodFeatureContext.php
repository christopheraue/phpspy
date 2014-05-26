<?php

use Behat\Behat\Context\BehatContext;

/**
 * Spy method features context.
 */
class SpyMethodFeatureContext extends BehatContext
{
    public function __construct(array $parameters)
    {

    }

    /**
     * @Given /^There is a class "([^"]*)"$/
     */
    public function thereIsAClass($classname)
    {
        require_once dirname(__FILE__)."/$classname.php";
        $this->getMainContext()->objects[$classname] = $classname;
    }

    /**
     * @Given /^There is an object "([^"]*)" derived from class "([^"]*)"/
     */
    public function thereIsAnObjectDerivedFromClass($objectname, $classname)
    {
        $this->thereIsAClass($classname);
        $this->getMainContext()->objects[$objectname] = new $classname();
    }

    /**
     * @Given /^There is a spy "([^"]*)" spying on method "([^"]*)" of "([^"]*)"$/
     */
    public function thereIsASpySpyingOnMethodOf($spy, $method, $classname)
    {
        $this->getMainContext()->objects[$spy] = new \christopheraue\phpspy\Spy($classname, $method);
    }

    /**
     * @When /^"([^"]*)" calls method "([^"]*)"(?: with: (.+))?$/
     */
    public function callsMethod($object, $method, $args)
    {
        $args = explode(",", preg_replace('/\s*,\s*/', ',', $args));
        $this->getMainContext()->lastResult = call_user_func_array(
            array($this->getMainContext()->objects[$object], $method),
            $args
        );
    }

    /**
     * @When /^"([^"]*)" calls method "([^"]*)" (\d+) times(?: with: (.+))?$/
     */
    public function callsMethodMultipleTimes($object, $method, $counter, $args)
    {
        $results = array();
        for ($idx=0; $idx<$counter; $idx++) {
            $callArgs = str_replace(",", $idx.",", $args);
            $results[] = $this->callsMethod($object, $method, $callArgs);
        }
        $this->getMainContext()->lastResult = implode("\n", $results);
    }
}