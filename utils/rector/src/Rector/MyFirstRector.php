<?php

namespace Utils\Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Expr\MethodCall;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use SilverStripe\Control\HTTPRequest;
use PHPStan\Type\ObjectType;

final class MyFirstRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        // what node types are we looking for?
        // pick from
        // https://github.com/rectorphp/php-parser-nodes-docs/
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {

        $methodCallName = $this->getName($node->name);
        if ($methodCallName === null) {
            return null;
        }

        if (! $this->nodeTypeResolver->isObjectType(
            $node->var,
            new ObjectType(HTTPRequest::class)
        )) {
            return null;
        }

        // we only care about "set*" method names
        if (! str_starts_with($methodCallName, 'set')) {
            // return null to skip it
            return null;
        }

        $newMethodCallName = preg_replace(
            '#^set#', 'change', $methodCallName
        );

        $node->name = new Identifier($newMethodCallName);

        // return $node if you modified it
        return $node;
    }

    /**
     * This method helps other to understand the rule
     * and to generate documentation.
     */
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
}
