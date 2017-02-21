- [installation_guide](https://secure.phabricator.com/book/phabricator/article/installation_guide/)
- [Creating a Configuration File](https://secure.phabricator.com/book/phabricator/article/advanced_configuration/)

vhost
```
<VirtualHost *:80>
    ServerName phabricator.local
    ServerAlias phabricator.local
    ServerAdmin webmaster@phabricator.local
    DocumentRoot "/Volumes/HD2/projects/php/phabricator/webroot"

    RewriteEngine on
    RewriteRule ^(.*)$          /index.php?__path__=$1  [B,L,QSA]

    <Directory "/Volumes/HD2/projects/php/phabricator/webroot">
        Allow from all
        AllowOverride All
        Options -MultiViews
        Require all granted
    </Directory>
</VirtualHost>
```
