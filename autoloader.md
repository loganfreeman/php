Custom autoloader for non-composer installations
---
```php
<?php
/**
 * Custom autoloader for non-composer installations.
 */
spl_autoload_register(function($class)
{
    if ($class[0] == '\\') {
        $class = substr($class, 1);
    }
    $path = sprintf('%s/%s.php', __DIR__, implode('/', explode('\\', $class)));
    if (is_file($path)) {
        require_once($path);
    }
});
```

autoload
---
```php
/**
 * AUTOLOAD CLASSES
 * Function will autoload the proper class file when the class is called
 */
function __autoload($className)
{
	// Get the path where the classes are located
	$classPath = dirname(dirname(__FILE__)) . DS . "Classes" . DS . $className . ".php";
	// Do include_once if found
	if (file_exists($classPath)) {
        include_once $classPath;
    }
}
```
