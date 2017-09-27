route not found
---
This looks like you have to enable .htaccess by adding this to your vhost:
```
<Directory /var/www/html/public/>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```
If that doesn't work, make sure you have [`mod_rewrite`](http://httpd.apache.org/docs/current/mod/mod_rewrite.html) enabled.

[PHP code is not being executed, instead code shows on the page](http://stackoverflow.com/questions/5121495/php-code-is-not-being-executed-instead-code-shows-on-the-page)
---
note for php 7 users, add this to your httpd.conf file:
```
# PHP 7 specific configuration
<IfModule php7_module>
    AddType application/x-httpd-php .php
    AddType application/x-httpd-php-source .phps
    <IfModule dir_module>
        DirectoryIndex index.html index.php
    </IfModule>
</IfModule>
```

libjpeg issue
---
```
wget -c http://www.ijg.org/files/jpegsrc.v8d.tar.gz
tar xzf jpegsrc.v8d.tar.gz
cd jpeg-8d
./configure
make
cp ./.libs/libjpeg.8.dylib /usr/local/opt/jpeg/lib
```
