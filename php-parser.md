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
