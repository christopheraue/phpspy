<?php
/**
 * A spy for tracking calls to methods.
 *
 * @author   Christopher Aue <mail@christopheraue.net>
 * @license  MIT
 * @link     https://github.com/christopheraue/phpspy
 */

namespace christopheraue\phpspy;

class Spy
{
    public static $_spies = array();

    protected $_classname = null;
    protected $_methodname = null;
    protected $_calls = array();

    /**
     * Spy on a class' method
     *
     * @param string $classname  The class which method to spy on
     * @param string $methodname The name of the method to spy on
     */
    public function __construct($classname, $methodname)
    {

        //runkit seems not to know classes that were not already loaded
        //fix it by creating an instance here and throw it away
        $klass = new $classname();

        //Sometimes $classname and $methodname are transfered to this constructer in all lower case
        //although they were given in camel case. Strange behavior. Doesn't matter, since PHP
        //class and function names are case insensitive. Convert the names to all lower case.
        $this->_classname = strtolower($classname);
        $this->_methodname = strtolower($methodname);

        $spyName = $this->_classname.':'.$this->_methodname;

        if (array_key_exists($spyName, self::$_spies)) {
            /** @var christopheraue\phpspy\Spy $spy */
            $spy = self::$_spies[$spyName];
            $spy->kill();
        }

        self::$_spies[$spyName] = $this;

        $this->_replaceMethod();
    }

    /**
     * Replace the method to spy on
     *
     * @return void
     */
    protected function _replaceMethod()
    {
        $spyName = $this->_classname.':'.$this->_methodname;

        $spyClassname = __CLASS__;
        runkit_method_add(
            $this->_classname,
            $this->_methodname.'_spy',
            '',
            '$args = func_get_args();

            $spyName = "'.$spyName.'";
            $spy = '.$spyClassname.'::$_spies[$spyName];
            $spy->recordCall($args);

            return call_user_func_array(array($this, "'.$this->_methodname.'_original"), $args);'
        );

        if (!method_exists($this->_classname, $this->_methodname.'_original')) {
            runkit_method_copy(
                $this->_classname,
                $this->_methodname.'_original',
                $this->_classname,
                $this->_methodname
            );
        }

        //keep memory address of function to prevent seg fault
        runkit_method_redefine(
            $this->_classname,
            $this->_methodname,
            '',
            '$args = func_get_args();
            return call_user_func_array(array($this, "'.$this->_methodname.'_spy"), $args);'
        );
    }
    /**
     * Save a call to the method
     *
     * @param array $args Arguments the method was called with
     *
     * @return void
     */
    public function recordCall(array $args)
    {
        array_push($this->_calls, $args);
    }

    /**
     * Reset tracked calls of a class' method
     *
     * @return void
     */
    public function reset()
    {
        $this->_calls = array();
    }

    /**
     * Return tracked calls of a class' method
     *
     * @return array
     */
    public function getCalls()
    {
        return $this->_calls;
    }

    /**
     * Return number of tracked calls
     *
     * @return int
     */
    public function getCallCount()
    {
        return count($this->_calls);
    }

    /**
     * Return a specific call
     *
     * @param int $idx Index indicating the nth call
     *
     * @return array
     */
    public function getCall($idx)
    {
        return $this->_calls[$idx];
    }

    /**
     * Return the last tracked call
     *
     * @return array
     */
    public function getLastCall()
    {
        return $this->_calls[$this->getCallCount()-1];
    }

    /**
     * Return a specific argument of a specific call
     *
     * @param int $callIdx Index indicating the nth call
     * @param int $argIdx  Index indicating the mth argument of the nth call
     *
     * @return array
     */
    public function getCallArgument($callIdx, $argIdx)
    {
        return $this->_calls[$callIdx][$argIdx];
    }

    /**
     * Return a specific argument of the last tracked call
     *
     * @param int $argIdx Index indicating the mth argument of the nth call
     *
     * @return array
     */
    public function getLastCallArgument($argIdx)
    {
        $lastCall = $this->getLastCall();
        return $lastCall[$argIdx];
    }

    /**
     * Kill the spy
     *
     * @return void
     */
    public function kill()
    {
        runkit_method_remove($this->_classname, $this->_methodname.'_spy');
        //keep memory address of function to prevent seg fault
        runkit_method_redefine(
            $this->_classname,
            $this->_methodname,
            '',
            '$args = func_get_args();
            return call_user_func_array(array($this, "'.$this->_methodname.'_original"), $args);'
        );
        unset(self::$_spies[$this->_classname.':'.$this->_methodname]);
    }
}
