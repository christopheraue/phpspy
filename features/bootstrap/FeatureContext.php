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
        if ((string) $this->lastResult != $result) {
            throw new Exception(
                "Actual result is:\n".(string) $this->lastResult
            );
        }
    }

     /**
     * @Then /^"([^"]*)" should have tracked (\d+) calls$/
     */
    public function shouldHaveTrackedCalls($spy, $count)
    {
        $actualCount = $this->objects[$spy]->getCallCount();
        if ($actualCount != $count) {
            throw new Exception(
                "Actual count is:\n".$actualCount
            );
        }
    }

    /**
     * @Then /^The ([+-]?\d+)th requested call tracked by "([^"]*)" should be its ([+-]?\d+)th tracked call$/
     */
    public function theThRequestedCallTrackedByShouldBeItsThTrackedCall($requestIdx, $spy, $callIdx)
    {
        $requestCall = $this->objects[$spy]->getCall($requestIdx);
        $actualCall = $this->objects[$spy]->getCall($callIdx);
        if ($requestCall !== $actualCall) {
            throw new Exception(
                "Not equal"
            );
        }
    }

    /**
     * @Then /^The call tracked by "([^"]*)" received (\d+) arguments$/
     */
    public function theCallTrackedByReceivedArguments($spy, $argN)
    {
        $actualCount = $this->objects[$spy]->getCall(0)->getArgCount();
        if ($actualCount != $argN) {
            throw new Exception(
                "Actual count is:\n".$actualCount
            );
        }
    }

    /**
     * @Then /^The call tracked by "([^"]*)" received the argument "([^"]*)" at position ([+-]?\d+)$/
     */
    public function theCallTrackedByReceivedTheArgumentSecretAtPosition($spy, $arg, $argPos)
    {
        $actualArg = $this->objects[$spy]->getCall(0)->getArg($argPos);
        if ($actualArg != $arg) {
            throw new Exception(
                "Actual argument is:\n".$actualArg
            );
        }
    }

    /**
     * @Then /^The call tracked by "([^"]*)" returned the result "([^"]*)"$/
     */
    public function theCallTrackedByReturnedTheResult($spy, $result)
    {
        $actualResult = $this->objects[$spy]->getCall(0)->getResult();
        if ($actualResult != $result) {
            throw new Exception(
                "Actual result is:\n".$actualResult
            );
        }
    }

    /**
     * @Then /^The call tracked by "([^"]*)" was in the context of "([^"]*)"$/
     */
    public function theCallTrackedByWasInTheContextOf($spy, $object)
    {
        $actualContext = $this->objects[$spy]->getCall(0)->getContext();
        if ($actualContext !== $this->objects[$object]) {
            throw new Exception(
                "Actual context is:\n".($actualContext ? get_class($actualContext) : 'null')
            );
        }
    }

}
