PHP Spy
=======

Spy on functions and methods and track the arguments they were called with. The spy does not interfere with the default behavior of the code. So, spied functions are still executed.

Why?
----
To make code testing more comfortable. Especially, if you just want to check, that a method was called and received the correct arguments.

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
Constructor
- to spy on methods: new \\christopheraue\\phpspy\\Spy($className, $methodName)
- to spy on functions: new \\christopheraue\\phpspy\\Spy($functionName)

Public methods of the spy:
* getCallCount(): Returns the number of recorded calls.
* getCall($n): Returns the nth recorded call. Negative $n get calls from the back of the list.
* reset(): Resets the spy by deleting all recorded calls.
* kill(): Deletes all recorded calls, stops recording further calls and kills the spy.

Calls are objects on their own. The have the following interface:
* getArgCount(): Returns the number of recorded arguments
* getArg($n): Returns the nth argument of the call. Negative $n get arguments from the back of the list.
* getResult(): Returns the return value of the call.
* getContext(): Returns `null` for functions and an object for methods

### Basic Example
#### Spying on methods
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

$vip->learnSecret("The cake is a lie.")

echo $spy->getCallCount();           //1
echo $spy->getCall(0)->getArg(0);    //"The cake is a lie."
echo $spy->getCall(0)->getContext(); //$vip
```

#### Spying on functions
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