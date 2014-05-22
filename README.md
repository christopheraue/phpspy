PHP Spy
=======

Spy on a class' methods and track the arguments it was called with. The spy does not interfere with the default behavior of the code. So, spied methods are still executed.

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
Constructor: new \\christopheraue\\phpspy\\Spy($classname, $methodname)

Public methods:
* reset(): Resets the spy by deleting all tracked calls.
* getCallCount(): Returns the count of tracked calls.
* getCall($n): Returns the nth tracked call. Negative $n get calls from the back of the list.
* kill(): Deletes all tracked calls, stops tracking further calls and kills the spy.

Calls are objects on their own. The have the following interface:
* getArg($n): Returns the nth argument of the call. Negative $n get arguments from the back of the list.
* getResult(): Returns the return value of the call.

### Basic Example
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

$secret = $spy->getCall(0)->getArg(0);
echo $secret  //"The cake is a lie."
```
