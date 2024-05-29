<?php

namespace MaximeRainville\SilverstripeCmsSixRector;

use DeepCopy\f001\A;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Expr\MethodCall;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use PHPStan\Type\ObjectType;
use PHPStan\Reflection\ReflectionProvider;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
use SilverStripe\Control\Session;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;

final class SessionConstructorRector extends AbstractRector
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
        if (!$node instanceof New_) {
            return null;
        }

        $class = $node->class;
        if (!method_exists($class, 'toString')) {
            return null; //we have something like "new $foo"
        }

        $className = $class->toString();
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }

        $classReflection = $this->reflectionProvider->getClass($className);

        if ($className !== Session::class) {
            return null;
        }

        $node->class = new Name(Session::class);

        // Check for constructor arguments
        if (count($node->args) === 0 || $this->isEmptyArray($node->args[0]) || $this->isNull($node->args[0])) {
            // Remove arguments if they are empty array or null
            $node->args = [];
            return $node;
        }

        // Handle array initialization case
        if ($node->args[0]->value instanceof Array_) {
            $arrayItems = $node->args[0]->value->items;

            // Create a variable for the session object
            $sessionVar = new Variable('session');

            // Assign the new session to the variable
            $assignSession = new Assign($sessionVar, new New_(new Name('SilverStripe\Control\Session')));

            // Create foreach statements to set session values
            $foreachStmts = $this->createForeachStatements($sessionVar, $arrayItems);

            // Get the parent node to insert statements before or after
            $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
            $grandParent = $node->getAttribute(AttributeKey::PARENT_NODE)->getAttribute(AttributeKey::PARENT_NODE);

            if ($grandParent instanceof Expression && $parent instanceof FuncCall) {
                // Insert foreach statements before the function call
                $this->addNodeBeforeNode(new Expression($assignSession), $grandParent);
                foreach ($foreachStmts as $stmt) {
                    $this->addNodeBeforeNode($stmt, $grandParent);
                }
                // Replace the original new session call in the function arguments
                $parent->args[0]->value = $sessionVar;
            } else {
                // Insert assign and foreach statements normally
                $this->addNodeAfterNode(new Expression($assignSession), $parent);
                foreach ($foreachStmts as $stmt) {
                    $this->addNodeAfterNode($stmt, $parent);
                }
                return $sessionVar;
            }
        }


        return $node;
    }

    private function createForeachStatements(Variable $sessionVar, array $arrayItems): array
    {
        $stmts = [];
        foreach ($arrayItems as $arrayItem) {
            if ($arrayItem instanceof ArrayItem && $arrayItem->key !== null) {
                $foreach = new Foreach_(
                    new Array_([new ArrayItem($arrayItem->value, $arrayItem->key)]),
                    new Variable('sessionValue'),
                    [
                        'keyVar' => new Variable('sessionKey'),
                        'stmts' => [
                            new Expression(
                                new MethodCall($sessionVar, 'set', [
                                    new Variable('sessionKey'),
                                    new Variable('sessionValue'),
                                ])
                            ),
                        ],
                    ]
                );
                $stmts[] = $foreach;
            }
        }
        return $stmts;
    }

    private function isEmptyArray(Arg $arg): bool
    {
        return $arg->value instanceof Array_ && $arg->value->items === [];
    }

    private function isNull(Node $node): bool
    {
        return ($node->value instanceof Node\Expr\ConstFetch && $node->value->name->toString() === 'null');
    }
}
