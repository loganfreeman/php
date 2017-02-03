awesome
---
- [awesome-yii](https://github.com/samdark/awesome-yii)

guide
---
- [get started](http://www.yiiframework.com/doc-2.0/guide-start-installation.html)
- [How To Create Single Page Application in minutes! 
with AngularJs 1.3 and Yii 2.0](https://github.com/hscstudio/angular1-yii2)
- [Yii 2 Practical Project Template](https://github.com/kartik-v/yii2-app-practical)

console
---
```php
#!/usr/bin/env php
<?php
/**
 * Yii console bootstrap file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/config/console.php');

$application = new yii\console\Application($config);
$exitCode = $application->run();
exit($exitCode);
```

test
---
bootstrap
```php
<?php

// ensure we get report on all possible php errors
error_reporting(-1);

define('YII_ENABLE_ERROR_HANDLER', false);
define('YII_DEBUG', true);
$_SERVER['SCRIPT_NAME'] = '/' . __DIR__;
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

Yii::setAlias('@yiiunit/extensions/httpclient', __DIR__);
Yii::setAlias('@yii/httpclient', dirname(__DIR__));
```
