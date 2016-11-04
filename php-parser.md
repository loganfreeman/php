[Converting namespaced code to pseudo namespaces](https://github.com/loganfreeman/PHP-Parser/blob/master/doc/2_Usage_of_basic_components.markdown)
---
We start off with the following base code:
```php
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;

$inDir  = '/some/path';
$outDir = '/some/other/path';

$parser        = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
$traverser     = new NodeTraverser;
$prettyPrinter = new PrettyPrinter\Standard;

$traverser->addVisitor(new NameResolver); // we will need resolved names
$traverser->addVisitor(new NamespaceConverter); // our own node visitor

// iterate over all .php files in the directory
$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($inDir));
$files = new \RegexIterator($files, '/\.php$/');

foreach ($files as $file) {
    try {
        // read the file that should be converted
        $code = file_get_contents($file);

        // parse
        $stmts = $parser->parse($code);

        // traverse
        $stmts = $traverser->traverse($stmts);

        // pretty print
        $code = $prettyPrinter->prettyPrintFile($stmts);

        // write the converted file to the target directory
        file_put_contents(
            substr_replace($file->getPathname(), $outDir, 0, strlen($inDir)),
            $code
        );
    } catch (PhpParser\Error $e) {
        echo 'Parse Error: ', $e->getMessage();
    }
}
```
Now lets start with the main code, the NodeVisitor\NamespaceConverter. One thing it needs to do is convert A\\B style names to A_B style ones.
```php
use PhpParser\Node;

class NamespaceConverter extends \PhpParser\NodeVisitorAbstract
{
    public function leaveNode(Node $node) {
        if ($node instanceof Node\Name) {
            return new Node\Name($node->toString('_'));
        }
    }
}
```
Code generation
---
```php
use PhpParser\BuilderFactory;
use PhpParser\PrettyPrinter;
use PhpParser\Node;

$factory = new BuilderFactory;
$node = $factory->namespace('Name\Space')
    ->addStmt($factory->use('Some\Other\Thingy')->as('SomeOtherClass'))
    ->addStmt($factory->class('SomeClass')
        ->extend('SomeOtherClass')
        ->implement('A\Few', '\Interfaces')
        ->makeAbstract() // ->makeFinal()

        ->addStmt($factory->method('someMethod')
            ->makePublic()
            ->makeAbstract() // ->makeFinal()
            ->setReturnType('bool')
            ->addParam($factory->param('someParam')->setTypeHint('SomeClass'))
            ->setDocComment('/**
                              * This method does something.
                              *
                              * @param SomeClass And takes a parameter
                              */')
        )

        ->addStmt($factory->method('anotherMethod')
            ->makeProtected() // ->makePublic() [default], ->makePrivate()
            ->addParam($factory->param('someParam')->setDefault('test'))
            // it is possible to add manually created nodes
            ->addStmt(new Node\Expr\Print_(new Node\Expr\Variable('someParam')))
        )

        // properties will be correctly reordered above the methods
        ->addStmt($factory->property('someProperty')->makeProtected())
        ->addStmt($factory->property('anotherProperty')->makePrivate()->setDefault(array(1, 2, 3)))
    )

    ->getNode()
;

$stmts = array($node);
$prettyPrinter = new PrettyPrinter\Standard();
echo $prettyPrinter->prettyPrintFile($stmts);
```
