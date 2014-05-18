PHP Spy
=======

Spy on a class' methods and track the arguments it was called with. The spy does not interfer with the default behavior of the code. So, spied methods are still executed.

Why?
----
To make code testing more comfortable. Especially, if you just want to check, that a method was called and recieved the correct arguments.

Requirements
------------
* PHP > 5.3
* [runkit](https://github.com/php/pecl-php-runkit)

Installation
------------
### Manual
Copy *Spy.php* into your project and include it.

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
* getCalls(): Returns all tracked calls.
* getCallCount(): Return sthe count of tracked calls.
* getCall($n): Returns the nth tracked call.
* getLastCall(): Returns the last tracked call.
* getCallArgument($n, $m): Returns the mth argument of the nth tracked call.
* getLastCallArgument($m): Returns the mth argument of the last tracked call.
* kill(): Deletes all tracked calls, stops tracking further calls and kills the spy.

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

$spy = new \christopheraue\phpspy\Spy("VIP", "learnSecret");

$vip = new VIP();
$vip->learnSecret("The cake is a lie.")

$secret = $spy->getLastCallArgument(0);
echo $secret  //"The cake is a lie."
```
