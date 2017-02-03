### Entry scripts 
*Entry scripts* are the first step in the application bootstrapping process. 
An application (either Web application or console application) has a single entry script. End users make requests to entry scripts which instantiate application instances and forward the requests to them.

Entry scripts mainly do the following work:

- Define global constants;
- Register [Composer autoloader](https://getcomposer.org/doc/01-basic-usage.md#autoloading);
- Include the Yii class file;
- Load application configuration;
- Create and configure an [application](http://www.yiiframework.com/doc-2.0/guide-structure-applications.html) instance;
- Call yii\base\Application::run() to process the incoming request.

