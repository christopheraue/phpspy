PHP Spy
=======

Track arguments and return values of calls to functions and methods when you cannot use PHPUnit Mocks. Spys do not interfere with the behavior of the code and delegate calls to the actual implementation of the spied on function by default. But, they can be configured to delegate calls to another function.

Requirements
------------
* PHP > 5.3
* [runkit](https://github.com/zenovich/runkit)

Installation
------------
### Via Composer
Add to your *composer.json*:

```json
{
    "require": {
        "christopheraue/phpspy": "*"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/christopheraue/phpspy"
        }
    ]
}
```

Usage
-----
### Spying on functions
```php
function id($in)
{
    return $in;
}

$spy = new \christopheraue\phpspy\Spy("id");

id(1);
id(2);
id(3);

echo $spy->getCallCount();           //3
echo $spy->getCall->getArgCount();   //1
echo $spy->getCall(1)->getResult();  //2
echo $spy->getCall(0)->getContext(); //null
```

### Spying on methods
```php
class VIP
{
    private $_secret;

    public function learnSecret($secret)
    {
        $this->_secret = $secret;
    }
}
$vip = new VIP();

$spy = new \christopheraue\phpspy\Spy("VIP", "learnSecret");

$vip->learnSecret("The cake is a lie.");

echo $spy->getCallCount();           //1
echo $spy->getCall(0)->getArg(0);    //"The cake is a lie."
echo $spy->getCall(0)->getContext(); //$vip
```

### Spying on static methods
```php
class VIP
{
    public static function id($in)
    {
        return $in;
    }
}

$spy = new \christopheraue\phpspy\Spy("VIP", "id");

VIP::id("static, static, static.");

echo $spy->getCallCount();           //1
echo $spy->getCall(0)->getResult();  //"static, static, static."
echo $spy->getCall(0)->getContext(); //"VIP"
```

Complete API
------------
### Constructor
To spy on
* (static) methods: `new \christopheraue\phpspy\Spy($className, $methodName)`
* functions: `new \christopheraue\phpspy\Spy($functionName)`

### Interface of a spy:
* `getCallCount()`: Returns the number of recorded calls.
* `getCall($n)`: Returns the nth recorded call. Negative $n get calls from the back of the list.
* `reset()`: Resets the spy by deleting all recorded calls.
* `actAs($callable)`: Delegates calls to a spied on function to another [callable](http://php.net/manual/en/language.types.callable.php).
* `actNaturally()`: Delegates calls to the actual implementation of the spied on function (again).
* `kill()`: Deletes all recorded calls, stops recording further calls and kills the spy.

### Interface of a call:
* `getArgCount()`: Returns the number of recorded arguments
* `getArg($n)`: Returns the nth argument of the call. Negative $n get arguments from the back of the list.
* `getResult()`: Returns the return value of the call.
* `getContext()`: Returns `null` for functions, an reference to the object for methods and the class name for static methods.