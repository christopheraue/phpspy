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
            /** @var \christopheraue\phpspy\Spy $spy */
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

            $result = call_user_func_array(array($this, "'.$this->_methodname.'_original"), $args);

            $spyName = "'.$spyName.'";
            $spy = '.$spyClassname.'::$_spies[$spyName];
            $spy->recordCall($args, $result);

            return $result;'
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
     * @param array $args   Arguments the method was called with
     * @param mixed $result Return value of the call
     *
     * @return void
     */
    public function recordCall(array $args, $result)
    {
        $call = new Spy\Call($args, $result);
        array_push($this->_calls, $call);
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
     * @param int $idx Index indicating the nth call,
     * negative indices get call from the back of the list
     *
     * @return array
     * @throws \Exception
     */
    public function getCall($idx)
    {
        if (!is_numeric($idx)) {
            throw new \Exception('$idx must be an integer.');
        }

        if ($idx < 0) {
            return $this->_calls[count($this->_calls)+$idx];
        }

        return $this->_calls[$idx];
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
