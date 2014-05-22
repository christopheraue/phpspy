<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{
    private $_objects = array();

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {

    }

    /**
     * @Given /^There is an object "([^"]*)" derived from class "([^"]*)"/
     */
    public function thereIsAnObjectDerivedFromClass($objectname, $classname)
    {
        require_once dirname(__FILE__)."/".$classname.".php";
        $this->_objects[$objectname] = new $classname();
    }

    /**
     * @Given /^There is a spy "([^"]*)" spying on method "([^"]*)" of "([^"]*)"$/
     */
    public function thereIsASpySpyingOnMethodOf($spy, $method, $classname)
    {
        $this->_objects[$spy] = new \christopheraue\phpspy\Spy($classname, $method);
    }

    /**
     * @When /^"([^"]+)" ([^\s]+)s (\d+) ([^\s]+[^s])s?(?:: (.+))?$/
     */
    public function doesAThingMultipleTimes($subject, $verb, $counter, $object, $args)
    {
        for ($idx=0; $idx<$counter; $idx++) {
            $callArgs = str_replace(",", $idx.",", $args);
            $this->doesAThing($subject, $verb, $object, $callArgs);
        }
    }

     /**
     * @When /^"([^"]+)" ([^\s]+)s the ([^\s]+)(?:: (.+))?$/
     */
    public function doesAThing($subject, $verb, $object, $args)
    {
        $args = explode(",", preg_replace('/\s*,\s*/', ',', $args));
        $methodName = $verb.ucfirst($object);
        call_user_func_array(array($this->_objects[$subject], $methodName), $args);
    }

     /**
     * @Then /^"([^"]*)" should have tracked (\d+) calls$/
     */
    public function shouldHaveTrackedCalls($spy, $count)
    {
        return $this->_objects[$spy]->getCallCount() == $count;
    }

    /**
     * @Then /^The (\d+)th call tracked by "([^"]*)" should be the call with the ([+-]?\d+)th learned secret$/
     */
    public function theThCallTrackedByShouldBeTheCallWithTheThLearnedSecret($callIdx, $spy, $secretIdx)
    {
        return $this->_objects[$spy]->getCall($callIdx)->getArg(0) == "secret".$secretIdx;
    }

    /**
     * @Then /^The call tracked by "([^"]*)" received the argument "([^"]*)" at position ([+-]?\d+)$/
     */
    public function theCallTrackedByReceivedTheArgumentSecretAtPosition($spy, $arg, $argPos)
    {
        return $this->_objects[$spy]->getCall(0)->getArg($argPos) == $arg;
    }

    /**
     * @Then /^The call tracked by "([^"]*)" returned the result "([^"]*)"$/
     */
    public function theCallTrackedByReturnedTheResult($spy, $result)
    {
        return $this->_objects[$spy]->getCall(0)->getResult() == $result;
    }

    /**
     * @Then /^The call tracked by "([^"]*)" was in the context of "([^"]*)"$/
     */
    public function theCallTrackedByWasInTheContextOf($spy, $object)
    {
        return $this->_objects[$spy]->getCall(0)->getContext() == $this->_objects[$object];
    }

}
