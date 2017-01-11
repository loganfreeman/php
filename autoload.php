<?php

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
