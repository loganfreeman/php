```php
/**
     * Finds the path to the file where the class is defined.
     *
     * @param string $class The name of the class
     *
     * @return string|null The path, if found
     */
    public function findFile($class)
    {
        if (false !== $pos = strrpos($class, '\\')) {
            // namespaced class name
            $classPath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $pos)).DIRECTORY_SEPARATOR;
            $className = substr($class, $pos + 1);
        } else {
            // PEAR-like class name
            $classPath = null;
            $className = $class;
        }

        $classPath .= str_replace('_', DIRECTORY_SEPARATOR, $className).'.php';

        foreach ($this->prefixes as $prefix => $dirs) {
            if ($class === strstr($class, $prefix)) {
                foreach ($dirs as $dir) {
                    if (file_exists($dir.DIRECTORY_SEPARATOR.$classPath)) {
                        return $dir.DIRECTORY_SEPARATOR.$classPath;
                    }
                }
            }
        }

        foreach ($this->fallbackDirs as $dir) {
            if (file_exists($dir.DIRECTORY_SEPARATOR.$classPath)) {
                return $dir.DIRECTORY_SEPARATOR.$classPath;
            }
        }

        if ($this->useIncludePath && $file = stream_resolve_include_path($classPath)) {
            return $file;
        }
    }
 ```
