<?php
/**
 * A objects containing the tracked calls.
 *
 * PHP version 5
 *
 * @author  Christopher Aue <mail@christopheraue.net>
 * @license http://opensource.org/licenses/MIT MIT
 * @link    https://github.com/christopheraue/phpspy
 */

namespace christopheraue\phpspy\Spy;

class Call
{
    protected $_args;
    protected $_result;

    /**
     * Create a call
     *
     * @param array $args   Array with arguments of the call
     * @param mixed $result Result of the call
     */
    public function __construct($args, $result)
    {
        $this->_args = $args;
        $this->_result = $result;
    }

    /**
     * Get an argument of the call
     *
     * @param int $idx Index of the argument to get,
     * negative indices get arguments from the back of the list
     *
     * @return mixed
     * @throws \Exception
     */
    public function getArg($idx)
    {
        if (!is_numeric($idx)) {
            throw new \Exception('$idx must be an integer.');
        }

        if ($idx < 0) {
            return $this->_args[count($this->_args)+$idx];
        }

        return $this->_args[$idx];
    }

    /**
     * Get the call's return value
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->_result;
    }
}