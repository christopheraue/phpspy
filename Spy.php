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
    protected static $_spies = array();

    protected $_name = null;
    protected $_context = null;
    protected $_functionName = null;
    /** @var callable _substitute */
    protected $_substitute = null;

    protected $_origFuncSuffix = '_original';
    protected $_spyFuncSuffix = '_spy';

    protected $_calls = array();

    /**
     * Create a spy to spy on a function or a method
     *
     * @param string $context    A classname or a functioname to spy on
     * @param string $methodName If $context is a classname, the name of the method to spy on
     */
    public function __construct($context, $methodName = null)
    {
        if (func_num_args() == 1) {
            $this->_initFunctionSpy($context);
        } else {
            $this->_initMethodSpy($context, $methodName);
        }
    }

    protected function _initFunctionSpy($functionName)
    {
        $this->_functionName = strtolower($functionName);
        $this->_name = $this->_functionName;
        $this->_storeInRegister();
        $this->_redirectCallToFunction();
    }

    protected function _initMethodSpy($classname, $methodname)
    {
        if (!class_exists($classname)) {
            //try to load it via autoloading, or fail
            new $classname();
        }

        //Sometimes $classname and $methodname are transferred to this constructor in all lower case
        //although they were given in camel case. Strange behavior. Doesn't matter, since PHP
        //class and function names are case insensitive. Convert the names to all lower case.
        $this->_context = strtolower($classname);
        $this->_functionName = strtolower($methodname);

        $this->_name = $this->_context.'::'.$this->_functionName;
        $this->_storeInRegister();
        $this->_redirectCallToMethod();
    }

    protected function _storeInRegister()
    {
        if (array_key_exists($this->_name, self::$_spies)) {
            /** @var \christopheraue\phpspy\Spy $spy */
            $spy = self::$_spies[$this->_name];
            $spy->kill();
        }

        self::$_spies[$this->_name] = $this;
    }

    protected function _redirectCallToMethod()
    {
        $newOrigFuncName = $this->_functionName.$this->_origFuncSuffix;
        $spyFuncName = $this->_functionName.$this->_spyFuncSuffix;
        $isStatic = (new \ReflectionMethod($this->_context, $this->_functionName))->isStatic();
        $isInherited = !method_exists($this->_context, $this->_functionName)
            && method_exists(get_parent_class($this->_context), $this->_functionName);

        runkit_method_add(
            $this->_context,
            $spyFuncName,
            '',
            $this->_getSpyFunctionBody(),
            RUNKIT_ACC_PRIVATE | ($isStatic ? RUNKIT_ACC_STATIC : 0)
        );

        if (!method_exists($this->_context, $newOrigFuncName)) {
            runkit_method_copy($this->_context, $newOrigFuncName, $this->_context, $this->_functionName);
        }

        //keep memory address of function to prevent seg fault
        $runkit_method_modifier = $isInherited ? 'runkit_method_add' : 'runkit_method_redefine';
        $runkit_method_modifier(
            $this->_context,
            $this->_functionName,
            '',
            $this->_getReplaceFunctionBody(),
            RUNKIT_ACC_PUBLIC | ($isStatic ? RUNKIT_ACC_STATIC : 0)
        );
    }

    protected function _redirectCallToFunction()
    {
        $newOrigFuncName = $this->_functionName.$this->_origFuncSuffix;
        $spyFuncName = $this->_functionName.$this->_spyFuncSuffix;

        runkit_function_add($spyFuncName, '', $this->_getSpyFunctionBody());

        if (!function_exists($newOrigFuncName)) {
            runkit_function_copy($this->_functionName, $newOrigFuncName);
        }

        //keep memory address of function to prevent seg fault
        runkit_function_redefine($this->_functionName, '', $this->_getReplaceFunctionBody());
    }

    protected function _getSpyFunctionBody()
    {
        $isSpyingOnMethod = !!$this->_context;

        if ($isSpyingOnMethod) {
            $isStatic = (new \ReflectionMethod($this->_context, $this->_functionName))->isStatic();
            if ($isStatic) {
                $context = 'get_called_class()';
                $getSpyParams = "'$this->_context', '$this->_functionName'";
            } else {
                $context = '$this';
                $getSpyParams = '$this, "'.$this->_functionName.'"';
            }
        } else {
            $context = 'null';
            $getSpyParams = "'$this->_functionName'";
        }

        return '$args = func_get_args();
        $context = '.$context.';

        $spy = '.__CLASS__.'::getSpy('.$getSpyParams.');
        return $spy->recordCall($context, $args);';
    }

    protected function _getReplaceFunctionBody()
    {
        $spyFuncName = $this->_functionName.$this->_spyFuncSuffix;
        $isSpyingOnMethod = !!$this->_context;

        if ($isSpyingOnMethod) {
            $isStatic = (new \ReflectionMethod($this->_context, $this->_functionName))->isStatic();
            if ($isStatic) {
                $spyCallback = 'array("'.$this->_context.'", "'.$spyFuncName.'")';
            } else {
                $spyCallback = 'array($this, "'.$spyFuncName.'")';
            }
        } else {
            $spyCallback = "\"$spyFuncName\"";
        }

        return '$args = func_get_args();
        return call_user_func_array('.$spyCallback.', $args);';
    }

    /**
     * Get a spy belonging to a function or method of a class or object
     * (for internal use only)
     *
     * @param object $context    Context the function was called in
     * @param string $methodName Name of the function
     *
     * @return Spy
     */
    public static function getSpy($context, $methodName = null)
    {
        if (func_num_args() == 1) {
            return self::_getFunctionSpy($context);
        }

        return self::_getMethodSpy($context, $methodName);
    }

    protected static function _getFunctionSpy($functionName)
    {
        $spyName = $functionName;
        return self::$_spies[$spyName];
    }

    protected static function _getMethodSpy($context, $methodName)
    {
        $className = $context;
        if (is_object($className)) {
            $className = strtolower(get_class($className));
        }

        $spyName = $className.'::'.$methodName;
        return self::$_spies[$spyName];
    }

    /**
     * Save a call to the method (for internal use only)
     *
     * @param object|null $context For methods their instance, for function 'null'
     * @param array       $args    Arguments the function was called with
     *
     * @return mixed
     */
    public function recordCall($context, array $args)
    {
        $originalFuncName = $this->_functionName.$this->_origFuncSuffix;

        if ($this->isActingAs()) {
            $isSpyingOnMethod = !!$context;

            if ($this->_substitute instanceof \Closure) {
                if ($isSpyingOnMethod) {
                    //call closure in context of instance or class
                    if (is_string($context)) {
                        $callable = $this->_substitute->bindTo(null, $context);
                    } else {
                        $callable = $this->_substitute->bindTo($context, get_class($context));
                    }
                } else {
                    $callable = $this->_substitute->bindTo(null, null);
                }
            } else {
                $callable = $this->_substitute;
            }
        } else {
            $callable = $context ? array($context, $originalFuncName) : "$originalFuncName";
        }
        $result = call_user_func_array($callable, $args);

        $call = new Spy\Call($context, $args, $result);
        array_push($this->_calls, $call);

        return $result;
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
     * @return Spy\Call
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
     * Intercept calls to the actual implementation and call a substitute instead
     *
     * @param callable $callable
     */
    public function actAs(callable $callable)
    {
        $this->_substitute = $callable;
    }

    /**
     * @return bool
     */
    public function isActingAs()
    {
        return !is_null($this->_substitute);
    }

    /**
     * Use the actual implementation (again) if the spied on function is called
     */
    public function actNaturally()
    {
        $this->_substitute = null;
    }

    /**
     * No longer spy on the function and restore everything at it was before
     * the spying started
     */
    public function kill()
    {
        if ($this->_context) {
            $this->_reverseMethodCallRedirection();
        } else {
            $this->_reverseFunctionCallRedirection();
        }

        $this->_deleteFromRegister();
    }

    protected function _reverseMethodCallRedirection()
    {
        $newOrigFuncName = $this->_functionName.$this->_origFuncSuffix;
        $spyFuncName = $this->_functionName.$this->_spyFuncSuffix;
        $isStatic = (new \ReflectionMethod($this->_context, $this->_functionName))->isStatic();

        if ($isStatic) {
            $origCallback = 'array("'.$this->_context.'", "'.$newOrigFuncName.'")';
        } else {
            $origCallback = 'array($this, "'.$newOrigFuncName.'")';
        }

        runkit_method_remove($this->_context, $spyFuncName);
        //keep memory address of function to prevent seg fault
        runkit_method_redefine(
            $this->_context,
            $this->_functionName,
            '',
            '$args = func_get_args();
            return call_user_func_array('.$origCallback.', $args);',
            RUNKIT_ACC_PUBLIC | ($isStatic ? RUNKIT_ACC_STATIC : 0)
        );
    }

    protected function _reverseFunctionCallRedirection()
    {
        $newOrigFuncName = $this->_functionName.$this->_origFuncSuffix;
        $spyFuncName = $this->_functionName.$this->_spyFuncSuffix;

        runkit_function_remove($spyFuncName);
        //keep memory address of function to prevent seg fault
        runkit_function_redefine(
            $this->_functionName,
            '',
            '$args = func_get_args();
            return call_user_func_array("'.$newOrigFuncName.'", $args);'
        );
    }

    protected function _deleteFromRegister()
    {
        unset(self::$_spies[$this->_name]);
    }
}
