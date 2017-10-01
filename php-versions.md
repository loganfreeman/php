[PHP version switcher for OSX](https://github.com/sgotre/sphp-osx)
---
```
git clone git@github.com:sgotre/sphp-osx.git
echo 'export PATH="/usr/local/bin:$PATH"' >> $HOME/.bashrc
```

[How to Switch between Multiple PHP Version on Ubuntu](https://tecadmin.net/switch-between-multiple-php-version-on-ubuntu/#)
---
[Install New PHP Versions](https://lornajane.net/posts/2016/php-7-0-and-5-6-on-ubuntu)

Apache:-

```
$ sudo a2dismod php5.6
$ sudo a2enmod php7.1
$ sudo service apache2 restart
```

Command Line:-

```
$ sudo update-alternatives --set php /usr/bin/php7.1
$ sudo update-alternatives --set phar /usr/bin/phar7.1
$ sudo update-alternatives --set phar.phar /usr/bin/phar.phar7.1
```

Install extensions
---
```
sudo add-apt-repository ppa:ondrej/php
sudo apt-get update
sudo apt-get install php7.1-gd
sudo apt-get install php7.1-mbstring
```
