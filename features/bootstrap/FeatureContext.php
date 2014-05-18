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
    private $_output = array();
    private $_lastOutput;
    private $_vip = null;
    private $_spy = null;

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        require_once dirname(__FILE__)."/Vip.php";
        $this->_vip = new Vip();
        $this->_spy = new \christopheraue\phpspy\Spy("Vip", "learnSecret");
    }

    /**
     * @When /^I get the spied call with index ([+-]?\d+)$/
     */
    public function iGetTheSpiedCallWithIndex($callIdx)
    {
        $this->_lastOutput = $this->_spy->getCall($callIdx);
    }

    /**
     * @Given /^I get the call\'s argument with index ([+-]?\d+)$/
     */
    public function iGetTheCallSArgumentWithIndex($argIdx)
    {
        $this->_lastOutput = $this->_lastOutput->getArg($argIdx);
    }

    /**
     * @When /^I call the spy\'s method ([^ ]*)$/
     */
    public function iCallTheSpySMethod($methodName)
    {
        $this->_lastOutput = $this->_spy->$methodName();
    }


    /**
     * @When /^I call the spy\'s method ([^\s]*) with: (.*)$/
     */
    public function iCallTheSpySMethodWith($methodName, $argList)
    {
        $args = explode(",", preg_replace('/\s*,\s*/', ',', $argList));
        $this->_lastOutput = call_user_func_array(array($this->_spy, $methodName), $args);
    }

    /**
     * @Given /^Vip learns (\d+) secrets?$/
     */
    public function vipLearnsSecrets($counter)
    {
        $output = array();
        for ($idx=0; $idx<$counter; $idx++) {
            $output[] = $this->_vip->learnSecret("secret $idx", "source $idx");
        }

        $this->_lastOutput = implode("\n", $output);
    }

    /**
     * @Given /^echo the result$/
     */
    public function echoTheResult()
    {
        $this->_output[] = $this->_lastOutput;
    }

    /**
     * @Then /^I should get:$/
     */
    public function iShouldGet(PyStringNode $string)
    {
        $output = implode("\n", $this->_output);

        if ((string) $string !== $output) {
            throw new Exception(
                "Actual output is:\n" . $output
            );
        }
    }
}
