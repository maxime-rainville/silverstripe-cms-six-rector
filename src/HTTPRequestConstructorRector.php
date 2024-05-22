<?php

namespace MaximeRainville\SilverstripeCmsSixRector;

use DeepCopy\f001\A;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Expr\MethodCall;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use SilverStripe\Control\HTTPRequest;
use PHPStan\Type\ObjectType;
use PHPStan\Reflection\ReflectionProvider;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class HTTPRequestConstructorRector extends AbstractRector
{
    private readonly ReflectionProvider $reflectionProvider;
    private readonly NodeFinder $nodeFinder;

    public function __construct(ReflectionProvider $reflectionProvider, NodeFinder $nodeFinder)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeFinder = $nodeFinder;
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [New_::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change method calls from set* to change*.', [
                new CodeSample(
                    // code before
                    '$user->setPassword("123456");',
                    // code after
                    '$user->changePassword("123456");'
                ),
            ]
        );
    }

    public function refactor(Node $node): ?Node
    {
        $traits = [];
        $class = $node->class;
        if (!method_exists($class, 'toString')) {
            return null; //we have something like "new $foo"
        }

        $className = $class->toString();
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }

        $classReflection = $this->reflectionProvider->getClass($className);

        if (!$classReflection->is(HTTPRequest::class) && !$classReflection->isSubclassOf(HTTPRequest::class)) {
            return null;
        }

        // Get the short name of the class (to respect the use statement if exists)
        $shortClassName = $this->getShortName($node, $className);

        $originalArguments = $node->args;
        $args = [
            $originalArguments[1],
            $originalArguments[0],
        ];

        $get = isset($originalArguments[2]) && !$this->isEmptyArray($originalArguments[2])
            ? $originalArguments[2] : false;
        $post = isset($originalArguments[3]) && !$this->isEmptyArray($originalArguments[3])
            ? $originalArguments[3] : false;

        if ($get && $post) {
            $arrayMergeCall = new FuncCall(new Name('array_merge'), [$get, $post]);
            $args[] = new Arg($arrayMergeCall);
        } elseif ($get) {
            $args[] = $originalArguments[2];
        } elseif ($post) {
            $args[] = $post;
        } elseif (isset($originalArguments[4])) {
            $args[] = new Arg(new Array_([]));
        }

        if (isset($originalArguments[4])) {
            // Cookie
            $args[] = new Arg(new Array_([]));
            // Files
            $args[] = new Arg(new Array_([]));
            // Server
            $args[] = new Arg(new Array_([]));
            // Body
            $args[] = $originalArguments[4];
        }

        return new StaticCall(new Name($shortClassName), 'create', $args);
    }

    private function getShortName(Node $node, string $className): string
    {
        // Find the namespace node of the current node
        $currentNode = $node;
        while ($currentNode !== null && !$currentNode instanceof Node\Stmt\Namespace_) {
            $currentNode = $currentNode->getAttribute('parent');
        }

        // Find all use statements within the namespace or file
        $useStatements = $this->nodeFinder->findInstanceOf(
            $currentNode instanceof Node\Stmt\Namespace_ ? $currentNode : $node->getAttribute('parent'),
            Node\Stmt\Use_::class
        );

        foreach ($useStatements as $useStatement) {
            foreach ($useStatement->uses as $use) {
                if ($this->nodeNameResolver->getName($use->name) === $className) {
                    return $use->name->getLast();
                }
            }
        }

        // Otherwise, return the short name of the class
        return (new Name($className))->getLast();
    }

    private function isEmptyArray(Arg $arg): bool
    {
        return $arg->value instanceof Array_ && $arg->value->items === [];
    }
}
