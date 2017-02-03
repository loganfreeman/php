[umask](http://stackoverflow.com/questions/12116121/php-umask0-what-is-the-purpose)
---
```php
$createFolder = function($path) {
    // Code borrowed from Io...
    if (!is_dir($path)) {
        $oldumask = umask(0);

        if (!mkdir($path, 0755, true)) {
            // Set a 503 response header so things like Varnish won't cache a bad page.
            http_response_code(503);

            exit('Tried to create a folder at '.$path.', but could not.');
        }

        // Because setting permission with mkdir is a crapshoot.
        chmod($path, 0755);
        umask($oldumask);
    }
};
```
