<?php

declare(strict_types=1);

namespace MaximeRainville\SilverstripeCmsSixRector;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Name;
use PhpParser\NodeTraverser;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use SilverStripe\Control\HTTPRequest;

final class FullyQualifiedNameToShortNameRector extends AbstractRector
{
    private string $targetClass = HTTPRequest::class;

    public function __construct()
    {

    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RectorDefinition('Convert FQN references to shortname for a specific class and add any missing use statement', [
            new CodeSample(
                <<<'PHP'
$instance = new \App\SomeNamespace\MyClass();
PHP
                ,
                <<<'PHP'
use App\SomeNamespace\MyClass;

$instance = new MyClass();
PHP
            )
        ]);
    }

    public function getNodeTypes(): array
    {
        return [FullyQualified::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (!$node instanceof FullyQualified) {
            return null;
        }

        if ((string) $node !== $this->targetClass) {
            return null;
        }

        // Create the short name node
        $shortName = new Name($node->getLast());

        // Ensure the use statement for the target class exists
        $this->addUseStatement($node, $this->targetClass);

        return $shortName;
    }

    private function addUseStatement(Node $node, string $className): void
    {
        $namespace = $node->getAttribute(AttributeKey::NAMESPACE_NAME);
        $useStatements = $node->getAttribute(AttributeKey::USE_NODES) ?? [];

        foreach ($useStatements as $useStatement) {
            foreach ($useStatement->uses as $useUse) {
                if ((string) $useUse->name === $className) {
                    return;
                }
            }
        }

        // Add the use statement
        $this->addNodeBeforeNode(new Node\Stmt\Use_([new Node\Stmt\UseUse(new Name($className))]), $node);
    }
}
