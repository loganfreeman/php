Constructor Injection
---
The DI container supports constructor injection with the help of type hints for constructor parameters. The type hints tell the container which classes or interfaces are dependent when it is used to create a new object. The container will try to get the instances of the dependent classes or interfaces and then inject them into the new object through the constructor. For example,
```php
class Foo
{
    public function __construct(Bar $bar)
    {
    }
}

$foo = $container->get('Foo');
// which is equivalent to the following:
$bar = new Bar;
$foo = new Foo($bar);
```
PHP Callable Injection
---

In this case, the container will use a registered PHP callable to build new instances of a class. Each time when yii\di\Container::get() is called, the corresponding callable will be invoked. The callable is responsible to resolve the dependencies and inject them appropriately to the newly created objects.
```php
class FooBuilder
{
    public static function build()
    {
        $foo = new Foo(new Bar);
        // ... other initializations ...
        return $foo;
    }
}

$container->set('Foo', ['app\helper\FooBuilder', 'build']);

$foo = $container->get('Foo');
```
