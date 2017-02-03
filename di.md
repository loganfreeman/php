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
