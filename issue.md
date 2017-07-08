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
