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
    private $_instances = array();

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        // Initialize your context here
    }

    /**
     * @Given /^I have the class "([^"]*)"$/
     */
    public function iHaveTheClass($className)
    {
        require_once dirname(__FILE__)."/$className.php";
    }

    /**
     * @Given /^I have an instance "([^"]*)" of class "([^"]*)"$/
     */
    public function iHaveAnInstanceOfClass($instName, $className)
    {
        $this->_instances[$instName] = new $className();
        $this->_lastOutput = $this->_instances[$instName];
    }

    /**
     * @Given /^I set the spy "([^"]*)" on method "([^"]*)" of class "([^"]*)"$/
     */
    public function iSetTheSpyOnMethodOfClass($spyName, $methodName, $instName)
    {
        $this->_instances[$spyName] = new \christopheraue\phpspy\Spy($instName, $methodName);
        $this->_lastOutput = $this->_instances[$spyName];
    }

    /**
     * @Given /^I call method "([^"]*)" of "([^"]*)" with "([^"]*)"$/
     */
    public function iCallMethodOfWith($methodName, $instName, $argList)
    {
        $args = explode(",", preg_replace('/\s*,\s*/', ',', $argList));
        $this->_lastOutput = call_user_func_array(array($this->_instances[$instName], $methodName), $args);
    }

    /**
     * @Given /^I call method "([^"]*)" of "([^"]*)" with "([^"]*)" (\d+) times$/
     */
    public function iCallMethodOfWithTimes($methodName, $instName, $argList, $counter)
    {
        $output = array();
        while($counter--) {
            $this->iCallMethodOfWith($methodName, $instName, $argList);
            $output[] = $this->_lastOutput;
        }

        $this->_lastOutput = implode("\n", $output);
    }

    /**
     * @Given /^I call method "([^"]*)" of "([^"]*)"$/
     */
    public function iCallMethodOf($methodName, $instName)
    {
        $this->iCallMethodOfWith($methodName, $instName, "");
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
