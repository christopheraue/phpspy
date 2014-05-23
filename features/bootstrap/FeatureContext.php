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
    public $lastResult;
    public $objects = array( "null" => null );

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        $this->useContext('spyMethod', new SpyMethodFeatureContext($parameters));
        $this->useContext('spyFunction', new SpyFunctionFeatureContext($parameters));
    }

    /**
     * @When /^"([^"]+)" ([^\s]+)s (\d+) ([^\s]+[^s])s?(?:: (.+))?$/
     */
    public function doesAThingMultipleTimes($subject, $verb, $counter, $object, $args)
    {
        $results = array();
        for ($idx=0; $idx<$counter; $idx++) {
            $callArgs = str_replace(",", $idx.",", $args);
            $results[] = $this->doesAThing($subject, $verb, $object, $callArgs);
        }
        $this->lastResult = implode("\n", $results);
    }

     /**
     * @When /^"([^"]+)" ([^\s]+)s the ([^\s]+)(?:: (.+))?$/
     */
    public function doesAThing($subject, $verb, $object, $args)
    {
        $args = explode(",", preg_replace('/\s*,\s*/', ',', $args));
        $methodName = $verb.ucfirst($object);
        $this->lastResult = call_user_func_array(array($this->objects[$subject], $methodName), $args);
    }

    /**
     * @When /^"([^"]*)" (?:is|was|has been) ([^\s]+[^ed])(?:ed)?$/
     */
    public function isDoneWith($object, $verb)
    {
        $this->lastResult = $this->objects[$object]->$verb();
    }

    /**
     * @Then /^It should have the result: (.+)$/
     */
    public function itShouldHaveTheResult($result)
    {
        return $this->lastResult = $result;
    }

     /**
     * @Then /^"([^"]*)" should have tracked (\d+) calls$/
     */
    public function shouldHaveTrackedCalls($spy, $count)
    {
        return $this->objects[$spy]->getCallCount() == $count;
    }

    /**
     * @Then /^The ([+-]?\d+)th requested call tracked by "([^"]*)" should be its ([+-]?\d+)th tracked call$/
     */
    public function theThRequestedCallTrackedByShouldBeItsThTrackedCall($requestIdx, $spy, $callIdx)
    {
        return $this->objects[$spy]->getCall($requestIdx)->getArg(0) == $callIdx;
    }

    /**
     * @Then /^The call tracked by "([^"]*)" received the argument "([^"]*)" at position ([+-]?\d+)$/
     */
    public function theCallTrackedByReceivedTheArgumentSecretAtPosition($spy, $arg, $argPos)
    {
        return $this->objects[$spy]->getCall(0)->getArg($argPos) == $arg;
    }

    /**
     * @Then /^The call tracked by "([^"]*)" returned the result "([^"]*)"$/
     */
    public function theCallTrackedByReturnedTheResult($spy, $result)
    {
        return $this->objects[$spy]->getCall(0)->getResult() == $result;
    }

    /**
     * @Then /^The call tracked by "([^"]*)" was in the context of "([^"]*)"$/
     */
    public function theCallTrackedByWasInTheContextOf($spy, $object)
    {
        return $this->objects[$spy]->getCall(0)->getContext() == $this->objects[$object];
    }

}
