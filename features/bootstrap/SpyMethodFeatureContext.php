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
     * @Given /^There is an object "([^"]*)" derived from class "([^"]*)"/
     */
    public function thereIsAnObjectDerivedFromClass($objectname, $classname)
    {
        require_once dirname(__FILE__)."/".$classname.".php";
        $this->getMainContext()->objects[$objectname] = new $classname();
    }

    /**
     * @Given /^There is a spy "([^"]*)" spying on method "([^"]*)" of "([^"]*)"$/
     */
    public function thereIsASpySpyingOnMethodOf($spy, $method, $classname)
    {
        $this->getMainContext()->objects[$spy] = new \christopheraue\phpspy\Spy($classname, $method);
    }
}